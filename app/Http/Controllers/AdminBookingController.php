<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $query = Booking::with([
            'child.parentProfile.user',
            'service',
            'timeSlot',
            'caregiverAssignment.caregiver',
        ]);

        $search = trim((string) $request->input('search'));
        $status = $request->input('status');
        $bookingDate = $request->input('booking_date');

        if ($search !== '') {
            $query->where(function ($bookingQuery) use ($search) {
                $bookingQuery
                    ->where('booking_reference', 'like', '%' . $search . '%')
                    ->orWhereHas('child', function ($childQuery) use ($search) {
                        $childQuery->where('full_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('child.parentProfile.user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('service', function ($serviceQuery) use ($search) {
                        $serviceQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        if (in_array($status, ['pending', 'confirmed', 'completed', 'cancelled'], true)) {
            $query->where('status', $status);
        }

        if ($bookingDate) {
            $query->whereDate('booking_date', $bookingDate);
        }

        $bookings = $query
            ->orderByDesc('booking_date')
            ->orderByDesc('booking_id')
            ->get();

        return view('admin.bookings.index', compact(
            'bookings',
            'search',
            'status',
            'bookingDate'
        ));
    }

    public function show(Request $request, Booking $booking)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $booking->load([
            'child.parentProfile.user',
            'service',
            'timeSlot',
            'caregiverAssignment.caregiver',
            'caregiverAssignment.activities',
        ]);

        $caregivers = User::where('role', 'caregiver')
            ->where('status', 'active')
            ->whereHas('caregiverProfile', function ($query) {
                $query->where('availability_status', 'available');
            })
            ->with('caregiverProfile')
            ->orderBy('name')
            ->get();

        return view(
            'admin.bookings.show',
            compact('booking', 'caregivers')
        );
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

    public function reject(Request $request, Booking $booking)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        if ($booking->status !== 'pending') {
            return back()->with(
                'error',
                'Only pending bookings can be rejected.'
            );
        }

        $booking->status = 'cancelled';
        $booking->save();

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with(
                'success',
                'Booking rejected successfully. The reserved slot is available again.'
            );
    }

    public function assignCaregiver(Request $request, Booking $booking)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        if ($booking->status !== 'confirmed') {
            return back()->with(
                'error',
                'Only confirmed bookings can receive a caregiver.'
            );
        }

        $validated = $request->validate([
            'caregiver_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $caregiver = User::whereKey($validated['caregiver_id'])
            ->where('role', 'caregiver')
            ->where('status', 'active')
            ->whereHas('caregiverProfile', function ($query) {
                $query->where('availability_status', 'available');
            })
            ->first();

        if (!$caregiver) {
            return back()->with(
                'error',
                'Please select an active and available caregiver.'
            );
        }

        $assignment = $booking->caregiverAssignment;

        if ($assignment) {
            if (
                $assignment->caregiver_id !== $caregiver->id
                && $assignment->activities()->exists()
            ) {
                return back()->with(
                    'error',
                    'This caregiver cannot be changed because activity updates already exist.'
                );
            }

            $assignment->caregiver_id = $caregiver->id;
            $assignment->assigned_by = $request->user()->id;
            $assignment->assigned_at = now();
            $assignment->status = 'assigned';
            $assignment->save();

            $message = 'Caregiver reassigned successfully.';
        } else {
            $booking->caregiverAssignment()->create([
                'caregiver_id' => $caregiver->id,
                'assigned_by' => $request->user()->id,
                'assigned_at' => now(),
                'status' => 'assigned',
            ]);

            $message = 'Caregiver assigned successfully.';
        }

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', $message);
    }
}
