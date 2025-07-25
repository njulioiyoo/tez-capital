<?php

namespace App\Http\Controllers\System\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\Menu\MenuItemReorderRequest;
use App\Http\Requests\System\Menu\MenuItemStoreRequest;
use App\Http\Requests\System\Menu\MenuItemUpdateRequest;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $menuItems = MenuItem::getMenuTree();

        return response()->json([
            'data' => $menuItems->map(fn ($item) => $item->toMenuArray()),
        ]);
    }

    /**
     * Get all menu items (flat structure for management)
     */
    public function all(): JsonResponse
    {
        $menuItems = MenuItem::active()->ordered()->get();

        return response()->json([
            'data' => $menuItems,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MenuItemStoreRequest $request): JsonResponse
    {
        $menuItem = MenuItem::create($request->validated());

        return response()->json([
            'message' => 'Menu item created successfully',
            'data' => $menuItem->toMenuArray(),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(MenuItem $menuItem): JsonResponse
    {
        return response()->json([
            'data' => $menuItem->toMenuArray(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MenuItemUpdateRequest $request, MenuItem $menuItem): JsonResponse
    {
        $menuItem->update($request->validated());

        return response()->json([
            'message' => 'Menu item updated successfully',
            'data' => $menuItem->fresh()->toMenuArray(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MenuItem $menuItem): JsonResponse
    {
        $menuItem->delete();

        return response()->json([
            'message' => 'Menu item deleted successfully',
        ]);
    }

    /**
     * Reorder menu items
     */
    public function reorder(MenuItemReorderRequest $request): JsonResponse
    {
        \Log::info('Reorder request received', $request->validated());

        foreach ($request->validated()['items'] as $item) {
            \Log::info("Updating menu item {$item['id']} to position {$item['position']}");
            MenuItem::where('id', $item['id'])->update(['position' => $item['position']]);
        }

        return response()->json([
            'message' => 'Menu items reordered successfully',
        ]);
    }
}
