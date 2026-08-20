<?php

use App\Http\Controllers\AdminActivityController;
use App\Http\Controllers\AdminAssignmentController;
use App\Http\Controllers\AdminBookingController;
use App\Http\Controllers\AdminBookingRequestController;
use App\Http\Controllers\AdminCaregiverController;
use App\Http\Controllers\AdminChildController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminInquiryController;
use App\Http\Controllers\AdminParentController;
use App\Http\Controllers\AdminPaymentController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AdminServiceController;
use App\Http\Controllers\AdminTimeSlotController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingRequestController;
use App\Http\Controllers\CaregiverActivityController;
use App\Http\Controllers\CaregiverAssignmentController;
use App\Http\Controllers\CaregiverProfileController;
use App\Http\Controllers\CaregiverScheduleController;
use App\Http\Controllers\ChildController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ParentActivityController;
use App\Http\Controllers\ParentCaregiverController;
use App\Http\Controllers\ParentProfileController;
use App\Http\Controllers\ParentServiceController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PublicPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/services', [PublicPageController::class, 'services'])->name('public.services');
Route::get('/services/{service}', [PublicPageController::class, 'service'])->name('public.services.show');
Route::get('/about', [PublicPageController::class, 'about'])->name('about');
Route::get('/contact', [PublicPageController::class, 'contact'])->name('contact');
Route::post('/contact', [PublicPageController::class, 'sendContact'])->name('contact.store');

Route::match(['get', 'post'], '/payments/sslcommerz/success', [PaymentController::class, 'sslSuccess'])
    ->name('sslcommerz.success');
Route::match(['get', 'post'], '/payments/sslcommerz/fail', [PaymentController::class, 'sslFail'])
    ->name('sslcommerz.fail');
Route::match(['get', 'post'], '/payments/sslcommerz/cancel', [PaymentController::class, 'sslCancel'])
    ->name('sslcommerz.cancel');
Route::post('/payments/sslcommerz/ipn', [PaymentController::class, 'sslIpn'])
    ->name('sslcommerz.ipn');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])
        ->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');

    Route::get('/admin/bookings', [AdminBookingController::class, 'index'])
        ->name('admin.bookings.index');
    Route::get('/admin/bookings/{booking}', [AdminBookingController::class, 'show'])
        ->name('admin.bookings.show');
    Route::post('/admin/bookings/{booking}/confirm', [AdminBookingController::class, 'confirm'])
        ->name('admin.bookings.confirm');
    Route::post('/admin/bookings/{booking}/reject', [AdminBookingController::class, 'reject'])
        ->name('admin.bookings.reject');
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

    Route::get('/admin/parents', [AdminParentController::class, 'index'])
        ->name('admin.parents.index');
    Route::get('/admin/parents/create', [AdminParentController::class, 'create'])
        ->name('admin.parents.create');
    Route::post('/admin/parents', [AdminParentController::class, 'store'])
        ->name('admin.parents.store');
    Route::get('/admin/parents/{parent}', [AdminParentController::class, 'show'])
        ->name('admin.parents.show');
    Route::get('/admin/parents/{parent}/edit', [AdminParentController::class, 'edit'])
        ->name('admin.parents.edit');
    Route::post('/admin/parents/{parent}/update', [AdminParentController::class, 'update'])
        ->name('admin.parents.update');
    Route::post('/admin/parents/{parent}/status', [AdminParentController::class, 'changeStatus'])
        ->name('admin.parents.status');

    Route::get('/admin/assignments', [AdminAssignmentController::class, 'index'])
        ->name('admin.assignments.index');
    Route::get('/admin/assignments/{assignment}', [AdminAssignmentController::class, 'show'])
        ->name('admin.assignments.show');

    Route::get('/admin/children', [AdminChildController::class, 'index'])
        ->name('admin.children.index');
    Route::get('/admin/children/{child}', [AdminChildController::class, 'show'])
        ->name('admin.children.show');

    Route::get('/admin/activities', [AdminActivityController::class, 'index'])
        ->name('admin.activities.index');
    Route::get('/admin/activities/{activity}', [AdminActivityController::class, 'show'])
        ->name('admin.activities.show');

    Route::get('/admin/reports', [AdminReportController::class, 'index'])
        ->name('admin.reports.index');
    Route::get('/admin/reports/bookings.csv', [AdminReportController::class, 'exportBookings'])
        ->name('admin.reports.bookings-csv');
    Route::get('/admin/reports/print', [AdminReportController::class, 'print'])
        ->name('admin.reports.print');

    Route::get('/admin/inquiries', [AdminInquiryController::class, 'index'])
        ->name('admin.inquiries.index');
    Route::get('/admin/inquiries/{message}', [AdminInquiryController::class, 'show'])
        ->name('admin.inquiries.show');
    Route::post('/admin/inquiries/{message}/status', [AdminInquiryController::class, 'updateStatus'])
        ->name('admin.inquiries.status');

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
    Route::post('/admin/payments/{payment}/refund', [AdminPaymentController::class, 'refund'])
        ->name('admin.payments.refund');
    Route::post('/admin/payments/{payment}/refund-status', [AdminPaymentController::class, 'checkRefundStatus'])
        ->name('admin.payments.refund-status');

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

    Route::get('/admin/time-slots', [AdminTimeSlotController::class, 'index'])
        ->name('admin.time-slots.index');
    Route::get('/admin/time-slots/create', [AdminTimeSlotController::class, 'create'])
        ->name('admin.time-slots.create');
    Route::post('/admin/time-slots', [AdminTimeSlotController::class, 'store'])
        ->name('admin.time-slots.store');
    Route::get('/admin/time-slots/{timeSlot}/edit', [AdminTimeSlotController::class, 'edit'])
        ->name('admin.time-slots.edit');
    Route::post('/admin/time-slots/{timeSlot}/update', [AdminTimeSlotController::class, 'update'])
        ->name('admin.time-slots.update');
    Route::post('/admin/time-slots/{timeSlot}/status', [AdminTimeSlotController::class, 'changeStatus'])
        ->name('admin.time-slots.status');
});

