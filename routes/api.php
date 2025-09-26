<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\BookingController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/properties', [PropertyController::class, 'index']);
    Route::get('/properties/{property}', [PropertyController::class, 'show']);

    // Admin-only management
    Route::middleware('admin')->group(function () {
        Route::post('/properties', [PropertyController::class, 'store']);
        Route::put('/properties/{property}', [PropertyController::class, 'update']);
        Route::delete('/properties/{property}', [PropertyController::class, 'destroy']);

        // Availability admin endpoints
        Route::get('/properties/{property}/availabilities', [AvailabilityController::class, 'index']);
        Route::post('/properties/{property}/availabilities', [AvailabilityController::class, 'store']);
        Route::delete('/properties/{property}/availabilities/{availability}', [AvailabilityController::class, 'destroy']);

        // Admin booking list / status
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::put('/bookings/{booking}/status', [BookingController::class, 'updateStatus']);
    });

    // Guest
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);
});
