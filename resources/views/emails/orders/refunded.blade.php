<x-mail::message>
#Order Refund Processed

Hello {{ $order->user->name ?? 'Customer' }},

We have processed a {{ $type }} refund for your recent order.

### Refund Details:
<x-mail::panel>
**Order Number:** {{ $order->order_number }}<br>
**Refund Amount:** ${{ number_format($amount, 2) }}<br>
**Type:** {{ ucfirst($type) }} Refund
</x-mail::panel>

The refunded amount will be credited to your original payment method within a few business days depending on your bank.

<x-mail::button :url="env('NEXT_PUBLIC_FRONTEND_URL', 'http://localhost:3000') . '/orders/' . $order->id">
View Order Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }} Team
</x-mail::message>
