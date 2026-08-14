<?php

namespace App\Http\Controllers;

use App\Models\ChildActivity;
use Illuminate\Http\Request;

class ParentActivityController extends Controller
{
    public function index(Request $request)
    {
        $parentProfile = $this->parentProfile($request);

        $children = $parentProfile->children()
            ->orderBy('full_name')
            ->get();

        $query = ChildActivity::with([
            'assignment.caregiver.caregiverProfile',
            'assignment.booking.child',
            'assignment.booking.service',
        ])
            ->whereHas('assignment.booking.child', function ($childQuery) use ($parentProfile) {
                $childQuery->where('parent_profile_id', $parentProfile->parent_profile_id);
            });

        if ($request->filled('child_id')) {
            $childId = (int) $request->child_id;
            abort_unless($children->contains('child_id', $childId), 403);

            $query->whereHas('assignment.booking', function ($bookingQuery) use ($childId) {
                $bookingQuery->where('child_id', $childId);
            });
        }

        if ($request->filled('activity_date')) {
            $query->whereDate('activity_time', $request->activity_date);
        }

        $activities = $query
            ->latest('activity_time')
            ->get();

        return view('activities.index', compact('activities', 'children'));
    }

    public function show(Request $request, ChildActivity $activity)
    {
        $parentProfile = $this->parentProfile($request);

        $activity->load([
            'assignment.caregiver.caregiverProfile',
            'assignment.booking.child',
            'assignment.booking.service',
        ]);

        abort_unless(
            $activity->assignment->booking->child->parent_profile_id
                === $parentProfile->parent_profile_id,
            403
        );

        return view('activities.show', compact('activity'));
    }

    private function parentProfile(Request $request)
    {
        abort_unless($request->user()->role === 'parent', 403);
        return $request->user()->parentProfile()->firstOrCreate();
    }
}
