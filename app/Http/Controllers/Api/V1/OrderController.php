<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Services\FiservPaymentService;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
    public function cancel(string $id)
    {
        try {
            $order = $this->orderService->getById($id);
            Gate::authorize('cancel', $order);
            $this->orderService->update($id, ['status' => 'cancelled']);

            return response_success('Order cancelled successfully.');
        } catch (\Exception $e) {
            return response_error('Failed to cancel order.', ['error' => $e->getMessage()], 500);
        }
    }

    // public function checkout(OrderRequest $request)
    // {
    //     try {
    //         $userId = auth('sanctum')->id();
    //         $items = $request->validated('items');

    //         // 1. Create the order
    //         $order = $this->orderService->createOrder($userId, $items);

    //         // 2. Initiate Hosted Payment Link with Fiserv
    //         $paymentResponse = $this->paymentService->createPaymentLink(
    //             $order->order_number,
    //             $order->total_amount
    //         );

    //         // 3. Save the transaction record
    //         $order->transactions()->create([
    //             'user_id' => $userId,
    //             'gateway' => 'fiserv',
    //             // Fiserv er response theke id ta ekhane seve hobe
    //             'gateway_transaction_id' => $paymentResponse['paymentLinkId'] ?? $paymentResponse['transactionId'] ?? null,
    //             'amount' => $order->total_amount,
    //             'currency' => 'USD',
    //             'status' => 'pending',
    //             'metadata' => $paymentResponse,
    //         ]);

    //         // Fiserv je URL ta dibe seta frontend e pathiye dile, frontend user ke oi link e redirect korbe
    //         $checkoutUrl = $paymentResponse['paymentUrl'] ?? $paymentResponse['url'] ?? null;

    //         return response_success('Order placed. Redirect to payment link.', [
    //             'order' => $order,
    //             'checkout_url' => $checkoutUrl,
    //             'payment_data' => $paymentResponse // Debug korar jonno pura response ta dicchi
    //         ], 201);
    //     } catch (Exception $e) {
    //         return response_error('Checkout failed.', ['error' => $e->getMessage()], 500);
    //     }
    // }
    public function checkout(OrderRequest $request)
    {
        try {
            $userId = auth('sanctum')->id();
            $items = $request->validated('items');

            // 1. Create the order using the service
            $order = $this->orderService->createOrder($userId, $items);

            // 2. TEMPORARILY BYPASS FISERV PAYMENT
            // We will uncomment this when the Fiserv API credentials are fully active
            /*
            $paymentResponse = $this->paymentService->createPaymentLink(
                $order->order_number,
                $order->total_amount
            );
            */

            // 3. Save a dummy transaction record to maintain database relations
            $order->transactions()->create([
                'user_id' => $userId,
                'gateway' => 'dummy_bypass', // Indicating this is a mocked payment
                'gateway_transaction_id' => 'mock_' . Str::random(10),
                'amount' => $order->total_amount,
                'currency' => 'USD',
                'status' => 'success', // Set to success so we can process the order in frontend
                'metadata' => ['note' => 'Payment gateway temporarily bypassed for development'],
            ]);

            // 4. Update order payment status to paid (mocked)
            $order->update(['payment_status' => 'paid']);

            return response_success('Order placed successfully (Payment bypassed).', [
                'order' => $order,
                'checkout_url' => null, // No redirect URL needed for now
            ], 201);
        } catch (Exception $e) {
            return response_error('Checkout failed.', ['error' => $e->getMessage()], 500);
        }
    }
}
