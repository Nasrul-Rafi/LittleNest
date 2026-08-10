<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ParentProfile;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $parentProfile = $this->getParentProfile($request);

        $bookings = $parentProfile->bookings()
            ->with(['child', 'service'])
            ->latest('booking_date')
            ->latest('booking_time')
            ->get();

        return view('bookings.index', compact('bookings'));
    }

    public function create(Request $request): View
    {
        $parentProfile = $this->getParentProfile($request);

        $children = $parentProfile->children()
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get();

        $services = Service::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view(
            'bookings.create',
            compact('children', 'services')
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $parentProfile = $this->getParentProfile($request);

        $validated = $request->validate([
            'child_id' => [
                'required',
                'integer',
            ],
            'service_id' => [
                'required',
                'integer',
            ],
            'booking_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'booking_time' => [
                'required',
                'date_format:H:i',
            ],
            'special_instructions' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $child = $parentProfile->children()
            ->where('status', 'active')
            ->whereKey($validated['child_id'])
            ->first();

        abort_unless($child, 403);

        $service = Service::whereKey($validated['service_id'])
            ->where('status', 'active')
            ->first();

        if (!$service) {
            return back()
                ->withErrors([
                    'service_id' =>
                        'The selected service is not available.',
                ])
                ->withInput();
        }

        $bookingDateTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $validated['booking_date']
                . ' '
                . $validated['booking_time']
        );

        if ($bookingDateTime->isPast()) {
            return back()
                ->withErrors([
                    'booking_time' =>
                        'Booking date and time must be in the future.',
                ])
                ->withInput();
        }

        $booking = $child->bookings()->create([
            'service_id' => $service->service_id,
            'booking_date' => $validated['booking_date'],
            'booking_time' => $validated['booking_time'],
            'special_instructions' =>
                $validated['special_instructions'] ?? null,
            'status' => 'pending',
            'total_amount' => $service->price,
        ]);

        return redirect()
            ->route('bookings.show', $booking)
            ->with(
                'success',
                'Booking request submitted successfully.'
            );
    }

    public function show(
        Request $request,
        Booking $booking
    ): View {
        $parentProfile = $this->getParentProfile($request);
        $this->ensureOwnership($booking, $parentProfile);

        $booking->load(['child', 'service']);

        return view('bookings.show', compact('booking'));
    }

    public function cancel(
        Request $request,
        Booking $booking
    ): RedirectResponse {
        $parentProfile = $this->getParentProfile($request);
        $this->ensureOwnership($booking, $parentProfile);

        if (!in_array(
            $booking->status,
            ['pending', 'confirmed'],
            true
        )) {
            return redirect()
                ->route('bookings.show', $booking)
                ->with(
                    'error',
                    'This booking can no longer be cancelled.'
                );
        }

        $booking->update([
            'status' => 'cancelled',
        ]);

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', 'Booking cancelled successfully.');
    }

    private function getParentProfile(
        Request $request
    ): ParentProfile {
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