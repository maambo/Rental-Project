<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BlacklistController;
use App\Http\Controllers\Admin\LandlordApplicationAdminController;
use App\Http\Controllers\Admin\LandlordProfileController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PropertyApprovalController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\Admin\UtilityController;
use App\Http\Controllers\Api\PropertyReportController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Landlord\BillingController as LandlordBillingController;
use App\Http\Controllers\Landlord\DashboardController as LandlordDashboardController;
use App\Http\Controllers\Landlord\LeaseController;
use App\Http\Controllers\Landlord\PropertyApplicationController as LandlordPropertyApplicationController;
use App\Http\Controllers\Landlord\PropertyController as LandlordPropertyController;
use App\Http\Controllers\LandlordApplicationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyApplicationController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyReviewController;
use App\Http\Controllers\RentalHistoryController;
use App\Http\Controllers\Tenant\BillingController as TenantBillingController;
use App\Http\Controllers\Tenant\LedgerController;
use App\Http\Controllers\TourRequestController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/', [HomeController::class, 'index'])->name('landing');
Route::get('/properties/{property}', [PropertyController::class, 'show'])->name('properties.show');
Route::get('/landlord/apply', [LandlordApplicationController::class, 'create'])->name('landlord.apply');
Route::post('/landlord/apply', [LandlordApplicationController::class, 'store'])->name('landlord.apply.store');
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);

// Authenticated
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('verified')->name('dashboard');
    Route::get('/landlord/application-status', [LandlordApplicationController::class, 'status'])->name('landlord.status');
    Route::get('/landlord/application/edit', [LandlordApplicationController::class, 'edit'])->name('landlord.application.edit');
    Route::put('/landlord/application/update', [LandlordApplicationController::class, 'update'])->name('landlord.application.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/rental-history', [RentalHistoryController::class, 'index'])->name('rental-history.index');
    Route::get('/rental-history/{rentalHistory}', [RentalHistoryController::class, 'show'])->name('rental-history.show');

    Route::get('/properties/{property}/apply', [PropertyApplicationController::class, 'create'])->name('properties.apply');
    Route::post('/properties/{property}/apply', [PropertyApplicationController::class, 'store'])->name('properties.apply.store');
    Route::post('/properties/{property}/tour-request', [TourRequestController::class, 'store'])->name('properties.tour.store');
    Route::post('/properties/{property}/reviews', [PropertyReviewController::class, 'store'])->name('properties.reviews.store');

    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{user}', [ChatController::class, 'show'])->name('chat.show');
});

// Landlord
Route::middleware(['auth', 'role:landlord'])->prefix('landlord')->name('landlord.')->group(function () {
    Route::get('/dashboard', [LandlordDashboardController::class, 'index'])->name('dashboard');
    Route::resource('properties', LandlordPropertyController::class);
    Route::resource('property-applications', LandlordPropertyApplicationController::class)->only(['index', 'show', 'update']);
    Route::post('/property-applications/{application}/approve', [LandlordPropertyApplicationController::class, 'approve'])->name('property-applications.approve');
    Route::post('/property-applications/{application}/reject', [LandlordPropertyApplicationController::class, 'reject'])->name('property-applications.reject');
    Route::resource('leases', LeaseController::class)->except(['index']);
    Route::get('/leases/{lease}/print', [LeaseController::class, 'print'])->name('leases.print');
    Route::resource('billing', LandlordBillingController::class)->only(['index', 'show']);
    Route::post('/billing/{billing}/verify', [LandlordBillingController::class, 'verifyPayment'])->name('billing.verify');
});

// Tenant
Route::middleware(['auth', 'role:tenant'])->prefix('tenant')->name('tenant.')->group(function () {
    Route::resource('billing', TenantBillingController::class)->only(['index', 'show']);
    Route::post('/billing/{billing}/confirm', [TenantBillingController::class, 'confirmPayment'])->name('billing.confirm');
    Route::get('/ledger', [LedgerController::class, 'index'])->name('ledger.index');
});

// Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class)->except(['create', 'edit']);
    Route::resource('users', UserController::class);
    Route::post('/users/{user}/login-as', [UserController::class, 'loginAs'])->name('users.login-as');

    Route::get('/applications', [LandlordApplicationAdminController::class, 'index'])->name('applications.index');
    Route::get('/applications/{id}', [LandlordApplicationAdminController::class, 'show'])->name('applications.show');
    Route::post('/applications/{id}/under-review', [LandlordApplicationAdminController::class, 'markAsUnderReview'])->name('applications.under-review');
    Route::post('/applications/{id}/approve', [LandlordApplicationAdminController::class, 'approve'])->name('applications.approve');
    Route::post('/applications/{id}/reject', [LandlordApplicationAdminController::class, 'reject'])->name('applications.reject');

    Route::get('/properties', [PropertyApprovalController::class, 'index'])->name('properties.index');
    Route::get('/properties/{id}', [PropertyApprovalController::class, 'show'])->name('properties.show');
    Route::post('/properties/{id}/approve', [PropertyApprovalController::class, 'approve'])->name('properties.approve');
    Route::post('/properties/{id}/reject', [PropertyApprovalController::class, 'reject'])->name('properties.reject');

    Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');
    Route::get('/landlords', [LandlordProfileController::class, 'index'])->name('landlords.index');
    Route::get('/landlords/{id}', [LandlordProfileController::class, 'show'])->name('landlords.show');

    Route::resource('blacklist', BlacklistController::class)->only(['index', 'store', 'destroy']);
    Route::get('/reports', [PropertyReportController::class, 'index'])->name('reports.index');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Utilities management
    Route::get('/utilities', [UtilityController::class, 'index'])->name('utilities.index');
    Route::post('/utilities', [UtilityController::class, 'store'])->name('utilities.store');
    Route::put('/utilities/{utility}', [UtilityController::class, 'update'])->name('utilities.update');
    Route::delete('/utilities/{utility}', [UtilityController::class, 'destroy'])->name('utilities.destroy');
    Route::post('/utilities/{utility}/options', [UtilityController::class, 'storeOption'])->name('utilities.options.store');
    Route::delete('/utilities/options/{option}', [UtilityController::class, 'destroyOption'])->name('utilities.options.destroy');

    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});

require __DIR__ . '/auth.php';

