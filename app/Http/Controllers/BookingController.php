<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ParentProfile;
use App\Models\TimeSlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $parentProfile = $this->getParentProfile($request);

        $query = $parentProfile->bookings()
            ->with(['child', 'service', 'timeSlot']);

        $search = trim((string) $request->input('search'));
        $status = $request->input('status');
        $month = $request->input('month');

        if ($search !== '') {
            $query->where(function ($bookingQuery) use ($search) {
                $bookingQuery
                    ->where('booking_reference', 'like', '%' . $search . '%')
                    ->orWhereHas('child', function ($childQuery) use ($search) {
                        $childQuery->where('full_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('service', function ($serviceQuery) use ($search) {
                        $serviceQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        if (in_array($status, ['pending', 'confirmed', 'completed', 'cancelled'], true)) {
            $query->where('bookings.status', $status);
        }

        if (is_string($month) && preg_match('/^\d{4}-\d{2}$/', $month)) {
            [$year, $monthNumber] = array_map('intval', explode('-', $month));

            $query->whereYear('booking_date', $year)
                ->whereMonth('booking_date', $monthNumber);
        }

        $bookings = $query
            ->latest('booking_date')
            ->latest('booking_time')
            ->paginate(8)
            ->withQueryString();

        return view('bookings.index', compact(
            'bookings',
            'search',
            'status',
            'month'
        ));
    }

    public function create(Request $request): View
    {
        $parentProfile = $this->getParentProfile($request);

        $children = $parentProfile->children()
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get();

        $timeSlots = TimeSlot::with('service')
            ->withCount([
                'bookings as active_bookings_count' => function ($query) {
                    $query->whereIn('status', ['pending', 'confirmed']);
                },
            ])
            ->where('status', 'open')
            ->whereDate('slot_date', '>=', today())
            ->whereHas('service', function ($query) {
                $query->where('status', 'active');
            })
            ->orderBy('slot_date')
            ->orderBy('start_time')
            ->get()
            ->filter(function (TimeSlot $timeSlot) {
                return $timeSlot->isBookable();
            })
            ->values();

        return view(
            'bookings.create',
            compact('children', 'timeSlots')
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
            'slot_id' => [
                'required',
                'integer',
                'exists:time_slots,slot_id',
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

        $booking = DB::transaction(function () use (
            $validated,
            $child
        ) {
            $timeSlot = TimeSlot::with('service')
                ->lockForUpdate()
                ->find($validated['slot_id']);

            if (!$timeSlot || !$timeSlot->isBookable()) {
                return null;
            }

            return $child->bookings()->create([
                'service_id' => $timeSlot->service_id,
                'slot_id' => $timeSlot->slot_id,
                'booking_date' => $timeSlot->slot_date->format('Y-m-d'),
                'booking_time' => $timeSlot->start_time,
                'special_instructions' =>
                    $validated['special_instructions'] ?? null,
                'status' => 'pending',
                'total_amount' => $timeSlot->service->price,
            ]);
        });

        if (!$booking) {
            return back()
                ->withErrors([
                    'slot_id' =>
                        'The selected time slot is no longer available. Please choose another slot.',
                ])
                ->withInput();
        }

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

        $booking->load([
            'child',
            'service',
            'timeSlot',
            'caregiverAssignment.caregiver',
            'caregiverAssignment.activities',
            'bookingRequests.reviewer',
            'bookingRequests.requestedSlot',
        ]);

        $payments = $booking->payments()
            ->latest('payment_id')
            ->get();

        $latestPayment = $payments->first();

        $bookingRequests = $booking->bookingRequests
            ->sortByDesc('request_id');

        $pendingRequest = $bookingRequests->firstWhere(
            'request_status',
            'pending'
        );

        return view(
            'bookings.show',
            compact(
                'booking',
                'payments',
                'latestPayment',
                'bookingRequests',
                'pendingRequest'
            )
        );
    }

    public function cancel(
        Request $request,
        Booking $booking
    ): RedirectResponse {
        $parentProfile = $this->getParentProfile($request);
        $this->ensureOwnership($booking, $parentProfile);

        $hasPaidPayment = $booking->payments()
            ->where('payment_status', 'paid')
            ->exists();

        if ($hasPaidPayment) {
            return redirect()
                ->route('bookings.show', $booking)
                ->with(
                    'error',
                    'A paid booking cannot be cancelled directly.'
                );
        }

        if ($booking->status === 'confirmed') {
            return redirect()
                ->route('bookings.show', $booking)
                ->with(
                    'error',
                    'Please submit a cancellation request for a confirmed booking.'
                );
        }

        if ($booking->status !== 'pending') {
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
