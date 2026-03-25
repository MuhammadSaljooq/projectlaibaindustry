<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBankStatementEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role !== 'viewer';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'transaction_date' => ['required', 'date'],
            'company_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
