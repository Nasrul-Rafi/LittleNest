<?php

namespace App\Http\Controllers;

use App\Models\ChildActivity;
use Illuminate\Http\Request;

class AdminActivityController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role === 'admin', 403);

        $request->validate([
            'child' => ['nullable', 'string', 'max:255'],
            'caregiver' => ['nullable', 'string', 'max:255'],
            'booking' => ['nullable', 'string', 'max:100'],
            'activity_type' => ['nullable', 'string', 'max:100'],
            'activity_date' => ['nullable', 'date'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);

        $query = ChildActivity::with([
            'assignment.caregiver',
            'assignment.booking.child',
        ]);

        $child = trim((string) $request->input('child'));
        $caregiver = trim((string) $request->input('caregiver'));
        $booking = trim((string) $request->input('booking'));
        $activityType = trim((string) $request->input('activity_type'));
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        if ($child !== '') {
            $query->whereHas('assignment.booking.child', function ($childQuery) use ($child) {
                $childQuery->where('full_name', 'like', '%' . $child . '%');
            });
        }

        if ($caregiver !== '') {
            $query->whereHas('assignment.caregiver', function ($caregiverQuery) use ($caregiver) {
                $caregiverQuery->where('name', 'like', '%' . $caregiver . '%');
            });
        }

        if ($booking !== '') {
            $query->whereHas('assignment.booking', function ($bookingQuery) use ($booking) {
                $bookingQuery->where('booking_reference', 'like', '%' . $booking . '%');
            });
        }

        if ($activityType !== '') {
            $query->where('activity_type', 'like', '%' . $activityType . '%');
        }

        if ($request->filled('activity_date')) {
            $query->whereDate('activity_time', $request->activity_date);
        }

        if ($fromDate) {
            $query->whereDate('activity_time', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('activity_time', '<=', $toDate);
        }

        $activities = $query
            ->latest('activity_time')
            ->get();

        return view('admin.activities.index', compact(
            'activities',
            'child',
            'caregiver',
            'booking',
            'activityType',
            'fromDate',
            'toDate'
        ));
    }

    public function show(Request $request, ChildActivity $activity)
    {
        abort_unless($request->user()->role === 'admin', 403);

        $activity->load([
            'assignment.caregiver',
            'assignment.booking.child.parentProfile.user',
            'assignment.booking.service',
        ]);

        return view('admin.activities.show', compact('activity'));
    }
}
