<?php

namespace App\Http\Requests\System\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');
        
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'password' => [
                'nullable',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'status' => 'boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.max' => 'Name cannot exceed 255 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already taken.',
            'email.max' => 'Email cannot exceed 255 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'phone.max' => 'Phone number cannot exceed 20 characters.',
            'address.max' => 'Address cannot exceed 500 characters.',
            'status.boolean' => 'Status must be true or false.',
            'roles.array' => 'Roles must be an array.',
            'roles.*.exists' => 'Selected role does not exist.',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->boolean('status', true),
        ]);
    }
}