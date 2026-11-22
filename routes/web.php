<?php

use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomImageController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

// Guests can look up a booking with the code from their confirmation email.
Route::post('/bookings/lookup', [BookingController::class, 'guestLookup'])->name('bookings.lookup');
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');

Route::get('/cart', [CartController::class, 'show']);
Route::post('/cart', [CartController::class, 'add']);

Route::post('/payments/callback', [PaymentController::class, 'callback'])->name('payments.callback');
Route::get('/payments/return', [PaymentController::class, 'return'])->name('payments.return');

Route::middleware('auth')->group(function () {
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
    Route::get('/bookings/{booking}/attachment', [BookingController::class, 'attachment'])->name('bookings.attachment');
    Route::get('/invoices/{reference}', [BookingController::class, 'invoice'])->name('invoices.show');

    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('can:admin')->prefix('admin')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports');
        Route::get('/rooms/image-preview', [RoomImageController::class, 'preview'])->name('admin.rooms.preview');
        Route::get('/stats', [BookingController::class, 'stats'])->name('admin.stats');
    });

    Route::get('/admin/reports/export', [ReportController::class, 'export'])->name('admin.reports.export');
});
