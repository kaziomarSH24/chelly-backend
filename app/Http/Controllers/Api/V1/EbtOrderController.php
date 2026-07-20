<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderEbtDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EbtOrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'address1' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zipCode' => 'required|string',
            'cardNumber' => 'required|string',
            'pin' => 'required|string',
            'package_id' => 'nullable|exists:ebt_packages,id',
            'permissionSelect' => 'required|string|in:yes',
        ]);

        if ($validated['permissionSelect'] !== 'yes') {
            return response_error('Permission is required to charge the EBT card.', [], 400);
        }

        $mealPlan = 'Custom EBT Order';
        $amount = 0;

        if (isset($validated['package_id'])) {
            $package = \App\Models\EbtPackage::find($validated['package_id']);
            if (!$package || !$package->is_active) {
                return response_error('Selected package is invalid or inactive.', [], 400);
            }
            $mealPlan = $package->title;
            $amount = $package->price;
        }

        try {
            DB::beginTransaction();

            // Find or create user
            $user = User::firstOrCreate(
                ['email' => $validated['email']],
                [
                    'name' => $validated['firstName'] . ' ' . $validated['lastName'],
                    'password' => Hash::make(Str::random(16)),
                ]
            );

            // Create Order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'total_amount' => $amount,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'ebt',
                'full_name' => $validated['firstName'] . ' ' . $validated['lastName'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address1'] . ', ' . $validated['city'] . ', ' . $validated['state'] . ' ' . $validated['zipCode'],
            ]);

            // Create EBT Details
            OrderEbtDetail::create([
                'order_id' => $order->id,
                'card_number' => $validated['cardNumber'], // Will be encrypted by model cast
                'pin' => $validated['pin'], // Will be encrypted by model cast
                'meal_plan' => $mealPlan,
            ]);

            DB::commit();

            return response_success('EBT Order placed successfully.', [
                'order_number' => $order->order_number
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response_error('Failed to process EBT order.', ['error' => $e->getMessage()], 500);
        }
    }
}
