<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CurriculumController;

// Handle preflight requests
Route::options('{any}', function () {
    return response('', 200);
})->where('any', '.*');

// Add CORS headers for all API routes
Route::middleware(['api'])->group(function () {
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    // Student API Routes
    Route::prefix('students')->group(function () {
        // Get student data by national ID
        Route::get('/national-id/{nationalId}', [StudentController::class, 'getStudentData']);

        });

    // Curricula API Routes
    Route::prefix('curricula')->group(function () {
        Route::get('/', [CurriculumController::class, 'index'])->name('curricula.index');
        Route::get('/{id}/file', [CurriculumController::class, 'file'])->name('curricula.file');
    });

});

