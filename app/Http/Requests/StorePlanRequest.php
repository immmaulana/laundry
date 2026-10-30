<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20'],
            'plan' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'gte:0'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
