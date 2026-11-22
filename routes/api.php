<?php

use App\Http\Controllers\Api\MobileAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/mobile/bookings', [MobileAuthController::class, 'bookings']);