Route::middleware(['auth', 'role:caregiver'])->group(function () {
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
});

Route::middleware(['auth', 'role:parent'])->group(function () {
    Route::get('/parent/services', [ParentServiceController::class, 'index'])
        ->name('parent.services.index');
    Route::get('/parent/services/{service}', [ParentServiceController::class, 'show'])
        ->name('parent.services.show');

    Route::get('/activities', [ParentActivityController::class, 'index'])
        ->name('activities.index');
    Route::get('/activities/summary.csv', [ParentActivityController::class, 'summary'])
        ->name('activities.summary');
    Route::get('/activities/{activity}', [ParentActivityController::class, 'show'])
        ->name('activities.show');
    Route::get('/assigned-caregivers/{assignment}', [ParentCaregiverController::class, 'show'])
        ->name('caregivers.show');

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/export.csv', [PaymentController::class, 'export'])->name('payments.export');
    Route::get('/bookings/{booking}/payment', [PaymentController::class, 'create'])
        ->name('payments.create');
    Route::post('/bookings/{booking}/payment', [PaymentController::class, 'store'])
        ->name('payments.store');
    Route::post('/payments/{payment}/check-status', [PaymentController::class, 'checkStatus'])
        ->name('payments.check-status');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])
        ->name('payments.show');
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])
        ->name('payments.receipt');

    Route::get('/profile', [ParentProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ParentProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ParentProfileController::class, 'update'])->name('profile.update');

    Route::resource('children', ChildController::class);

    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])
        ->name('bookings.cancel');
    Route::get('/bookings/{booking}/requests/create/{type}', [BookingRequestController::class, 'create'])
        ->name('booking-requests.create');
    Route::post('/bookings/{booking}/requests', [BookingRequestController::class, 'store'])
        ->name('booking-requests.store');
    Route::resource('bookings', BookingController::class)
        ->only(['index', 'create', 'store', 'show']);
});
