<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService)
    {
       
    }

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
        $order = $this->orderService->getById($id, ['user', 'items.food', 'deliveries', 'address']);
        return response_success('Order details retrieved successfully.', $order);
    }

    public function updateStatus(Request $request, string $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $order = $this->orderService->updateOrderStatus($id, $validated['status']);

        return response_success('Order status updated successfully.', $order);
    }
}
