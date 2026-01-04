<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

// Debug route
Route::get('/debug-user', function () {
    $user = auth()->user();
    if ($user) {
        $user->load('roleModel');
        return response()->json([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'role_id' => $user->role_id,
            'roleModel' => $user->roleModel,
            'roleModel_name' => $user->roleModel ? $user->roleModel->name : 'NULL',
        ]);
    }
    return response()->json(['error' => 'Not authenticated']);
})->middleware('auth');

// Landing page
Route::get('/', function () {
    $properties = \App\Models\Property::with(['images', 'landlord'])
        ->where('approval_status', 'approved')
        ->latest()
        ->take(6)
        ->get();
        
   return Inertia::render('Welcome', [
        'properties' => $properties,
    ]);
})->name('landing');

// Landlord Application (public access)
Route::get('/landlord/apply', [\App\Http\Controllers\LandlordApplicationController::class, 'create'])->name('landlord.apply');
Route::post('/landlord/apply', [\App\Http\Controllers\LandlordApplicationController::class, 'store'])->name('landlord.apply.store');

// Landlord application status (requires auth)
Route::middleware('auth')->group(function () {
    Route::get('/landlord/application-status', [\App\Http\Controllers\LandlordApplicationController::class, 'status'])->name('landlord.status');
    Route::get('/landlord/application/edit', [\App\Http\Controllers\LandlordApplicationController::class, 'edit'])->name('landlord.application.edit');
    Route::put('/landlord/application/update', [\App\Http\Controllers\LandlordApplicationController::class, 'update'])->name('landlord.application.update');
});

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/properties/{property}/apply', [\App\Http\Controllers\PropertyApplicationController::class, 'create'])->name('properties.apply');
    Route::post('/properties/{property}/apply', [\App\Http\Controllers\PropertyApplicationController::class, 'store'])->name('properties.apply.store');
    Route::post('/properties/{property}/tour-request', [\App\Http\Controllers\TourRequestController::class, 'store'])->name('properties.tour.store');
    Route::post('/properties/{property}/reviews', [\App\Http\Controllers\PropertyReviewController::class, 'store'])->name('properties.reviews.store');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    $user->load('roleModel');
    
    // Redirect based on role
    if ($user->roleModel) {
        if ($user->roleModel->name === 'applicant_landlord') {
            $application = DB::table('landlord_applications')
                ->where('user_id', $user->id)
                ->first();
            
            $tierInfo = [
                'small' => ['properties' => 10, 'fee' => 500],
                'medium' => ['properties' => 50, 'fee' => 2000],
                'large' => ['properties' => 'Unlimited', 'fee' => 5000],
            ];
            
            return Inertia::render('ApplicantLandlord/Dashboard', [
                'application' => $application,
                'tierInfo' => $tierInfo,
            ]);
        } elseif ($user->roleModel->name === 'landlord') {
            // Redirect to landlord dashboard
            return redirect()->route('landlord.dashboard');
        }
    }
    
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User Management
    Route::resource('users', \App\Http\Controllers\UserController::class);

    // Landlord Routes
    Route::prefix('landlord')->name('landlord.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Landlord\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('properties', \App\Http\Controllers\Landlord\PropertyController::class);
    });

    // Admin - Role & Permission Management
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
        Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class)->except(['create', 'edit']);
        
        // Admin Dashboard Pages
        Route::get('/applications', [\App\Http\Controllers\Admin\LandlordApplicationAdminController::class, 'index'])->name('applications.index');
        Route::get('/applications/{id}', [\App\Http\Controllers\Admin\LandlordApplicationAdminController::class, 'show'])->name('applications.show');
        Route::post('/applications/{id}/under-review', [\App\Http\Controllers\Admin\LandlordApplicationAdminController::class, 'markAsUnderReview'])->name('applications.under-review');
        Route::post('/applications/{id}/approve', [\App\Http\Controllers\Admin\LandlordApplicationAdminController::class, 'approve'])->name('applications.approve');
        Route::post('/applications/{id}/reject', [\App\Http\Controllers\Admin\LandlordApplicationAdminController::class, 'reject'])->name('applications.reject');
        
        Route::get('/properties', [\App\Http\Controllers\Admin\PropertyApprovalController::class, 'index'])->name('properties.index');
        Route::get('/properties/{id}', [\App\Http\Controllers\Admin\PropertyApprovalController::class, 'show'])->name('properties.show');
        Route::post('/properties/{id}/approve', [\App\Http\Controllers\Admin\PropertyApprovalController::class, 'approve'])->name('properties.approve');
        Route::post('/properties/{id}/reject', [\App\Http\Controllers\Admin\PropertyApprovalController::class, 'reject'])->name('properties.reject');
        
        Route::get('/statistics', [\App\Http\Controllers\Admin\StatisticsController::class, 'index'])->name('statistics.index');
        Route::get('/landlords', [\App\Http\Controllers\Admin\LandlordProfileController::class, 'index'])->name('landlords.index');
        Route::get('/settings', function () {
            return Inertia::render('Admin/Settings/Index');
        })->name('settings.index');
    });
});

// Property routes
Route::get('/properties/{property}', [\App\Http\Controllers\PropertyController::class, 'show'])->name('properties.show');

require __DIR__.'/auth.php';

// Google Auth
Route::get('/auth/google', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'callback']);
