<?php

use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\TourRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes - No authentication required
Route::get('/properties', [PropertyController::class, 'index']);
Route::get('/properties/{id}', [PropertyController::class, 'show']);

// Protected routes - Require authentication
Route::middleware('auth:sanctum')->group(function () {
    
    // Get authenticated user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Property Reviews
    Route::post('/properties/{id}/reviews', [ReviewController::class, 'store']);

    // Tour Requests
    Route::post('/properties/{id}/tour-requests', [TourRequestController::class, 'store']);

    // Landlord routes
    Route::middleware('role:landlord')->prefix('landlord')->group(function () {
        // Property management
        Route::apiResource('properties', PropertyController::class)->except(['index', 'show']);
        
        // Tour requests management
        Route::get('/tour-requests', [TourRequestController::class, 'index']);
        Route::post('/tour-requests/{id}/approve', [TourRequestController::class, 'approve']);
    });

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Admin endpoints will be added here
    });

    // Tenant routes
    Route::middleware('role:tenant')->prefix('tenant')->group(function () {
        // Tenant endpoints will be added here
    });
});
