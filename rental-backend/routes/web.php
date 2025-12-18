<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'properties' => \App\Models\Property::with('images')->latest()->take(6)->get(),
    ]);
});

Route::get('/properties/{id}', [\App\Http\Controllers\PropertyController::class, 'show'])->name('properties.show');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/properties/{property}/apply', [\App\Http\Controllers\PropertyApplicationController::class, 'create'])->name('properties.apply');
    Route::post('/properties/{property}/apply', [\App\Http\Controllers\PropertyApplicationController::class, 'store'])->name('properties.apply.store');
    Route::post('/properties/{property}/tour-request', [\App\Http\Controllers\TourRequestController::class, 'store'])->name('properties.tour.store');
    Route::post('/properties/{property}/reviews', [\App\Http\Controllers\PropertyReviewController::class, 'store'])->name('properties.reviews.store');
    
    // Landlord
    Route::get('/landlord/apply', [\App\Http\Controllers\LandlordApplicationController::class, 'create'])->name('landlord.apply');
    Route::post('/landlord/apply', [\App\Http\Controllers\LandlordApplicationController::class, 'store'])->name('landlord.apply.store');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('users', \App\Http\Controllers\UserController::class);
});

require __DIR__.'/auth.php';

// Google Auth
Route::get('/auth/google', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'callback']);
