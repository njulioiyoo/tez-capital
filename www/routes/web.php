<?php

use App\Http\Controllers\System\AuditLog\AuditLogController;
use App\Http\Controllers\System\Configurations\ConfigurationController;
use App\Http\Controllers\System\Menu\MenuController;
use App\Http\Controllers\System\RolesPermissions\PermissionController;
use App\Http\Controllers\System\RolesPermissions\RoleController;
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

Route::get('configurations', function () {
    return Inertia::render('Configurations');
})->middleware(['auth', 'verified'])->name('configurations');

// API routes grouped by functionality
Route::prefix('api')->group(function () {
    // Public endpoints
    Route::get('menu-items', [MenuController::class, 'index'])->name('api.menu-items.index');
    Route::get('configurations/public', [ConfigurationController::class, 'getPublic'])->name('api.configurations.public');

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

            // Menu routes
            Route::prefix('menu')->name('menu.')->group(function () {
                Route::get('items/all', [MenuController::class, 'all'])->name('items.all');
                Route::post('items', [MenuController::class, 'store'])->name('items.store');
                Route::get('items/{menuItem}', [MenuController::class, 'show'])->name('items.show');
                Route::put('items/{menuItem}', [MenuController::class, 'update'])->name('items.update');
                Route::delete('items/{menuItem}', [MenuController::class, 'destroy'])->name('items.destroy');
                Route::post('items/reorder', [MenuController::class, 'reorder'])->name('items.reorder');
            });

            // Configurations routes
            Route::prefix('configurations')->name('configurations.')->group(function () {
                Route::get('/', [ConfigurationController::class, 'index'])->name('index');
                Route::get('group/{group}', [ConfigurationController::class, 'getByGroup'])->name('group');
                Route::post('/', [ConfigurationController::class, 'store'])->name('store');
                Route::get('{configuration}', [ConfigurationController::class, 'show'])->name('show');
                Route::put('{configuration}', [ConfigurationController::class, 'update'])->name('update');
                Route::delete('{configuration}', [ConfigurationController::class, 'destroy'])->name('destroy');
                Route::post('bulk-update', [ConfigurationController::class, 'updateMultiple'])->name('bulk-update');
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

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
