<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingRequest;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $pendingBookingCount = Booking::where('status', 'pending')->count();
        $confirmedBookingCount = Booking::where('status', 'confirmed')->count();

        $pendingRequestCount = BookingRequest::where(
            'request_status',
            'pending'
        )->count();

        $activeCaregiverCount = User::where('role', 'caregiver')
            ->where('status', 'active')
            ->count();

        $activeServiceCount = Service::where('status', 'active')->count();

        $pendingPaymentCount = Payment::where(
            'payment_status',
            'pending'
        )->count();

        $paidTotal = Payment::where('payment_status', 'paid')
            ->sum('amount');

        $recentBookings = Booking::with([
            'child.parentProfile.user',
            'service',
        ])
            ->latest('booking_id')
            ->take(5)
            ->get();

        $recentPayments = Payment::with([
            'booking.child.parentProfile.user',
        ])
            ->latest('payment_id')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'pendingBookingCount',
            'confirmedBookingCount',
            'pendingRequestCount',
            'activeCaregiverCount',
            'activeServiceCount',
            'pendingPaymentCount',
            'paidTotal',
            'recentBookings',
            'recentPayments'
        ));
    }
}
