<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;

class BannerRequest extends BaseRequest
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

        // Handle unique title and image requirements based on the method
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $bannerId = $this->route('banner');
            $rules['title'] = 'required|string|max:255|unique:banners,title,' . $bannerId;
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240'; // Image is optional on update
        } else {
            $rules['title'] = 'required|string|max:255|unique:banners,title';
            $rules['image'] = 'required|image|mimes:jpeg,png,jpg,webp|max:10240'; // Image is required on create
        }

        return $rules;
    }
}


