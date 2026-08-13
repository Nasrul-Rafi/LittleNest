<?php

namespace App\Http\Controllers;

use App\Models\BookingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminBookingRequestController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAdmin($request);

        $bookingRequests = BookingRequest::with([
            'booking.child.parentProfile.user',
            'booking.service',
            'reviewer',
        ])
            ->latest('request_id')
            ->get();

        return view(
            'admin.booking-requests.index',
            compact('bookingRequests')
        );
    }

    public function show(
        Request $request,
        BookingRequest $bookingRequest
    ): View {
        $this->ensureAdmin($request);

        $bookingRequest->load([
            'booking.child.parentProfile.user',
            'booking.service',
            'reviewer',
        ]);

        return view(
            'admin.booking-requests.show',
            compact('bookingRequest')
        );
    }

    public function approve(
        Request $request,
        BookingRequest $bookingRequest
    ): RedirectResponse {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($bookingRequest->request_status !== 'pending') {
            return back()->with(
                'error',
                'Only pending requests can be approved.'
            );
        }

        $booking = $bookingRequest->booking;

        if ($booking->status !== 'confirmed') {
            return back()->with(
                'error',
                'The related booking is no longer confirmed.'
            );
        }

        if ($bookingRequest->request_type === 'cancellation') {
            $booking->update(['status' => 'cancelled']);
        } else {
            $booking->update([
                'booking_date' => $bookingRequest->requested_date,
                'booking_time' => $bookingRequest->requested_time,
            ]);
        }

        $bookingRequest->update([
            'request_status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_note' => $validated['admin_note'] ?? null,
        ]);

        return redirect()
            ->route('admin.booking-requests.show', $bookingRequest)
            ->with('success', 'Booking request approved successfully.');
    }

    public function reject(
        Request $request,
        BookingRequest $bookingRequest
    ): RedirectResponse {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:2000'],
        ]);

        if ($bookingRequest->request_status !== 'pending') {
            return back()->with(
                'error',
                'Only pending requests can be rejected.'
            );
        }

        $bookingRequest->update([
            'request_status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_note' => $validated['admin_note'],
        ]);

        return redirect()
            ->route('admin.booking-requests.show', $bookingRequest)
            ->with('success', 'Booking request rejected successfully.');
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()->role === 'admin', 403);
    }
}
