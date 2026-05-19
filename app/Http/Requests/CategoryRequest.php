<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;

class CategoryRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Base rules that are common for both store and update
        $rules = [
            'status' => 'nullable|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $categoryId = $this->route('category') ?? $this->route('id');

            $rules['name'] = 'required|string|max:255|unique:categories,name,' . $categoryId;
        } else {
           
            $rules['name'] = 'required|string|max:255|unique:categories,name';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A category with this name already exists.',
        ];
    }
}

