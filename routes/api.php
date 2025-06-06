<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Interfaces\Api\V1\Superadmin\AuthController;
use App\Interfaces\Api\V1\Superadmin\SchoolController;
use App\Interfaces\Api\V1\Superadmin\SubscriptionPlanController;
use App\Interfaces\Api\V1\Superadmin\FormFieldController;
use App\Interfaces\Api\V1\Superadmin\SchoolInquiryController;

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
});

// Legacy route for backward compatibility
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
