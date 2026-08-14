<?php

namespace App\Http\Controllers;

use App\Models\ChildActivity;
use Illuminate\Http\Request;

class AdminActivityController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role === 'admin', 403);

        $query = ChildActivity::with([
            'assignment.caregiver',
            'assignment.booking.child',
        ]);

        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }

        if ($request->filled('activity_date')) {
            $query->whereDate('activity_time', $request->activity_date);
        }

        $activities = $query->latest('activity_time')->get();

        return view('admin.activities.index', compact('activities'));
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
