<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Food;
use App\Models\Order;
use App\Models\User;

class DashboardService
{
    /**
     * Get all the necessary data for the admin dashboard overview
     */
    public function getDashboardData(): array
    {
        return [
            'statistics' => [
                'total_users' => User::count(),
                'total_categories' => Category::count(),
                'total_foods' => Food::count(),
                'total_orders' => Order::count(),
            ],
            'recent_orders' => $this->getRecentOrders(),
        ];
    }

    /**
     * Fetch the latest 5 orders for the recent orders table
     */
    private function getRecentOrders()
    {
        return Order::with('user:id,name') // Only load necessary user fields to keep it light
            ->latest()
            ->take(5) // Get only the latest 5 orders
            ->get(['id', 'user_id', 'order_number', 'status', 'total_amount', 'created_at']);
    }
}
