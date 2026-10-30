<?php

namespace App\Http\Requests;

use App\Support\PlanIcons;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'icon' => ['nullable', 'string', Rule::in(PlanIcons::keys())],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'icon' => $this->input('icon') ?: 'wash',
            'remove_logo' => filter_var($this->input('remove_logo'), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
