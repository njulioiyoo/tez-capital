<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Education;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EducationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Education::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title_id', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%")
                    ->orWhere('description_id', 'like', "%{$search}%")
                    ->orWhere('description_en', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->byCategory($request->get('category'));
        }

        // Filter by published status
        if ($request->filled('status')) {
            if ($request->get('status') === 'published') {
                $query->published();
            } elseif ($request->get('status') === 'draft') {
                $query->where('is_published', false);
            }
        }

        $education = $query->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Education', [
            'education' => $education,
            'categories' => Education::getCategories(),
            'filters' => $request->only(['search', 'category', 'status']),
            'bilingualEnabled' => Configuration::get('bilingual_enabled', false),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('EducationForm', [
            'education' => null,
            'categories' => Education::getCategories(),
            'bilingualEnabled' => Configuration::get('bilingual_enabled', false),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $bilingualEnabled = Configuration::get('bilingual_enabled', false);

        $rules = [
            'category' => 'required|in:'.implode(',', array_keys(Education::getCategories())),
            'tags' => 'nullable|array',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
            'sort_order' => 'integer|min:0',
        ];

        // Add bilingual validation rules
        if ($bilingualEnabled) {
            $rules = array_merge($rules, [
                'title_id' => 'required|string|max:255',
                'title_en' => 'required|string|max:255',
                'description_id' => 'nullable|string',
                'description_en' => 'nullable|string',
                'content_id' => 'nullable|string',
                'content_en' => 'nullable|string',
                'meta_title_id' => 'nullable|string|max:60',
                'meta_title_en' => 'nullable|string|max:60',
                'meta_description_id' => 'nullable|string|max:160',
                'meta_description_en' => 'nullable|string|max:160',
            ]);
        } else {
            $rules = array_merge($rules, [
                'title_id' => 'required|string|max:255',
                'description_id' => 'nullable|string',
                'content_id' => 'nullable|string',
                'meta_title_id' => 'nullable|string|max:60',
                'meta_description_id' => 'nullable|string|max:160',
            ]);
        }

        if ($request->hasFile('image')) {
            $rules['image'] = 'image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        }

        $validated = $request->validate($rules);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('education', 'public');
            $validated['image'] = $imagePath;
        }

        // Set published_at if publishing
        if ($validated['is_published'] && ! isset($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $education = Education::create($validated);

        return response()->json([
            'message' => 'Education content created successfully',
            'data' => $education,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Education $education): Response
    {
        return Inertia::render('EducationDetail', [
            'education' => $education,
            'categories' => Education::getCategories(),
            'bilingualEnabled' => Configuration::get('bilingual_enabled', false),
            'currentLanguage' => Configuration::get('default_language', 'id'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Education $education): Response
    {
        return Inertia::render('EducationForm', [
            'education' => $education,
            'categories' => Education::getCategories(),
            'bilingualEnabled' => Configuration::get('bilingual_enabled', false),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Education $education): JsonResponse
    {
        $bilingualEnabled = Configuration::get('bilingual_enabled', false);

        $rules = [
            'category' => 'required|in:'.implode(',', array_keys(Education::getCategories())),
            'tags' => 'nullable|array',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
            'sort_order' => 'integer|min:0',
        ];

        // Add bilingual validation rules
        if ($bilingualEnabled) {
            $rules = array_merge($rules, [
                'title_id' => 'required|string|max:255',
                'title_en' => 'required|string|max:255',
                'description_id' => 'nullable|string',
                'description_en' => 'nullable|string',
                'content_id' => 'nullable|string',
                'content_en' => 'nullable|string',
                'meta_title_id' => 'nullable|string|max:60',
                'meta_title_en' => 'nullable|string|max:60',
                'meta_description_id' => 'nullable|string|max:160',
                'meta_description_en' => 'nullable|string|max:160',
            ]);
        } else {
            $rules = array_merge($rules, [
                'title_id' => 'required|string|max:255',
                'description_id' => 'nullable|string',
                'content_id' => 'nullable|string',
                'meta_title_id' => 'nullable|string|max:60',
                'meta_description_id' => 'nullable|string|max:160',
            ]);
        }

        if ($request->hasFile('image')) {
            $rules['image'] = 'image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        }

        $validated = $request->validate($rules);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($education->image) {
                Storage::disk('public')->delete($education->image);
            }

            $imagePath = $request->file('image')->store('education', 'public');
            $validated['image'] = $imagePath;
        }

        // Set published_at if publishing for the first time
        if ($validated['is_published'] && ! $education->published_at) {
            $validated['published_at'] = now();
        }

        $education->update($validated);

        return response()->json([
            'message' => 'Education content updated successfully',
            'data' => $education,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Education $education): JsonResponse
    {
        // Delete image if exists
        if ($education->image) {
            Storage::disk('public')->delete($education->image);
        }

        $education->delete();

        return response()->json([
            'message' => 'Education content deleted successfully',
        ]);
    }

    /**
     * Bulk actions for education content
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:publish,unpublish,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:education,id',
        ]);

        $education = Education::whereIn('id', $request->ids);
        $message = 'Action completed successfully';

        switch ($request->action) {
            case 'publish':
                $education->update([
                    'is_published' => true,
                    'published_at' => now(),
                ]);
                $message = 'Education content published successfully';
                break;

            case 'unpublish':
                $education->update(['is_published' => false]);
                $message = 'Education content unpublished successfully';
                break;

            case 'delete':
                $education->each(function ($item) {
                    if ($item->image) {
                        Storage::disk('public')->delete($item->image);
                    }
                });
                $education->delete();
                $message = 'Education content deleted successfully';
                break;
        }

        return response()->json(['message' => $message]);
    }

    /**
     * API endpoint for frontend
     */
    public function api(Request $request): JsonResponse
    {
        $query = Education::published();

        if ($request->filled('category')) {
            $query->byCategory($request->get('category'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title_id', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%");
            });
        }

        $education = $query->orderBy('sort_order', 'asc')
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        return response()->json($education);
    }

    /**
     * Update view count for education content
     */
    public function updateViewCount(Education $education): JsonResponse
    {
        $education->incrementViews();

        return response()->json([
            'message' => 'View count updated successfully',
            'view_count' => $education->view_count,
        ]);
    }

    /**
     * Upload image for TinyMCE editor
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('education/images', 'public');

            return response()->json([
                'url' => Storage::disk('public')->url($path),
                'path' => $path,
            ]);
        }

        return response()->json([
            'message' => 'No file uploaded',
        ], 400);
    }
}
