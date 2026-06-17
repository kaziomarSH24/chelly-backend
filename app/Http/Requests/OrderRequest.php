<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;

class OrderRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Delivery Information Validation
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',

            // Order Items Validation
            'items' => 'required|array|min:1',
            'items.*.food_id' => 'required|exists:foods,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.plan_type' => 'required|in:regular,weekly',

            // Payment Method Validation
            'payment_method' => 'required|in:cash_on_delivery,card',

            // Card Details Validation (Only required if card payment)
            'card_number' => 'required_if:payment_method,card|string|min:15|max:16',
            'exp_month' => 'required_if:payment_method,card|string|size:2',
            'exp_year' => 'required_if:payment_method,card|string|size:4',
            'cvv' => 'required_if:payment_method,card|string|min:3|max:4',
        ];
    }


    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'card_number.required_if' => 'Please provide a valid card number to proceed with your payment.',
            'exp_month.required_if' => 'The card expiration month is required.',
            'exp_year.required_if' => 'The card expiration year is required.',
            'cvv.required_if' => 'Please enter the security code (CVV) of your card.',

            // Optional: You can also polish the length validation messages
            'card_number.min' => 'Please enter a valid 15 or 16-digit card number.',
            'card_number.max' => 'Please enter a valid 15 or 16-digit card number.',
            'cvv.min' => 'The CVV must be at least 3 digits.',
            'cvv.max' => 'The CVV cannot exceed 4 digits.',
        ];
    }
}
