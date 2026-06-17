<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification implements ShouldQueue, ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $status
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusMessage = ucfirst($this->status);

        return (new MailMessage)
            ->subject("Order Update: Your order is now {$statusMessage}")
            ->markdown('emails.orders.status_updated', [
                'order' => $this->order,
                'status' => $this->status,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->status,
            'message' => "Your order #{$this->order->order_number} status has been updated to {$this->status}.",
            'type' => 'status_updated'
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->status,
            'message' => "Your order #{$this->order->order_number} status is now {$this->status}.",
            'type' => 'status_updated'
        ]);
    }
}
