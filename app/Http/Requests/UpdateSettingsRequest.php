<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;

class UpdateSettingsRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:50',
            'contact_address' => 'nullable|string|max:255',
            'tiktok_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'youtube_url' => 'nullable|url',


            'privacy_policy' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'payment_guidelines' => 'nullable|string',
            
            'allowed_checkout_days' => 'nullable|string',
            'low_stock_threshold' => 'nullable|integer',
        ];
    }
}

