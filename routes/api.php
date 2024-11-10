<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PropertyController;

// Auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// User route (appliqué le middleware ici pour éviter la duplication)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// property routes
Route::controller(PropertyController::class)->middleware('auth:sanctum')->group(function () {
    Route::post('/property', [PropertyController::class, 'store']);
    Route::get('/property/{id}', [PropertyController::class, 'show']);
    Route::post('/property/{id}', [PropertyController::class, 'update']);
    Route::delete('/property/{id}', [PropertyController::class, 'destroy']);
    Route::get('/myproperties', [PropertyController::class, 'myProperties']);
    Route::get('/properties', [PropertyController::class, 'index']);
    });



// Booking routes
Route::controller(BookingController::class)->middleware('auth:sanctum')->group(function () {
    Route::get('/booking/booked-dates/{propertyId}', 'getBookedDates');
    Route::post('/booking', 'store');
    Route::get('/mybooking', 'getMyBooking');
    Route::get('/my-request-bookings', 'getBookingsRequest');
    Route::post('/booking/{id}', 'update');
});
