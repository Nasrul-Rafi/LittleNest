<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $bookings = Booking::with([
            'child.parentProfile.user',
            'service',
        ])
            ->orderBy('booking_date', 'desc')
            ->get();

        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(Request $request, Booking $booking)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        return view('admin.bookings.show', compact('booking'));
    }

    public function confirm(Request $request, Booking $booking)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        if ($booking->status !== 'pending') {
            return back()->with('error', 'Only pending bookings can be confirmed.');
        }

        $booking->status = 'confirmed';
        $booking->save();

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Booking confirmed successfully.');
    }
}
