<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;

class OfferRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
       $rules = [
            'status' => 'nullable|in:active,inactive',
        ];

        // Handle unique title validation dynamically for store and update
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $offerId = $this->route('offer');
            $rules['title'] = 'required|string|max:255|unique:offers,title,' . $offerId;
        } else {
            $rules['title'] = 'required|string|max:255|unique:offers,title';
        }

        return $rules;
    }
}

