<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ordered_at' => ['required', 'date'],
            'finished_at' => ['nullable', 'date'],
            'down_payment' => ['nullable', 'integer', 'min:0'],
            'payment' => ['nullable', 'integer', 'min:0'],
            'change' => ['nullable', 'integer', 'min:0'],
            'payment_status' => ['required', Rule::in(array_column(PaymentStatus::cases(), 'value'))],
            'order_status' => ['required', Rule::in(array_column(OrderStatus::cases(), 'value'))],
            'customer_id' => ['required', 'exists:customers,id'],
        ];
    }
}
