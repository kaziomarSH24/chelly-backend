<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Notifications\OrderRefunded;
use App\Services\FiservPaymentService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use App\Notifications\OrderStatusUpdated;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService, protected FiservPaymentService $paymentService) {}

    public function index()
    {
        $orders = $this->orderService->getAllAdminOrders();

        if ($orders->isEmpty()) {
            return response_error('No orders found.', [], 404);
        }

        return response_success('Admin orders retrieved successfully.', $orders);
    }

    public function show(string $id)
    {
        $order = $this->orderService->getById($id, ['user', 'items.food', 'deliveries', 'address', 'ebtDetails']);
        return response_success('Order details retrieved successfully.', $order);
    }

    public function updateStatus(Request $request, string $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $order = $this->orderService->updateOrderStatus($id, $validated['status']);
        // Notify the user about the status change
        $order->user->notify(new OrderStatusUpdated($order, $validated['status']));
        return response_success('Order status updated successfully.', $order);
    }

    /**
     * Process a full or partial refund for a specific order (Admin Only).
     */
    public function refund(Request $request, string $id)
    {
        try {
            $request->validate([
                'amount' => 'nullable|numeric|min:0.01'
            ]);

            $order = $this->orderService->getById($id);


            if (!in_array($order->payment_status, ['paid', 'partially_refunded'])) {
                return response_error('This order is not eligible for a refund. Current status: ' . $order->payment_status, [], 400);
            }

            $alreadyRefunded = $order->transactions()->where('status', 'refunded')->sum('amount');
            $remainingRefundable = $order->total_amount - $alreadyRefunded;

            if ($remainingRefundable <= 0) {
                return response_error('This order has already been fully refunded.', [], 400);
            }


            $refundAmount = (float) $request->input('amount', $remainingRefundable);


            if ($refundAmount > $remainingRefundable) {
                return response_error('Refund amount cannot exceed the remaining refundable amount ($' . $remainingRefundable . ').', [], 400);
            }

            $transaction = $order->transactions()
                ->where('gateway', 'fiserv')
                ->where('status', 'success')
                ->first();

            if (!$transaction || !$transaction->gateway_transaction_id) {
                return response_error('Original payment transaction not found.', [], 404);
            }

            $refundResponse = $this->paymentService->processRefund(
                $refundAmount,
                $transaction->gateway_transaction_id
            );

            $transactionState = $refundResponse['gatewayResponse']['transactionState'] ?? null;

            if ($transactionState !== 'CAPTURED' && $transactionState !== 'APPROVED') {
                return response_error('Refund was not approved by gateway.', $refundResponse, 400);
            }


            $isFullRefundNow = ($alreadyRefunded + $refundAmount) >= $order->total_amount;

            $this->orderService->update($id, [
                'payment_status' => $isFullRefundNow ? 'refunded' : 'partially_refunded',
                'status' => $isFullRefundNow ? 'cancelled' : $order->status
            ]);

            $order->transactions()->create([
                'user_id' => auth('sanctum')->id() ?? $order->user_id,
                'gateway' => 'fiserv',
                'gateway_transaction_id' => $refundResponse['gatewayResponse']['transactionProcessingDetails']['transactionId'] ?? null,
                'amount' => $refundAmount,
                'currency' => 'USD',
                'status' => 'refunded',
                'metadata' => $refundResponse,
            ]);

            $message = $isFullRefundNow
                ? 'Full refund processed successfully.'
                : 'Partial refund of $' . $refundAmount . ' processed successfully.';

            // Notify the user about the refund
            $refundType = $isFullRefundNow ? 'full' : 'partial';
            $order->user->notify(new OrderRefunded($order, $refundAmount, $refundType));
            return response_success($message);
        } catch (\Exception $e) {
            return response_error('Failed to process refund.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Check live transaction status from Fiserv.
     */
    public function checkTransactionStatus(string $transactionId)
    {
        try {
            $response = $this->paymentService->transactionInquiry($transactionId);

            return response_success('Live transaction details fetched successfully from Fiserv.', $response);
        } catch (\Exception $e) {
            return response_error('Failed to fetch transaction details.', ['error' => $e->getMessage()], 500);
        }
    }
}
