<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Interfaces\Api\V1\Superadmin\AuthController;
use App\Interfaces\Api\V1\Superadmin\SchoolController;
use App\Interfaces\Api\V1\Superadmin\SubscriptionPlanController;
use App\Interfaces\Api\V1\Superadmin\FormFieldController;
use App\Interfaces\Api\V1\Superadmin\SchoolInquiryController;
use App\Interfaces\Api\V1\Superadmin\AdminController;
use App\Interfaces\Api\V1\Admin\AuthController as AdminAuthController;
use App\Interfaces\Api\V1\Admin\ProfileController as AdminProfileController;
use App\Interfaces\Api\V1\Admin\SchoolSetupController;

/*
|--------------------------------------------------------------------------
| Central API Routes (Superadmin)
|--------------------------------------------------------------------------
|
| These routes are for the central superadmin API that manages the entire
| SaaS platform. All routes are prefixed with /api/v1 and use JSON responses.
|
*/

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    // School registration inquiry (public endpoint)
    Route::post('/inquiries', [SchoolInquiryController::class, 'store']);
    
    // Get active form fields for registration form
    Route::get('/form-fields/active', [FormFieldController::class, 'getActiveFields']);
});

// Superadmin authentication routes
Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    
    // Protected auth routes
    Route::middleware(['auth:sanctum', 'superadmin.auth'])->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });
});

// Protected superadmin routes
Route::prefix('v1')->middleware(['auth:sanctum', 'superadmin.auth'])->group(function () {
    
    // School Management
    Route::apiResource('schools', SchoolController::class);
    Route::post('/schools/{id}/suspend', [SchoolController::class, 'suspend']);
    Route::post('/schools/{id}/activate', [SchoolController::class, 'activate']);
    Route::get('/schools-statistics', [SchoolController::class, 'statistics']);
    
    // Subscription Plans Management
    Route::apiResource('subscription-plans', SubscriptionPlanController::class);
    Route::post('/subscription-plans/{id}/toggle-status', [SubscriptionPlanController::class, 'toggleStatus']);
    
    // Form Fields Management
    Route::apiResource('form-fields', FormFieldController::class);
    Route::post('/form-fields/update-order', [FormFieldController::class, 'updateOrder']);
    Route::post('/form-fields/{id}/toggle-status', [FormFieldController::class, 'toggleStatus']);
    
    // School Inquiries Management
    Route::apiResource('inquiries', SchoolInquiryController::class)->except(['store']);
    Route::post('/inquiries/{id}/approve', [SchoolInquiryController::class, 'approve']);
    Route::post('/inquiries/bulk-action', [SchoolInquiryController::class, 'bulkAction']);
    Route::get('/inquiries-statistics', [SchoolInquiryController::class, 'statistics']);
    
    // Admin Management (Superadmin)
    Route::apiResource('admins', AdminController::class);
    Route::put('/admins/{admin}/status', [AdminController::class, 'updateStatus']);
    Route::post('/admins/{admin}/reset-password', [AdminController::class, 'resetPassword']);
    Route::post('/admins/{admin}/resend-credentials', [AdminController::class, 'resendCredentials']);
    Route::get('/admins/{admin}/conversion-status', [AdminController::class, 'conversionStatus']);
    Route::get('/admins-statistics', [AdminController::class, 'statistics']);
});

/*
|--------------------------------------------------------------------------
| Admin API Routes (Central Database - Phase 1)
|--------------------------------------------------------------------------
|
| These routes are for admins before they are converted to tenants.
| Admins use these routes to login, manage profile, and create schools.
|
*/

// Admin authentication routes
Route::prefix('v1/admin/auth')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);
    
    // Protected admin auth routes
    Route::middleware(['auth:sanctum', 'admin.auth'])->group(function () {
        Route::get('/user', [AdminAuthController::class, 'user']);
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::post('/change-password', [AdminAuthController::class, 'changePassword']);
        Route::post('/refresh', [AdminAuthController::class, 'refresh']);
    });
});

// Protected admin routes (Phase 1)
Route::prefix('v1/admin')->middleware(['auth:sanctum', 'admin.auth'])->group(function () {
    
    // Profile Management
    Route::get('/profile', [AdminProfileController::class, 'show']);
    Route::put('/profile', [AdminProfileController::class, 'update']);
    Route::post('/profile/avatar', [AdminProfileController::class, 'uploadAvatar']);
    Route::get('/profile/school-setup-status', [AdminProfileController::class, 'schoolSetupStatus']);
    
    // School Setup (Phase 1 to Phase 2 conversion)
    Route::get('/school-setup', [SchoolSetupController::class, 'getSetupForm']);
    Route::post('/school-setup', [SchoolSetupController::class, 'createSchool']);
    Route::get('/school-setup/status', [SchoolSetupController::class, 'getSetupStatus']);
    Route::post('/school-setup/cancel', [SchoolSetupController::class, 'cancelSetup']);
    Route::post('/school-setup/retry', [SchoolSetupController::class, 'retrySetup']);
});

// Legacy route for backward compatibility
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
