<?php

namespace App\Http\Controllers;

use App\Models\CaregiverAssignment;
use App\Models\ChildActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CaregiverActivityController extends Controller
{
    public function index(Request $request)
    {
        if (
            $request->user()->role !== 'caregiver'
            || $request->user()->status !== 'active'
        ) {
            abort(403);
        }

        $activityTypes = [
            'check-in',
            'check-out',
            'meal',
            'nap',
            'play',
            'learning',
            'toilet',
            'health',
            'medicine',
            'mood',
            'special-notes',
        ];

        $validated = $request->validate([
            'activity_type' => [
                'nullable',
                'in:' . implode(',', $activityTypes),
            ],
            'activity_date' => ['nullable', 'date'],
        ]);

        $activities = ChildActivity::whereHas(
            'assignment',
            function ($query) use ($request) {
                $query->where(
                    'caregiver_id',
                    $request->user()->id
                );
            }
        )
            ->with([
                'assignment.booking.child',
                'assignment.booking.service',
            ])
            ->when(
                $validated['activity_type'] ?? null,
                function ($query, $activityType) {
                    $query->where('activity_type', $activityType);
                }
            )
            ->when(
                $validated['activity_date'] ?? null,
                function ($query, $activityDate) {
                    $query->whereDate('activity_time', $activityDate);
                }
            )
            ->orderBy('activity_time', 'desc')
            ->get();

        return view(
            'caregiver.activities.index',
            compact('activities', 'activityTypes')
        );
    }

    public function create(
        Request $request,
        CaregiverAssignment $assignment
    ) {
        if (
            $request->user()->role !== 'caregiver'
            || $request->user()->status !== 'active'
        ) {
            abort(403);
        }

        if ($assignment->caregiver_id !== $request->user()->id) {
            abort(403);
        }

        if ($assignment->booking->status !== 'confirmed') {
            return redirect()
                ->route('caregiver.assignments.show', $assignment)
                ->with('error', 'Activities can only be added to confirmed bookings.');
        }

        return view(
            'caregiver.activities.create',
            compact('assignment')
        );
    }

    public function store(
        Request $request,
        CaregiverAssignment $assignment
    ) {
        if (
            $request->user()->role !== 'caregiver'
            || $request->user()->status !== 'active'
        ) {
            abort(403);
        }

        if ($assignment->caregiver_id !== $request->user()->id) {
            abort(403);
        }

        if ($assignment->booking->status !== 'confirmed') {
            return redirect()
                ->route('caregiver.assignments.show', $assignment)
                ->with('error', 'Activities can only be added to confirmed bookings.');
        }

        $validated = $request->validate([
            'activity_type' => [
                'required',
                'in:check-in,check-out,meal,nap,play,learning,toilet,health,medicine,mood,special-notes',
            ],
            'details' => ['nullable', 'string', 'max:2000'],
            'activity_time' => ['required', 'date', 'before_or_equal:now'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store(
                'activity-photos',
                'public'
            );
        }

        $assignment->activities()->create([
            'activity_type' => $validated['activity_type'],
            'details' => $validated['details'] ?? null,
            'activity_time' => $validated['activity_time'],
            'photo_path' => $photoPath,
        ]);

        return redirect()
            ->route('caregiver.assignments.show', $assignment)
            ->with('success', 'Activity update added successfully.');
    }

    public function edit(Request $request, ChildActivity $activity)
    {
        if (
            $request->user()->role !== 'caregiver'
            || $request->user()->status !== 'active'
        ) {
            abort(403);
        }

        $activity->load('assignment.booking');

        if ($activity->assignment->caregiver_id !== $request->user()->id) {
            abort(403);
        }

        return view(
            'caregiver.activities.edit',
            compact('activity')
        );
    }

    public function update(Request $request, ChildActivity $activity)
    {
        if (
            $request->user()->role !== 'caregiver'
            || $request->user()->status !== 'active'
        ) {
            abort(403);
        }

        $activity->load('assignment.booking');

        if ($activity->assignment->caregiver_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'activity_type' => [
                'required',
                'in:check-in,check-out,meal,nap,play,learning,toilet,health,medicine,mood,special-notes',
            ],
            'details' => ['nullable', 'string', 'max:2000'],
            'activity_time' => ['required', 'date', 'before_or_equal:now'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $photoPath = $activity->photo_path;

        if ($request->hasFile('photo')) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            $photoPath = $request->file('photo')->store(
                'activity-photos',
                'public'
            );
        }

        $activity->update([
            'activity_type' => $validated['activity_type'],
            'details' => $validated['details'] ?? null,
            'activity_time' => $validated['activity_time'],
            'photo_path' => $photoPath,
        ]);

        return redirect()
            ->route(
                'caregiver.assignments.show',
                $activity->assignment
            )
            ->with('success', 'Activity update changed successfully.');
    }
}
