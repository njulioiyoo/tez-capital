<?php

namespace App\Http\Controllers\System\Configurations;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\Configurations\ConfigurationStoreRequest;
use App\Http\Requests\System\Configurations\ConfigurationUpdateRequest;
use App\Models\Configuration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConfigurationController extends Controller
{
    public function index(): JsonResponse
    {
        $configurations = Configuration::getAllGrouped();

        return response()->json([
            'data' => $configurations,
        ]);
    }

    public function getByGroup(string $group): JsonResponse
    {
        $configurations = Configuration::getByGroup($group);

        return response()->json([
            'data' => $configurations,
        ]);
    }

    public function getPublic(): JsonResponse
    {
        $configurations = Configuration::getPublic();

        return response()->json([
            'data' => $configurations,
        ]);
    }

    public function store(ConfigurationStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        if ($request->hasFile('file') && $data['type'] === 'file') {
            $file = $request->file('file');
            $path = $file->store('configurations', 'public');
            $data['value'] = $path;
        }

        // Use updateOrCreate to handle both create and update cases
        $configuration = Configuration::updateOrCreate(
            ['key' => $data['key']],
            $data
        );

        return response()->json([
            'message' => 'Configuration saved successfully',
            'data' => [
                'key' => $configuration->key,
                'value' => $configuration->getValue(),
                'type' => $configuration->type,
                'group' => $configuration->group,
                'description' => $configuration->description,
                'is_public' => $configuration->is_public,
            ],
        ], 201);
    }

    public function show(Configuration $configuration): JsonResponse
    {
        return response()->json([
            'data' => [
                'key' => $configuration->key,
                'value' => $configuration->getValue(),
                'type' => $configuration->type,
                'group' => $configuration->group,
                'description' => $configuration->description,
                'is_public' => $configuration->is_public,
            ],
        ]);
    }

    public function update(ConfigurationUpdateRequest $request, Configuration $configuration): JsonResponse
    {
        $data = $request->validated();
        
        if ($request->hasFile('file') && $data['type'] === 'file') {
            if ($configuration->value && $configuration->type === 'file') {
                Storage::disk('public')->delete($configuration->value);
            }
            
            $file = $request->file('file');
            $path = $file->store('configurations', 'public');
            $data['value'] = $path;
        }

        $configuration->update($data);

        return response()->json([
            'message' => 'Configuration updated successfully',
            'data' => [
                'key' => $configuration->key,
                'value' => $configuration->getValue(),
                'type' => $configuration->type,
                'group' => $configuration->group,
                'description' => $configuration->description,
                'is_public' => $configuration->is_public,
            ],
        ]);
    }

    public function destroy(Configuration $configuration): JsonResponse
    {
        if ($configuration->type === 'file' && $configuration->value) {
            Storage::disk('public')->delete($configuration->value);
        }

        $configuration->delete();

        return response()->json([
            'message' => 'Configuration deleted successfully',
        ]);
    }

    public function updateMultiple(Request $request): JsonResponse
    {
        \Log::info('updateMultiple called', ['payload' => $request->all()]);

        $request->validate([
            'configurations' => 'required|array',
            'configurations.*.key' => 'required|string',
            'configurations.*.value' => 'nullable',
            'configurations.*.type' => 'required|in:string,text,integer,boolean,json,file,email,url',
            'configurations.*.group' => 'required|in:general,branding,homepage,credit,maintenance,contact',
        ]);

        $configurations = $request->input('configurations');
        $results = [];

        foreach ($configurations as $configData) {
            \Log::info('Processing config', ['configData' => $configData]);
            
            // Handle value processing based on type
            $value = $configData['value'];
            
            // For JSON type, ensure we store it properly
            if ($configData['type'] === 'json' && is_string($value)) {
                // If it's already a JSON string, decode it first so Laravel can encode it again
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $value = $decoded;
                }
            }
            
            // For boolean type, ensure proper boolean value
            if ($configData['type'] === 'boolean') {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }
            
            // For integer type, ensure proper integer value
            if ($configData['type'] === 'integer') {
                $value = (int) $value;
            }
            
            $config = Configuration::updateOrCreate(
                ['key' => $configData['key']],
                [
                    'value' => $value,
                    'type' => $configData['type'],
                    'group' => $configData['group'],
                    'description' => $configData['description'] ?? null,
                    'is_public' => $configData['is_public'] ?? false,
                ]
            );

            \Log::info('Config saved', [
                'id' => $config->id,
                'key' => $config->key,
                'value' => $config->value,
                'processed_value' => $value,
                'fresh_value' => $config->fresh()->value
            ]);

            $results[] = [
                'key' => $config->key,
                'value' => $config->getValue(),
                'type' => $config->type,
                'group' => $config->group,
            ];
        }

        \Log::info('updateMultiple completed', ['results' => $results]);

        return response()->json([
            'message' => 'Configurations updated successfully',
            'data' => $results,
        ]);
    }
}