<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('customers', 'customer_code')->ignore($this->customer),
            ],
            'customer_name' => ['required', 'string', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:50'],
            'email'         => ['nullable', 'email', 'max:255'],
            'address'       => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_code.required' => 'A customer code is required.',
            'customer_code.unique'   => 'This customer code is already in use by another customer.',
        ];
    }
}
