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
    public function createOrder(int $userId, array $items, array $deliveryInfo)
    {

        return DB::transaction(function () use ($userId, $items, $deliveryInfo) {
            $preparedItems = $this->prepareItemsData($items);

            // Calculate grand total from prepared items using bundle logic
            $totalAmount = $this->calculateBundleTotal($preparedItems);

            // Create the main order record
            $order = Order::create([
                'user_id' => $userId,
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => 'pending',

                'payment_method' => $deliveryInfo['payment_method'],
                'full_name' => $deliveryInfo['full_name'],
                'email' => $deliveryInfo['email'] ?? null,
                'phone' => $deliveryInfo['phone'],
                'address' => $deliveryInfo['address'],
            ]);

            $this->createOrderItemsAndSchedules($order, $preparedItems);

            return $order;
        });
    }

    private function prepareItemsData(array $items): array
    {
        $orderItemsData = [];
        $lowStockFoods = [];
        
        // Fetch setting for low stock threshold, default to 5
        $lowStockSetting = \App\Models\Setting::where('key', 'low_stock_threshold')->first();
        $lowStockThreshold = $lowStockSetting ? (int)$lowStockSetting->value : 5;

        foreach ($items as $item) {
            $food = Food::findOrFail($item['food_id']);
            $variantId = $item['variant_id'] ?? null;

            if ($variantId) {
                $variant = \App\Models\FoodVariant::findOrFail($variantId);
                if ($variant->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$food->name} (Variant). Available: {$variant->stock}");
                }
                
                $oldStock = $variant->stock;
                $variant->decrement('stock', $item['quantity']);
                $newStock = $variant->fresh()->stock;
                
                if ($newStock <= $lowStockThreshold && $oldStock > $lowStockThreshold) {
                    // Send food object to LowStockAlert so it can use food_id and name
                    $lowStockFoods[] = $food;
                }
                
                // Also decrement the main food stock
                $food->decrement('stock', $item['quantity']);
            } else {
                if ($food->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$food->name}. Available: {$food->stock}");
                }
                
                $oldStock = $food->stock;
                $food->decrement('stock', $item['quantity']);
                $newStock = $food->fresh()->stock;
                
                if ($newStock <= $lowStockThreshold && $oldStock > $lowStockThreshold) {
                    $lowStockFoods[] = $food;
                }
            }

            // Set plan duration
            $days = $item['plan_type'] === 'weekly' ? 7 : 1;
            
            // If variant exists, price is variant price, otherwise food price
            $unitPrice = $variantId ? $variant->price : $food->price;
            $subtotal = $unitPrice * $item['quantity'];

            $orderItemsData[] = [
                'food_id' => $food->id,
                'variant_id' => $variantId,
                'plan_type' => $item['plan_type'],
                'total_days' => $days,
                'quantity' => $item['quantity'],
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ];
        }
        
        // Dispatch low stock alerts
        if (!empty($lowStockFoods)) {
            $admins = \App\Models\User::role('admin')->get();
            foreach ($lowStockFoods as $food) {
                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\LowStockAlert($food));
            }
        }

        return $orderItemsData;
    }

    private function calculateBundleTotal(array $preparedItems): float
    {
        $allItems = [];
        foreach ($preparedItems as $item) {
            for ($i = 0; $i < $item['quantity']; $i++) {
                $allItems[] = (float) $item['unit_price'];
            }
        }

        rsort($allItems);

        $total = 0.0;
        $remainingQty = count($allItems);
        $index = 0;

        while ($remainingQty > 0) {
            if ($remainingQty >= 21) {
                $total += 120.0;
                $index += 21;
                $remainingQty -= 21;
            } elseif ($remainingQty >= 10) {
                $total += 70.0;
                $index += 10;
                $remainingQty -= 10;
            } else {
                $total += $allItems[$index];
                $index += 1;
                $remainingQty -= 1;
            }
        }

        return $total;
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
            ->with(['items.food', 'items.variant'])
            ->withCount([
                'deliveries as total_deliveries',
                'deliveries as completed_deliveries' => function ($query) {
                    $query->where('status', 'delivered');
                }
            ])
            ->latest()
            ->paginate(request()->get('per_page', 10));
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
            ->with(['user', 'items.food', 'items.variant', 'ebtDetails']) // Eager load relations for the table
            ->withCount([
                'deliveries as total_deliveries',
                'deliveries as completed_deliveries' => function ($query) {
                    $query->where('status', 'delivered');
                }
            ])
            ->latest()
            ->paginate(request()->get('per_page', 10));
    }

    /**
     * Update the status of an order
     */
    public function updateOrderStatus(int $orderId, string $status): Order
    {
        $updateData = ['status' => $status];

        // Auto-update payment status to paid if order is completed
        if ($status === 'completed') {
            $updateData['payment_status'] = 'paid';
        }

        $order = $this->update($orderId, $updateData);

        if (in_array($status, ['completed', 'cancelled'])) {
            $ebtDetail = \App\Models\OrderEbtDetail::where('order_id', $orderId)->first();
            if ($ebtDetail && $ebtDetail->card_number) {
                // If it's not already masked, mask it in the database for permanent security
                if (!str_contains($ebtDetail->card_number, '****')) {
                    $cardNumber = $ebtDetail->card_number;
                    $last4 = strlen($cardNumber) >= 4 ? substr($cardNumber, -4) : $cardNumber;
                    $maskedCard = '**** **** **** ' . $last4;
                    
                    $ebtDetail->update([
                        'card_number' => $maskedCard,
                        'pin' => '****', // Setting to string to satisfy NOT NULL constraint
                    ]);
                }
            }
        }

        return $order;
    }
}
