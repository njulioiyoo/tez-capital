<?php

namespace App\Http\Requests\System\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserBulkActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => [
                'required',
                'string',
                Rule::in(['activate', 'deactivate', 'delete', 'assign_role'])
            ],
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'role_id' => 'required_if:action,assign_role|exists:roles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'Action is required.',
            'action.in' => 'Invalid action selected.',
            'user_ids.required' => 'Please select at least one user.',
            'user_ids.array' => 'User IDs must be an array.',
            'user_ids.min' => 'Please select at least one user.',
            'user_ids.*.exists' => 'Selected user does not exist.',
            'role_id.required_if' => 'Role is required when assigning roles.',
            'role_id.exists' => 'Selected role does not exist.',
        ];
    }
}