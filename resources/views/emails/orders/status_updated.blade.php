<x-mail::message>
# Introduction

The body of your message.

<x-mail::button :url="''">
Button Text
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
<x-mail::message>
# Order Status Updated

Hello {{ $order->user->name ?? 'Customer' }},

This is an update regarding your recent order **#{{ $order->order_number }}**.

### Current Status: **{{ ucfirst($status) }}**

<x-mail::panel>
@if($status === 'processing')
Your meal is currently being prepared by our kitchen. We will notify you once it's ready for delivery!
@elseif($status === 'completed')
Your order has been successfully delivered/completed. We hope you enjoy your meal!
@else
Your order status has been updated. Please check your dashboard for more details.
@endif
</x-mail::panel>

<x-mail::button :url="env('NEXT_PUBLIC_FRONTEND_URL', 'http://localhost:3000') . '/orders/' . $order->id">
Track Your Order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }} Team
</x-mail::message>
