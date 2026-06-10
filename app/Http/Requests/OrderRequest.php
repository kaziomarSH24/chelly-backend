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
            'items' => 'required|array|min:1',
            'items.*.food_id' => 'required|exists:foods,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.plan_type' => 'required|in:regular,weekly',

            'card_number' => 'required|string|min:15|max:16',
            'exp_month' => 'required|string|size:2', // e.g., '12'
            'exp_year' => 'required|string|size:4',  // e.g., '2026'
            'cvv' => 'required|string|min:3|max:4',
        ];
    }
}
