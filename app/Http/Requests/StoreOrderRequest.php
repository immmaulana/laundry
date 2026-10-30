<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ordered_at' => ['required', 'date'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'name' => ['required_without:customer_id', 'nullable', 'string', 'max:100'],
            'phone' => ['required_without:customer_id', 'nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }
}
