<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;

class FoodRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'status' => 'nullable|in:available,unavailable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10192', // 10MB max
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:10192',
            'deleted_image_ids' => 'nullable|array',
            'deleted_image_ids.*' => 'integer|exists:food_images,id',
            'collections' => 'nullable|array',
            'collections.*' => 'integer|exists:collections,id',
            'options' => 'nullable|array',
            'variants' => 'nullable|array',
            'variants.*.title' => 'nullable|string|max:255',
            'variants.*.price' => 'required_with:variants|numeric|min:0',
            'variants.*.stock' => 'required_with:variants|integer|min:0',
            'variants.*.option1' => 'nullable|string|max:255',
            'variants.*.option2' => 'nullable|string|max:255',
            'variants.*.option3' => 'nullable|string|max:255',
        ];

        // Handle unique name validation dynamically for store and update
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $foodId = $this->route('food');
            $rules['name'] = 'required|string|max:255|unique:foods,name,' . $foodId;
        } else {
            $rules['name'] = 'required|string|max:255|unique:foods,name';
        }

        return $rules;
    }
}

