<?php

use App\Http\Controllers\System\Configurations\MenuController;
use App\Http\Controllers\System\AuditLog\AuditLogController;
use App\Http\Controllers\System\RolesPermissions\RoleController;
use App\Http\Controllers\System\RolesPermissions\PermissionController;
use App\Http\Controllers\System\Users\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('menu-manager', function () {
    return Inertia::render('MenuManager');
})->middleware(['auth', 'verified'])->name('menu-manager');

Route::get('audit-log', [AuditLogController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('audit-log');

Route::get('roles', [RoleController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('roles');

Route::get('users', [UserController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('users');

// API routes grouped by functionality
Route::prefix('api')->group(function () {
    // Public endpoint for menu loading (used by sidebar)
    Route::get('menu-items', [MenuController::class, 'index'])->name('api.menu-items.index');
    
    // Protected endpoints
    Route::middleware(['auth', 'verified'])->group(function () {
        // System routes
        Route::prefix('system')->name('api.system.')->group(function () {
            // Users routes
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
            
            // Roles & Permissions routes
            Route::prefix('roles-permissions')->name('roles-permissions.')->group(function () {
                Route::apiResource('roles', RoleController::class)->except(['index']);
                Route::apiResource('permissions', PermissionController::class);
            });
            
            // Audit Log routes
            Route::prefix('audit-log')->name('audit-log.')->group(function () {
                Route::get('/', [AuditLogController::class, 'getAudits'])->name('index');
                Route::get('stats', [AuditLogController::class, 'getStats'])->name('stats');
                Route::post('export', [AuditLogController::class, 'export'])->name('export');
            });
            
            // Configurations routes
            Route::prefix('configurations')->name('configurations.')->group(function () {
                Route::get('menu-items/all', [MenuController::class, 'all'])->name('menu-items.all');
                Route::post('menu-items', [MenuController::class, 'store'])->name('menu-items.store');
                Route::get('menu-items/{menuItem}', [MenuController::class, 'show'])->name('menu-items.show');
                Route::put('menu-items/{menuItem}', [MenuController::class, 'update'])->name('menu-items.update');
                Route::delete('menu-items/{menuItem}', [MenuController::class, 'destroy'])->name('menu-items.destroy');
                Route::post('menu-items/reorder', [MenuController::class, 'reorder'])->name('menu-items.reorder');
            });
        });
        
        // Report routes
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
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
