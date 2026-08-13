<?php

use App\Http\Controllers\AdminBookingController;
use App\Http\Controllers\AdminBookingRequestController;
use App\Http\Controllers\AdminCaregiverController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminPaymentController;
use App\Http\Controllers\AdminServiceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingRequestController;
use App\Http\Controllers\CaregiverActivityController;
use App\Http\Controllers\CaregiverAssignmentController;
use App\Http\Controllers\CaregiverProfileController;
use App\Http\Controllers\CaregiverScheduleController;
use App\Http\Controllers\ChildController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ParentProfileController;
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
            return redirect()->route('admin.dashboard');
        }

        return view('dashboard');
    })->name('dashboard');

    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');

    Route::get('/admin/bookings', [AdminBookingController::class, 'index'])
        ->name('admin.bookings.index');

    Route::get('/admin/bookings/{booking}', [AdminBookingController::class, 'show'])
        ->name('admin.bookings.show');

    Route::post('/admin/bookings/{booking}/confirm', [AdminBookingController::class, 'confirm'])
        ->name('admin.bookings.confirm');

    Route::post('/admin/bookings/{booking}/assign-caregiver', [AdminBookingController::class, 'assignCaregiver'])
        ->name('admin.bookings.assign-caregiver');

    Route::get('/admin/booking-requests', [AdminBookingRequestController::class, 'index'])
        ->name('admin.booking-requests.index');

    Route::get('/admin/booking-requests/{bookingRequest}', [AdminBookingRequestController::class, 'show'])
        ->name('admin.booking-requests.show');

    Route::post('/admin/booking-requests/{bookingRequest}/approve', [AdminBookingRequestController::class, 'approve'])
        ->name('admin.booking-requests.approve');

    Route::post('/admin/booking-requests/{bookingRequest}/reject', [AdminBookingRequestController::class, 'reject'])
        ->name('admin.booking-requests.reject');

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

    Route::get('/admin/payments', [AdminPaymentController::class, 'index'])
        ->name('admin.payments.index');

    Route::get('/admin/payments/{payment}', [AdminPaymentController::class, 'show'])
        ->name('admin.payments.show');

    Route::post('/admin/payments/{payment}/mark-paid', [AdminPaymentController::class, 'markPaid'])
        ->name('admin.payments.mark-paid');

    Route::post('/admin/payments/{payment}/mark-failed', [AdminPaymentController::class, 'markFailed'])
        ->name('admin.payments.mark-failed');

    Route::get('/admin/services', [AdminServiceController::class, 'index'])
        ->name('admin.services.index');

    Route::get('/admin/services/create', [AdminServiceController::class, 'create'])
        ->name('admin.services.create');

    Route::post('/admin/services', [AdminServiceController::class, 'store'])
        ->name('admin.services.store');

    Route::get('/admin/services/{service}', [AdminServiceController::class, 'show'])
        ->name('admin.services.show');

    Route::get('/admin/services/{service}/edit', [AdminServiceController::class, 'edit'])
        ->name('admin.services.edit');

    Route::post('/admin/services/{service}/update', [AdminServiceController::class, 'update'])
        ->name('admin.services.update');

    Route::post('/admin/services/{service}/status', [AdminServiceController::class, 'changeStatus'])
        ->name('admin.services.status');

    Route::get('/caregiver/assignments', [CaregiverAssignmentController::class, 'index'])
        ->name('caregiver.assignments.index');

    Route::get('/caregiver/assignments/{assignment}', [CaregiverAssignmentController::class, 'show'])
        ->name('caregiver.assignments.show');

    Route::post('/caregiver/assignments/{assignment}/complete', [CaregiverAssignmentController::class, 'complete'])
        ->name('caregiver.assignments.complete');

    Route::get('/caregiver/schedule', [CaregiverScheduleController::class, 'index'])
        ->name('caregiver.schedule.index');

    Route::get('/caregiver/profile', [CaregiverProfileController::class, 'show'])
        ->name('caregiver.profile.show');

    Route::get('/caregiver/profile/edit', [CaregiverProfileController::class, 'edit'])
        ->name('caregiver.profile.edit');

    Route::post('/caregiver/profile/update', [CaregiverProfileController::class, 'update'])
        ->name('caregiver.profile.update');

    Route::get('/caregiver/activities', [CaregiverActivityController::class, 'index'])
        ->name('caregiver.activities.index');

    Route::get('/caregiver/assignments/{assignment}/activities/create', [CaregiverActivityController::class, 'create'])
        ->name('caregiver.activities.create');

    Route::post('/caregiver/assignments/{assignment}/activities', [CaregiverActivityController::class, 'store'])
        ->name('caregiver.activities.store');

    Route::get('/caregiver/activities/{activity}/edit', [CaregiverActivityController::class, 'edit'])
        ->name('caregiver.activities.edit');

    Route::post('/caregiver/activities/{activity}/update', [CaregiverActivityController::class, 'update'])
        ->name('caregiver.activities.update');

    Route::get('/bookings/{booking}/payment', [PaymentController::class, 'create'])
        ->name('payments.create');

    Route::post('/bookings/{booking}/payment', [PaymentController::class, 'store'])
        ->name('payments.store');

    Route::get('/payments/{payment}', [PaymentController::class, 'show'])
        ->name('payments.show');

    Route::get('/profile', [ParentProfileController::class, 'show'])
        ->name('profile.show');

    Route::get('/profile/edit', [ParentProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::post('/profile/update', [ParentProfileController::class, 'update'])
        ->name('profile.update');

    Route::resource('children', ChildController::class);

    Route::patch(
        '/bookings/{booking}/cancel',
        [BookingController::class, 'cancel']
    )->name('bookings.cancel');

    Route::get(
        '/bookings/{booking}/requests/create/{type}',
        [BookingRequestController::class, 'create']
    )->name('booking-requests.create');

    Route::post(
        '/bookings/{booking}/requests',
        [BookingRequestController::class, 'store']
    )->name('booking-requests.store');

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
