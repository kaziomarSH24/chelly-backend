<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class OrderRefunded extends Notification implements ShouldQueue, ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        public Order $order,
        public float $amount,
        public string $type
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('💳 Refund Processed - Order #' . $this->order->order_number)
            ->markdown('emails.orders.refunded', [
                'order' => $this->order,
                'amount' => $this->amount,
                'type' => $this->type,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'amount' => $this->amount,
            'type' => $this->type,
            'message' => $this->type === 'full'
                ? "Your order #{$this->order->order_number} has been fully refunded."
                : "A partial refund of \${$this->amount} has been issued for your order #{$this->order->order_number}.",
            'notification_type' => 'refunded'
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'amount' => $this->amount,
            'type' => $this->type,
            'message' => $this->type === 'full'
                ? "Your order #{$this->order->order_number} has been fully refunded."
                : "A partial refund of ${$this->amount} has been issued for your order #{$this->order->order_number}.",
            'notification_type' => 'refunded'
        ]);
    }
}
