<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date'                      => ['required', 'date'],
            'customer_code'             => ['nullable', 'string', 'max:100'],
            'customer_name'             => ['nullable', 'string', 'max:255'],
            'invoice_number'            => ['required', 'string', 'max:100'],
            'items'                     => ['required', 'array', 'min:1'],
            'items.*.product_name'      => ['required', 'string', 'max:255'],
            'items.*.price'             => ['required', 'numeric', 'min:0'],
            'items.*.quantity'          => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'                => 'Please add at least one item to the purchase.',
            'items.min'                     => 'Please add at least one item to the purchase.',
            'items.*.product_name.required' => 'Each item must have a product name.',
            'items.*.price.required'        => 'Each item must have a price.',
            'items.*.quantity.required'     => 'Each item must have a quantity.',
        ];
    }
}
