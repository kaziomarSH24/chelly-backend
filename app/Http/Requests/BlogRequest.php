<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;

class BlogRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'category_id' => 'required|exists:categories,id', // Added validation
            'content' => 'required|string',
            'status' => 'nullable|in:active,inactive',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $blogId = $this->route('blog');
            $rules['title'] = 'required|string|max:255|unique:blogs,title,' . $blogId;
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048';
        } else {
            $rules['title'] = 'required|string|max:255|unique:blogs,title';
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'; // Made nullable
        }

        return $rules;
    }
}

