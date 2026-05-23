<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view the specific order.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can cancel the order.
     */
    public function cancel(User $user, Order $order): bool
    {

        return $user->id === $order->user_id && $order->status === 'pending';
    }
}
