<?php

namespace App\Http\Requests\System\RolesPermissions;

use Illuminate\Foundation\Http\FormRequest;

class PermissionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:permissions,name',
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'group' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Permission name is required.',
            'name.unique' => 'Permission name already exists.',
            'name.max' => 'Permission name cannot exceed 255 characters.',
            'display_name.max' => 'Display name cannot exceed 255 characters.',
            'description.max' => 'Description cannot exceed 500 characters.',
            'group.max' => 'Group cannot exceed 100 characters.',
        ];
    }
}