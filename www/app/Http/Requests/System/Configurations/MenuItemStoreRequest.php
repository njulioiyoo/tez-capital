<?php

namespace App\Http\Requests\System\Configurations;

use Illuminate\Foundation\Http\FormRequest;

class MenuItemStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'href' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'position' => 'required|integer|min:0',
            'parent_id' => 'nullable|exists:menu_items,id',
            'badge' => 'nullable|string|max:50',
            'disabled' => 'boolean',
            'is_separator' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Menu title is required.',
            'title.max' => 'Menu title cannot exceed 255 characters.',
            'href.max' => 'URL cannot exceed 255 characters.',
            'icon.max' => 'Icon name cannot exceed 255 characters.',
            'position.required' => 'Position is required.',
            'position.integer' => 'Position must be a number.',
            'position.min' => 'Position must be at least 0.',
            'parent_id.exists' => 'Selected parent menu does not exist.',
            'badge.max' => 'Badge text cannot exceed 50 characters.',
            'disabled.boolean' => 'Disabled must be true or false.',
            'is_separator.boolean' => 'Is separator must be true or false.',
        ];
    }
}