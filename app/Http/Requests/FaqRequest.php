<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;

class FaqRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
       // Default rules for store (POST) request
        $rules = [
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'is_active' => 'nullable|boolean',
        ];

        // Adjust rules for update (PUT/PATCH) request allowing partial updates
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['question'] = 'sometimes|required|string|max:255';
            $rules['answer'] = 'sometimes|required|string';
        }

        return $rules;
    }
}

