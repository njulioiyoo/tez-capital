<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->prefix('settings')->name('settings.')->group(function () {

    // Profile Settings
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // Password Settings
    Route::prefix('password')->name('password.')->group(function () {
        Route::get('/', [PasswordController::class, 'edit'])->name('edit');
        Route::put('/', [PasswordController::class, 'update'])->name('update');
    });

    // Appearance Settings
    Route::get('/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance');
});
