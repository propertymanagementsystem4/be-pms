<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::get('/email/verify/{id}', [AuthController::class, 'verifyEmail'])
    ->name('verification.verify');
Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('/profile')->group(function () {
        Route::get('/', [UserController::class, 'getProfile']);
        Route::put('/update/{id}', [UserController::class, 'updateProfile']);
    });
    

});

Route::middleware(['auth:sanctum', 'role:ALL'])->group(function () {
    
});

Route::middleware(['auth:sanctum', 'role:OWNER'])->group(function () {
    
});

Route::middleware(['auth:sanctum', 'role:ADMIN'])->group(function () {
    
});

Route::middleware(['auth:sanctum', 'role:CUSTOMER'])->group(function () {
    
});

