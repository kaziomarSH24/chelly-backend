<?php

namespace App\Notifications;

use App\Models\Food;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification implements ShouldQueue, ShouldBroadcastNow
{
    use Queueable;

    public function __construct(public Food $food)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'food_id' => $this->food->id,
            'food_name' => $this->food->name,
            'current_stock' => $this->food->stock,
            'message' => "Low Stock Alert: {$this->food->name} is running low (Current stock: {$this->food->stock}).",
            'type' => 'low_stock_alert'
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'food_id' => $this->food->id,
            'food_name' => $this->food->name,
            'current_stock' => $this->food->stock,
            'message' => "Low Stock Alert: {$this->food->name} is running low (Current stock: {$this->food->stock}).",
            'type' => 'low_stock_alert'
        ]);
    }
}
