<?php

namespace App\Http\Requests\System\Configurations;

use Illuminate\Foundation\Http\FormRequest;

class ConfigurationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'key' => 'required|string|max:255',
            'type' => 'required|in:string,text,integer,boolean,json,file,email,url',
            'group' => 'required|in:general,branding,homepage,credit,maintenance,contact',
            'description' => 'nullable|string|max:500',
            'is_public' => 'boolean',
        ];

        switch ($this->input('type')) {
            case 'file':
                $rules['file'] = 'required|file|mimes:jpg,jpeg,png,gif,svg,pdf,doc,docx|max:5120';
                break;
            case 'email':
                $rules['value'] = 'required|email|max:255';
                break;
            case 'url':
                $rules['value'] = 'required|url|max:255';
                break;
            case 'integer':
                $rules['value'] = 'required|integer';
                break;
            case 'boolean':
                $rules['value'] = 'required|boolean';
                break;
            case 'json':
                $rules['value'] = 'required|json';
                break;
            default:
                $rules['value'] = 'required|string|max:1000';
                break;
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'key.required' => 'Configuration key is required.',
            'key.unique' => 'Configuration key already exists.',
            'value.required' => 'Configuration value is required.',
            'value.email' => 'Please enter a valid email address.',
            'value.url' => 'Please enter a valid URL.',
            'value.integer' => 'Value must be a number.',
            'value.boolean' => 'Value must be true or false.',
            'value.json' => 'Value must be valid JSON.',
            'type.required' => 'Configuration type is required.',
            'type.in' => 'Invalid configuration type.',
            'group.required' => 'Configuration group is required.',
            'group.in' => 'Invalid configuration group.',
            'file.required' => 'File is required for file type configuration.',
            'file.mimes' => 'File must be: jpg, jpeg, png, gif, svg, pdf, doc, docx.',
            'file.max' => 'File size cannot exceed 5MB.',
        ];
    }
}