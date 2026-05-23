<?php

namespace App\Services;

use App\Services\BaseService;
use App\Models\Order;
use App\Models\Food;
use App\Models\OrderDelivery;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService extends BaseService
{
    /**
     * The model class name.
     *
     * @var string
     */
    protected string $modelClass = Order::class;

    public function __construct()
    {
        // Ensure BaseService initializes the model instance
        parent::__construct();
    }


    // Define allowed filters
    protected function getAllowedFilters(): array
    {
        return [];
    }

    // Define allowed includes relationships
    protected function getAllowedIncludes(): array
    {
        return [];
    }

    // Define allowed sorts
    protected function getAllowedSorts(): array
    {
        return [];
    }

    /**
     * Process checkout and create order, items, and deliveries.
     */
    public function createOrder(int $userId, array $items)
    {
        return DB::transaction(function () use ($userId, $items) {
            $preparedItems = $this->prepareItemsData($items);

            // Calculate grand total from prepared items
            $totalAmount = collect($preparedItems)->sum('subtotal');

            // Create the main order record
            $order = Order::create([
                'user_id' => $userId,
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            $this->createOrderItemsAndSchedules($order, $preparedItems);

            return $order;
        });
    }

    private function prepareItemsData(array $items): array
    {
        $orderItemsData = [];

        foreach ($items as $item) {
            $food = Food::findOrFail($item['food_id']);

            // Set plan duration
            $days = $item['plan_type'] === 'weekly' ? 7 : 1;
            $subtotal = $food->price * $item['quantity'] * $days;

            $orderItemsData[] = [
                'food_id' => $food->id,
                'plan_type' => $item['plan_type'],
                'total_days' => $days,
                'quantity' => $item['quantity'],
                'unit_price' => $food->price,
                'subtotal' => $subtotal,
            ];
        }

        return $orderItemsData;
    }

    private function createOrderItemsAndSchedules(Order $order, array $preparedItems): void
    {
        foreach ($preparedItems as $data) {
            // Attach item to the order
            $orderItem = $order->items()->create($data);

            // Generate daily delivery schedules
            $schedules = [];
            for ($i = 0; $i < $data['total_days']; $i++) {
                $schedules[] = [
                    'order_id' => $order->id,
                    'order_item_id' => $orderItem->id,
                    'delivery_date' => now()->addDays($i)->toDateString(),
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $order->deliveries()->insert($schedules);
        }
    }

    /**
     * Retrieve paginated list of orders for a specific user
     */
    public function getUserOrders(int $userId)
    {
        return \Spatie\QueryBuilder\QueryBuilder::for(Order::class)
            ->where('user_id', $userId)
            ->allowedFilters(['status'])
            ->allowedSorts(['created_at', 'total_amount'])
            ->with(['items.food'])
            ->withCount([
                'deliveries as total_deliveries',
                'deliveries as completed_deliveries' => function ($query) {
                    $query->where('status', 'delivered');
                }
            ])
            ->latest()
            ->paginate(10);
    }


    /**
     * Retrieve all orders for the admin dashboard with advanced filtering
     */
    public function getAllAdminOrders()
    {
        return \Spatie\QueryBuilder\QueryBuilder::for(Order::class)
            ->allowedFilters([
                'status',
                \Spatie\QueryBuilder\AllowedFilter::partial('order_number'),
                // Filter by customer name using relation
                \Spatie\QueryBuilder\AllowedFilter::partial('customer', 'user.name'),
                // Filter by food name using nested relation
                \Spatie\QueryBuilder\AllowedFilter::partial('food', 'items.food.name'),
            ])
            ->allowedSorts(['created_at', 'total_amount', 'status'])
            ->with(['user', 'items.food']) // Eager load relations for the table
            ->withCount([
                'deliveries as total_deliveries',
                'deliveries as completed_deliveries' => function ($query) {
                    $query->where('status', 'delivered');
                }
            ])
            ->latest()
            ->paginate(10);
    }

    /**
     * Update the status of an order
     */
    public function updateOrderStatus(int $orderId, string $status): Order
    {
        return $this->update($orderId, ['status' => $status]);
    }
}
