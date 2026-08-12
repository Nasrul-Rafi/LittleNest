<?php

use App\Http\Controllers\AdminBookingController;
use App\Http\Controllers\AdminCaregiverController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ChildController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.store');

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.bookings.index');
        }

        return view('dashboard');
    })->name('dashboard');

    Route::get('/admin/bookings', [AdminBookingController::class, 'index'])
        ->name('admin.bookings.index');

    Route::get('/admin/bookings/{booking}', [AdminBookingController::class, 'show'])
        ->name('admin.bookings.show');

    Route::post('/admin/bookings/{booking}/confirm', [AdminBookingController::class, 'confirm'])
        ->name('admin.bookings.confirm');

    Route::get('/admin/caregivers', [AdminCaregiverController::class, 'index'])
        ->name('admin.caregivers.index');

    Route::get('/admin/caregivers/create', [AdminCaregiverController::class, 'create'])
        ->name('admin.caregivers.create');

    Route::post('/admin/caregivers', [AdminCaregiverController::class, 'store'])
        ->name('admin.caregivers.store');

    Route::get('/admin/caregivers/{caregiver}', [AdminCaregiverController::class, 'show'])
        ->name('admin.caregivers.show');

    Route::get('/admin/caregivers/{caregiver}/edit', [AdminCaregiverController::class, 'edit'])
        ->name('admin.caregivers.edit');

    Route::post('/admin/caregivers/{caregiver}/update', [AdminCaregiverController::class, 'update'])
        ->name('admin.caregivers.update');

    Route::post('/admin/caregivers/{caregiver}/status', [AdminCaregiverController::class, 'changeStatus'])
        ->name('admin.caregivers.status');

    Route::resource('children', ChildController::class);

    Route::patch(
        '/bookings/{booking}/cancel',
        [BookingController::class, 'cancel']
    )->name('bookings.cancel');

    Route::resource('bookings', BookingController::class)
        ->only([
            'index',
            'create',
            'store',
            'show',
        ]);

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});
