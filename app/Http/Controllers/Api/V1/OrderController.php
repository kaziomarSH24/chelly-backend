<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Models\User;
use App\Notifications\NewOrderPlaced;
use App\Notifications\OrderCancelled;
use App\Services\FiservPaymentService;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected FiservPaymentService $paymentService
    ) {}


    public function index()
    {
        $userId = auth('sanctum')->id();
        $orders = $this->orderService->getUserOrders($userId);

        if ($orders->isEmpty()) {
            return response_error('No orders found.', [], 404);
        }

        return response_success('Orders retrieved successfully.', $orders);
    }

    /**
     * Display the specified order details (For the View icon).
     */
    public function show(string $id)
    {
        $userId = auth('sanctum')->id();

        // Load order with related items, food details, deliveries, and address
        $order = $this->orderService->getById($id, ['items.food', 'deliveries', 'address']);

        Gate::authorize('view', $order);

        return response_success('Order details retrieved successfully.', $order);
    }

    /**
     * Cancel an order (For the Delete/Trash icon).
     */
    // public function cancel(string $id)
    // {
    //     try {
    //         $order = $this->orderService->getById($id);
    //         Gate::authorize('cancel', $order);
    //         $this->orderService->update($id, ['status' => 'cancelled']);

    //         return response_success('Order cancelled successfully.');
    //     } catch (\Exception $e) {
    //         return response_error('Failed to cancel order.', ['error' => $e->getMessage()], 500);
    //     }
    // }

    public function cancel(string $id)
    {
        try {
            $order = $this->orderService->getById($id);
            Gate::authorize('cancel', $order);

            // check order status
            if ($order->status !== 'pending') {
                return response_error('Sorry, your order is already processing and cannot be cancelled.', [], 403);
            }

            // payment check
            if ($order->payment_status === 'paid') {

                $transaction = $order->transactions()->where('gateway', 'fiserv')->where('status', 'success')->first();

                if (!$transaction || !$transaction->gateway_transaction_id) {
                    return response_error('Original payment transaction not found. Please contact support.', [], 404);
                }

                // Fiserv
                $refundResponse = $this->paymentService->processRefund(
                    $order->total_amount,
                    $transaction->gateway_transaction_id
                );

                $transactionState = $refundResponse['gatewayResponse']['transactionState'] ?? null;

                if ($transactionState !== 'CAPTURED' && $transactionState !== 'APPROVED') {
                    return response_error('Gateway rejected the refund. Please try again later.', $refundResponse, 400);
                }

                // Refund transaction record
                $order->transactions()->create([
                    'user_id' => auth('sanctum')->id(),
                    'gateway' => 'fiserv',
                    'gateway_transaction_id' => $refundResponse['gatewayResponse']['transactionProcessingDetails']['transactionId'] ?? null,
                    'amount' => $order->total_amount,
                    'currency' => 'USD',
                    'status' => 'refunded',
                    'metadata' => $refundResponse,
                ]);

                // update payment status to refunded
                $this->orderService->update($id, ['payment_status' => 'refunded']);
            }

            // update order status to cancelled
            $this->orderService->update($id, ['status' => 'cancelled']);

            $message = $order->payment_status === 'paid'
                ? 'Order cancelled and money refunded successfully.'
                : 'Order cancelled successfully.';
            $admins = User::role('admin')->get();
            Notification::send($admins, new OrderCancelled($order));

            return response_success($message);
        } catch (\Exception $e) {
            return response_error('Failed to cancel order.', ['error' => $e->getMessage()], 500);
        }
    }

    public function checkout(OrderRequest $request)
    {
        try {
            $userId = auth('sanctum')->id();
            $items = $request->validated('items');
            $paymentMethod = $request->validated('payment_method');

            //Get Delivery Information
            $deliveryInfo = $request->only(['full_name', 'email', 'phone', 'address', 'payment_method']);

            //Create the base order first, passing the delivery info
            $order = $this->orderService->createOrder($userId, $items, $deliveryInfo);

            // Handle the payment flow based on the selected method
            if ($paymentMethod === 'cash_on_delivery') {

                // Mark order as unpaid/pending for cash_on_delivery
                $order->update(['payment_status' => 'pending']);

                // Optional: Save a pending transaction record for cash_on_delivery
                $order->transactions()->create([
                    'user_id' => $userId,
                    'gateway' => 'cod',
                    'amount' => $order->total_amount,
                    'currency' => 'USD',
                    'status' => 'pending',
                ]);
            } else {

                // Handle Card Payment (Fiserv)
                $cardDetails = $request->only(['card_number', 'exp_month', 'exp_year', 'cvv']);

                $paymentResponse = $this->paymentService->processCharge(
                    $order->total_amount,
                    $cardDetails,
                    $order->order_number
                );

                $approvalStatus = $paymentResponse['gatewayResponse']['transactionState'] ?? null;
                $transactionId = $paymentResponse['gatewayResponse']['transactionProcessingDetails']['transactionId'] ?? null;

                if ($approvalStatus !== 'CAPTURED' && $approvalStatus !== 'APPROVED') {
                    // Update order status to failed so it doesn't stay stuck as pending forever
                    $order->update(['payment_status' => 'failed']);

                    return response_error('Payment was not approved by gateway.', $paymentResponse, 400);
                }

                // Save the transaction record (Success state)
                $order->transactions()->create([
                    'user_id' => $userId,
                    'gateway' => 'fiserv',
                    'gateway_transaction_id' => $transactionId,
                    'amount' => $order->total_amount,
                    'currency' => 'USD',
                    'status' => 'success',
                    'metadata' => $paymentResponse,
                ]);

                // Update order payment status to paid
                $order->update(['payment_status' => 'paid']);
            }

            // 4. Fetch all admins and send notification
            $admins = User::role('admin')->get();
            // dd($admins);
            Notification::send($admins, new NewOrderPlaced($order));

            $message = $paymentMethod === 'cash_on_delivery' ? 'Order placed successfully.' : 'Order placed and payment processed successfully.';

            return response_success($message, [
                'order' => $order,
            ], 201);
        } catch (Exception $e) {
            return response_error('Checkout failed.', ['error' => $e->getMessage()], 500);
        }
    }
    // public function checkout(OrderRequest $request)
    // {
    //     try {
    //         $userId = auth('sanctum')->id();
    //         $items = $request->validated('items');

    //         // 1. Create the order using the service
    //         $order = $this->orderService->createOrder($userId, $items);

    //         // 2. TEMPORARILY BYPASS FISERV PAYMENT
    //         // We will uncomment this when the Fiserv API credentials are fully active
    //         /*
    //         $paymentResponse = $this->paymentService->createPaymentLink(
    //             $order->order_number,
    //             $order->total_amount
    //         );
    //         */

    //         // 3. Save a dummy transaction record to maintain database relations
    //         $order->transactions()->create([
    //             'user_id' => $userId,
    //             'gateway' => 'dummy_bypass', // Indicating this is a mocked payment
    //             'gateway_transaction_id' => 'mock_' . Str::random(10),
    //             'amount' => $order->total_amount,
    //             'currency' => 'USD',
    //             'status' => 'success', // Set to success so we can process the order in frontend
    //             'metadata' => ['note' => 'Payment gateway temporarily bypassed for development'],
    //         ]);

    //         // 4. Update order payment status to paid (mocked)
    //         $order->update(['payment_status' => 'paid']);

    //         return response_success('Order placed successfully (Payment bypassed).', [
    //             'order' => $order,
    //             'checkout_url' => null, // No redirect URL needed for now
    //         ], 201);
    //     } catch (Exception $e) {
    //         return response_error('Checkout failed.', ['error' => $e->getMessage()], 500);
    //     }
    // }
}
