<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\HardwareTypeController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Hardware Repair Booking System API Routes
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Email verification routes
Route::post('/email/verify', [AuthController::class, 'verifyEmail'])->name('verification.verify');
Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail'])->name('verification.resend');

// Password reset routes
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');

// Hardware types (public for listing)
Route::get('/hardware-types', [HardwareTypeController::class, 'index']);
Route::get('/hardware-types/{hardwareType}', [HardwareTypeController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Broadcasting auth for Pusher (must use Sanctum auth)
    Route::post('/broadcasting/auth', function (Request $request) {
        return Broadcast::auth($request);
    });

    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/profile/image', [AuthController::class, 'updateProfileImage']);
    Route::put('/password', [AuthController::class, 'changePassword']);

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/recent', [NotificationController::class, 'recent']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);
    Route::delete('/notifications/clear-read', [NotificationController::class, 'clearRead']);

    // Booking routes (all authenticated users)
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/statistics', [BookingController::class, 'statistics']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::put('/bookings/{booking}', [BookingController::class, 'update']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);

    // Incharge routes
    Route::middleware('user_type:incharge,admin,superadmin')->group(function () {
        Route::put('/bookings/{booking}/status', [BookingController::class, 'updateStatus']);
    });

    // Admin routes (includes superadmin)
    Route::middleware('user_type:admin,superadmin')->group(function () {
        // User management
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/statistics', [UserController::class, 'statistics']);
        Route::get('/users/incharges', [UserController::class, 'incharges']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);

        // Assign incharge to booking
        Route::post('/bookings/{booking}/assign', [BookingController::class, 'assignIncharge']);

        // Hardware type management
        Route::post('/hardware-types', [HardwareTypeController::class, 'store']);
        Route::put('/hardware-types/{hardwareType}', [HardwareTypeController::class, 'update']);
        Route::delete('/hardware-types/{hardwareType}', [HardwareTypeController::class, 'destroy']);
    });
});
