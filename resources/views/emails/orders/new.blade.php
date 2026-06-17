<x-mail::message>
# 🛒 New Order Received!

Hello Admin,

Great news! A new order has just been placed successfully on the platform.

### Order Details:
<x-mail::panel>
**Order Number:** {{ $order->order_number }}<br>
**Customer Name:** {{ $order->user->name ?? 'Guest User' }}<br>
**Total Amount:** ${{ number_format($order->total_amount, 2) }}<br>
**Date:** {{ $order->created_at->format('d M Y, h:i A') }}
</x-mail::panel>

Please check the admin dashboard to process this order and view the items.

<x-mail::button :url="env('NEXT_PUBLIC_ADMIN_URL', 'http://localhost:3000') . '/admin/orders/' . $order->id">
View Order Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }} System
</x-mail::message>
