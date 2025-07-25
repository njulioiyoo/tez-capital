<?php

namespace App\Http\Requests\System\Configurations;

use Illuminate\Foundation\Http\FormRequest;

class MenuItemReorderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.position' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Items array is required.',
            'items.array' => 'Items must be an array.',
            'items.min' => 'At least one item is required.',
            'items.*.id.required' => 'Item ID is required.',
            'items.*.id.exists' => 'Item does not exist.',
            'items.*.position.required' => 'Item position is required.',
            'items.*.position.integer' => 'Item position must be a number.',
            'items.*.position.min' => 'Item position must be at least 0.',
        ];
    }
}