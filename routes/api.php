<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhookController;

// Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Courses Routes (Admin Only)
Route::middleware(['auth:api', 'role:admin,instructor'])->group(function () {
    Route::post('/courses', [CourseController::class, 'store']);
    Route::put('/courses/{id}', [CourseController::class, 'update']);
    Route::delete('/courses/{id}', [CourseController::class, 'destroy']);
});

// Stripe Payment Routes
Route::middleware('auth:api')->group(function () {
    Route::post('/checkout', [PaymentController::class, 'checkout']);
    Route::get('/payment/success', [PaymentController::class, 'success']);
    Route::get('/payment/cancel', [PaymentController::class, 'cancel']);
    Route::post('/webhook', [PaymentController::class, 'handleWebhook']);
});

Route::middleware(['auth:api'])->group(function () {

    // Authentication Routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    // Payments (Available to all authenticated users)
    Route::post('/payment/session', [PaymentController::class, 'createPaymentSession']);
    Route::post('/payment/webhook', [PaymentController::class, 'handleWebhook']);

    // Subscription Management
    Route::middleware(['role:student'])->group(function () {
        Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
        Route::post('/cancel-subscription', [SubscriptionController::class, 'cancelSubscription']);
        Route::get('/subscription-status', [SubscriptionController::class, 'checkSubscriptionStatus']);
    });

    // Admin-Only Routes
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/subscriptions', [SubscriptionController::class, 'listAllSubscriptions']);
        Route::get('/admin/payments', [PaymentController::class, 'listAllPayments']);
    });

    // View Courses (Anyone)
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);

    // Courses Routes (Admin & Instructor)
    Route::middleware(['auth:api', 'role:admin,instructor'])->group(function () {
        Route::post('/courses', [CourseController::class, 'store']);
        Route::put('/courses/{id}', [CourseController::class, 'update']);
        Route::delete('/courses/{id}', [CourseController::class, 'destroy']);
    });
});
Route::post('/stripe/webhook', [WebhookController::class, 'handleStripeWebhook']);
