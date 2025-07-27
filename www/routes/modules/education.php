<?php

use App\Http\Controllers\EducationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('education')->name('education.')->group(function () {

    // Education Content Management
    Route::get('/', [EducationController::class, 'index'])->name('index');
    Route::get('/create', [EducationController::class, 'create'])->name('create');
    Route::post('/', [EducationController::class, 'store'])->name('store');
    Route::get('/{education}', [EducationController::class, 'show'])->name('show');
    Route::get('/{education}/edit', [EducationController::class, 'edit'])->name('edit');
    Route::put('/{education}', [EducationController::class, 'update'])->name('update');
    Route::delete('/{education}', [EducationController::class, 'destroy'])->name('destroy');

    // Bulk Actions
    Route::post('/bulk-action', [EducationController::class, 'bulkAction'])->name('bulk-action');

    // API Endpoints
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/', [EducationController::class, 'api'])->name('index');
        Route::post('/{education}/view', [EducationController::class, 'updateViewCount'])->name('view');
        Route::post('/upload-image', [EducationController::class, 'uploadImage'])->name('upload-image');
    });
});
