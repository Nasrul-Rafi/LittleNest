<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ParentProfile;
use App\Models\TimeSlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingRequestController extends Controller
{
    public function create(
        Request $request,
        Booking $booking,
        string $type
    ): View {
        $parentProfile = $this->getParentProfile($request);
        $this->ensureOwnership($booking, $parentProfile);
        $this->ensureRequestCanBeCreated($booking, $type);

        $timeSlots = collect();

        if ($type === 'reschedule') {
            $timeSlots = TimeSlot::with('service')
                ->withCount([
                    'bookings as active_bookings_count' => function ($query) {
                        $query->whereIn('status', ['pending', 'confirmed']);
                    },
                ])
                ->where('service_id', $booking->service_id)
                ->where('status', 'open')
                ->whereDate('slot_date', '>=', today())
                ->when($booking->slot_id, function ($query) use ($booking) {
                    $query->where('slot_id', '!=', $booking->slot_id);
                })
                ->orderBy('slot_date')
                ->orderBy('start_time')
                ->get()
                ->filter(function (TimeSlot $timeSlot) {
                    return $timeSlot->isBookable();
                })
                ->values();
        }

        return view(
            'booking-requests.create',
            compact('booking', 'type', 'timeSlots')
        );
    }

    public function store(
        Request $request,
        Booking $booking
    ): RedirectResponse {
        $parentProfile = $this->getParentProfile($request);
        $this->ensureOwnership($booking, $parentProfile);

        $validated = $request->validate([
            'request_type' => [
                'required',
                'in:cancellation,reschedule',
            ],
            'requested_slot_id' => [
                'required_if:request_type,reschedule',
                'nullable',
                'integer',
                'exists:time_slots,slot_id',
            ],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $this->ensureRequestCanBeCreated(
            $booking,
            $validated['request_type']
        );

        $requestedSlot = null;

        if ($validated['request_type'] === 'reschedule') {
            $requestedSlot = TimeSlot::with('service')
                ->find($validated['requested_slot_id']);

            if (
                !$requestedSlot
                || $requestedSlot->service_id !== $booking->service_id
                || !$requestedSlot->isBookable()
                || $requestedSlot->slot_id === $booking->slot_id
            ) {
                return back()
                    ->withErrors([
                        'requested_slot_id' =>
                            'Please choose another available slot for the same service.',
                    ])
                    ->withInput();
            }
        }

        $booking->bookingRequests()->create([
            'request_type' => $validated['request_type'],
            'requested_slot_id' => $requestedSlot?->slot_id,
            'requested_date' => $requestedSlot
                ? $requestedSlot->slot_date->format('Y-m-d')
                : null,
            'requested_time' => $requestedSlot
                ? $requestedSlot->start_time
                : null,
            'reason' => $validated['reason'],
            'request_status' => 'pending',
        ]);

        return redirect()
            ->route('bookings.show', $booking)
            ->with(
                'success',
                'Your booking request was submitted for Admin review.'
            );
    }

    private function ensureRequestCanBeCreated(
        Booking $booking,
        string $type
    ): void {
        abort_unless(
            in_array($type, ['cancellation', 'reschedule'], true),
            404
        );

        abort_if(
            $booking->status !== 'confirmed',
            422,
            'Only confirmed bookings can have change requests.'
        );

        abort_if(
            $booking->bookingRequests()
                ->where('request_status', 'pending')
                ->exists(),
            422,
            'This booking already has a pending request.'
        );
    }

    private function getParentProfile(Request $request): ParentProfile
    {
        abort_unless($request->user()->role === 'parent', 403);

        return $request->user()
            ->parentProfile()
            ->firstOrCreate();
    }

    private function ensureOwnership(
        Booking $booking,
        ParentProfile $parentProfile
    ): void {
        $booking->loadMissing('child');

        abort_unless(
            $booking->child->parent_profile_id
                === $parentProfile->parent_profile_id,
            403
        );
    }
}
