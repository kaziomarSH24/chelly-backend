<x-mail::message>
# Order Cancelled

Hello {{ $order->user->name ?? 'Customer' }},

Your order has been successfully cancelled.

### Order Details:
<x-mail::panel>
**Order Number:** {{ $order->order_number }}<br>
**Date:** {{ $order->created_at->format('d M Y, h:i A') }}
</x-mail::panel>

If this was a mistake or you need further assistance, please feel free to reach out to our support team.

<x-mail::button :url="env('NEXT_PUBLIC_FRONTEND_URL', 'http://localhost:3000') . '/orders'">
Browse Menu
</x-mail::button>

Thanks,<br>
{{ config('app.name') }} Team
</x-mail::message>
