<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ParentProfile;
use Carbon\Carbon;
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

        return view(
            'booking-requests.create',
            compact('booking', 'type')
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
            'requested_date' => [
                'required_if:request_type,reschedule',
                'nullable',
                'date',
                'after_or_equal:today',
            ],
            'requested_time' => [
                'required_if:request_type,reschedule',
                'nullable',
                'date_format:H:i',
            ],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $this->ensureRequestCanBeCreated(
            $booking,
            $validated['request_type']
        );

        if ($validated['request_type'] === 'reschedule') {
            $requestedDateTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                $validated['requested_date']
                    . ' '
                    . $validated['requested_time']
            );

            if ($requestedDateTime->isPast()) {
                return back()
                    ->withErrors([
                        'requested_time' =>
                            'Requested date and time must be in the future.',
                    ])
                    ->withInput();
            }
        }

        $booking->bookingRequests()->create([
            'request_type' => $validated['request_type'],
            'requested_date' =>
                $validated['request_type'] === 'reschedule'
                    ? $validated['requested_date']
                    : null,
            'requested_time' =>
                $validated['request_type'] === 'reschedule'
                    ? $validated['requested_time']
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
