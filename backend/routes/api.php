<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BranchController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    
    // Auth Routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Branches Routes
    Route::get('/branches', [BranchController::class, 'index']);
    Route::get('/branches/{id}', [BranchController::class, 'show']);

    // Catalog Routes
    Route::get('/banners', [CatalogController::class, 'banners']);
    Route::get('/categories', [CatalogController::class, 'categories']);
    Route::get('/products', [CatalogController::class, 'products']);
    Route::get('/products/{id}', [CatalogController::class, 'showProduct']);

    // Midtrans Webhook (Public)
    Route::post('/payment/notification', [PaymentController::class, 'notification']);

    // Protected Routes (Sanctum Auth)
    Route::middleware('auth:sanctum')->group(function () {
        
        // User Profile
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
        Route::post('/auth/avatar', [AuthController::class, 'updateAvatar']);
        Route::put('/auth/fcm-token', [AuthController::class, 'updateFcmToken']);

        // Orders
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);

        // Payments
        Route::post('/orders/{id}/payment/initiate', [PaymentController::class, 'initiatePayment']);
        Route::get('/orders/{id}/payment/status', [PaymentController::class, 'getPaymentStatus']);
    });
});
