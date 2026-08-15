<?php

namespace App\Http\Controllers;

use App\Models\BookingRequest;
use App\Models\TimeSlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminBookingRequestController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAdmin($request);

        $bookingRequests = BookingRequest::with([
            'booking.child.parentProfile.user',
            'booking.service',
            'requestedSlot',
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
            'requestedSlot',
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
            DB::transaction(function () use (
                $booking,
                $bookingRequest,
                $request,
                $validated
            ) {
                $booking->update(['status' => 'cancelled']);

                $paidPayments = $booking->payments()
                    ->where('payment_status', 'paid')
                    ->whereNull('refunded_at')
                    ->get();

                foreach ($paidPayments as $payment) {
                    $payment->update([
                        'refund_amount' => $payment->amount,
                        'refunded_at' => now(),
                        'refund_note' =>
                            'Automatically refunded after approved cancellation request.',
                    ]);
                }

                $bookingRequest->update([
                    'request_status' => 'approved',
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                    'admin_note' => $validated['admin_note'] ?? null,
                ]);
            });

            return redirect()
                ->route('admin.booking-requests.show', $bookingRequest)
                ->with(
                    'success',
                    'Cancellation approved. Any paid payment was recorded as refunded.'
                );
        }

        $updated = $this->approveReschedule(
            $bookingRequest
        );

        if (!$updated) {
            return back()->with(
                'error',
                'The requested time slot is no longer available. Ask the parent to choose another slot.'
            );
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

    private function approveReschedule(
        BookingRequest $bookingRequest
    ): bool {
        $booking = $bookingRequest->booking;

        if (!$bookingRequest->requested_slot_id) {
            $booking->update([
                'booking_date' => $bookingRequest->requested_date,
                'booking_time' => $bookingRequest->requested_time,
            ]);

            return true;
        }

        return DB::transaction(function () use (
            $booking,
            $bookingRequest
        ) {
            $timeSlot = TimeSlot::with('service')
                ->lockForUpdate()
                ->find($bookingRequest->requested_slot_id);

            if (
                !$timeSlot
                || $timeSlot->service_id !== $booking->service_id
                || !$timeSlot->isBookable()
            ) {
                return false;
            }

            $booking->update([
                'slot_id' => $timeSlot->slot_id,
                'booking_date' => $timeSlot->slot_date->format('Y-m-d'),
                'booking_time' => $timeSlot->start_time,
            ]);

            return true;
        });
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()->role === 'admin', 403);
    }
}
