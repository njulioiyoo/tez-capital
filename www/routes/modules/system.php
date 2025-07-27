<?php

use App\Http\Controllers\System\AuditLog\AuditLogController;
use App\Http\Controllers\System\Configurations\ConfigurationController;
use App\Http\Controllers\System\Menu\MenuController;
use App\Http\Controllers\System\RolesPermissions\PermissionController;
use App\Http\Controllers\System\RolesPermissions\RoleController;
use App\Http\Controllers\System\Users\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('system')->name('system.')->group(function () {

    // Users Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-action', [UserController::class, 'bulkAction'])->name('bulk-action');
    });

    // Roles & Permissions
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])->name('index');
        Route::post('/', [PermissionController::class, 'store'])->name('store');
        Route::put('/{permission}', [PermissionController::class, 'update'])->name('update');
        Route::delete('/{permission}', [PermissionController::class, 'destroy'])->name('destroy');
    });

    // Configurations
    Route::prefix('configurations')->name('configurations.')->group(function () {
        Route::get('/', [ConfigurationController::class, 'index'])->name('index');
        Route::get('/public', [ConfigurationController::class, 'getPublic'])->name('public');
        Route::post('/', [ConfigurationController::class, 'store'])->name('store');
        Route::put('/{configuration}', [ConfigurationController::class, 'update'])->name('update');
        Route::delete('/{configuration}', [ConfigurationController::class, 'destroy'])->name('destroy');
    });

    // Menu Management
    Route::prefix('menu')->name('menu.')->group(function () {
        Route::get('/', [MenuController::class, 'index'])->name('index');
        Route::get('/items', [MenuController::class, 'api'])->name('items');
        Route::post('/items', [MenuController::class, 'store'])->name('items.store');
        Route::put('/items/{menuItem}', [MenuController::class, 'update'])->name('items.update');
        Route::delete('/items/{menuItem}', [MenuController::class, 'destroy'])->name('items.destroy');
        Route::post('/items/bulk-action', [MenuController::class, 'bulkAction'])->name('items.bulk-action');
    });

    // Audit Log
    Route::prefix('audit-log')->name('audit-log.')->group(function () {
        Route::get('/', [AuditLogController::class, 'index'])->name('index');
        Route::get('/export', [AuditLogController::class, 'export'])->name('export');
    });
});
