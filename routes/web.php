<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\AdminOnly;
use App\Http\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::get('/', [AuthController::class, 'showLogin']);
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'authenticate']);
Route::get('/verify-2fa', [AuthController::class, 'showVerify']);
Route::post('/verify-2fa', [AuthController::class, 'verifyTwoFactor']);
Route::post('/resend-2fa', [AuthController::class, 'resendTwoFactor']);
Route::get('/admin/dashboard', [ReviewController::class, 'dashboard'])
    ->middleware([AuthenticateSession::class, AdminOnly::class]);

// Protected routes
Route::middleware([AuthenticateSession::class])->group(function () {
    // Dashboard
    Route::get('/dashboard', [ReviewController::class, 'dashboard']);

    // Review management
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::get('/reviews/create', [ReviewController::class, 'create']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews/{id}/edit', [ReviewController::class, 'edit']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::get('/reviews/{id}', [ReviewController::class, 'show']);
    Route::get('/user/reviews', [ReviewController::class, 'allApproved']);

    // Profile management
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Admin routes
    Route::middleware([AdminOnly::class])->group(function () {
        Route::get('/admin/pending-requests', [ReviewController::class, 'pendingRequests']);
        Route::get('/admin/reviews', [ReviewController::class, 'allApproved']);
        Route::post('/admin/reviews/{id}/approve', [AdminController::class, 'approvReview']);
        Route::post('/admin/reviews/{id}/reject', [AdminController::class, 'rejectReview']);
        Route::get('/admin/keys', [AdminController::class, 'keyManagement']);
    });
});
