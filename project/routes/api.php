<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\FlightSearchController;
use App\Http\Controllers\MockProviderController;
use Illuminate\Support\Facades\Route;

Route::prefix('mock')->group(function (): void {
    Route::get('/provider-a', [MockProviderController::class, 'providerA']);
    Route::get('/provider-b', [MockProviderController::class, 'providerB']);
    Route::get('/provider-c', [MockProviderController::class, 'providerC']);
});

Route::get('/flights/search', [FlightSearchController::class, 'search']);
Route::post('/bookings', [BookingController::class, 'store']);
Route::get('/bookings/{reference}', [BookingController::class, 'show']);
