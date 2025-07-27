<?php

use App\Http\Controllers\EducationController;
use App\Http\Controllers\System\AuditLog\AuditLogController;
use App\Http\Controllers\System\Configurations\ConfigurationController;
use App\Http\Controllers\System\Menu\MenuController;
use App\Http\Controllers\System\RolesPermissions\PermissionController;
use App\Http\Controllers\System\RolesPermissions\RoleController;
use App\Http\Controllers\System\Users\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public API endpoints (no authentication required)
Route::get('menu-items', [MenuController::class, 'api'])->name('api.menu-items.index');
Route::get('configurations/public', [ConfigurationController::class, 'getPublic'])->name('api.configurations.public');
Route::get('education', [EducationController::class, 'api'])->name('api.education.index');
Route::get('csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->name('api.csrf-token');

// Protected API endpoints
Route::middleware(['auth', 'verified'])->group(function () {
    // System API routes
    Route::prefix('system')->name('api.system.')->group(function () {
        // Users API
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'getData'])->name('index');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('{user}', [UserController::class, 'show'])->name('show');
            Route::put('{user}', [UserController::class, 'update'])->name('update');
            Route::delete('{user}', [UserController::class, 'destroy'])->name('destroy');
            Route::post('bulk-action', [UserController::class, 'bulkAction'])->name('bulk-action');
            Route::post('{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('stats', [UserController::class, 'getStats'])->name('stats');
        });

        // Roles & Permissions API
        Route::prefix('roles-permissions')->name('roles-permissions.')->group(function () {
            Route::apiResource('roles', RoleController::class)->except(['index']);
            Route::apiResource('permissions', PermissionController::class);
        });

        // Audit Log API
        Route::prefix('audit-log')->name('audit-log.')->group(function () {
            Route::get('/', [AuditLogController::class, 'getAudits'])->name('index');
            Route::get('stats', [AuditLogController::class, 'getStats'])->name('stats');
            Route::post('export', [AuditLogController::class, 'export'])->name('export');
        });

        // Menu API
        Route::prefix('menu')->name('menu.')->group(function () {
            Route::get('items/all', [MenuController::class, 'all'])->name('items.all');
            Route::post('items', [MenuController::class, 'store'])->name('items.store');
            Route::get('items/{menuItem}', [MenuController::class, 'show'])->name('items.show');
            Route::put('items/{menuItem}', [MenuController::class, 'update'])->name('items.update');
            Route::delete('items/{menuItem}', [MenuController::class, 'destroy'])->name('items.destroy');
            Route::post('items/reorder', [MenuController::class, 'reorder'])->name('items.reorder');
        });

        // Configurations API
        Route::prefix('configurations')->name('configurations.')->group(function () {
            Route::get('/', [ConfigurationController::class, 'api'])->name('index');
            Route::get('group/{group}', [ConfigurationController::class, 'getByGroup'])->name('group');
            Route::post('/', [ConfigurationController::class, 'store'])->name('store');
            Route::get('{configuration}', [ConfigurationController::class, 'show'])->name('show');
            Route::put('{configuration}', [ConfigurationController::class, 'update'])->name('update');
            Route::delete('{configuration}', [ConfigurationController::class, 'destroy'])->name('destroy');
            Route::post('bulk-update', [ConfigurationController::class, 'updateMultiple'])->name('bulk-update');
        });

        // Education API
        Route::prefix('education')->name('education.')->group(function () {
            Route::post('/', [EducationController::class, 'store'])->name('store');
            Route::put('{education}', [EducationController::class, 'update'])->name('update');
            Route::delete('{education}', [EducationController::class, 'destroy'])->name('destroy');
            Route::post('bulk-action', [EducationController::class, 'bulkAction'])->name('bulk-action');
            Route::post('{education}/view', [EducationController::class, 'updateViewCount'])->name('view');
            Route::post('upload-image', [EducationController::class, 'uploadImage'])->name('upload-image');
        });
    });

    // Report API routes (placeholder for future implementation)
    Route::prefix('report')->name('api.report.')->group(function () {
        // Analytics routes (placeholder for future implementation)
        Route::prefix('analytics')->name('analytics.')->group(function () {
            // Route::get('dashboard', [AnalyticsController::class, 'dashboard'])->name('dashboard');
            // Route::get('traffic', [AnalyticsController::class, 'traffic'])->name('traffic');
        });

        // Financial Reports routes (placeholder for future implementation)
        Route::prefix('financial')->name('financial.')->group(function () {
            // Route::get('revenue', [FinancialController::class, 'revenue'])->name('revenue');
            // Route::get('expenses', [FinancialController::class, 'expenses'])->name('expenses');
        });
    });
});
