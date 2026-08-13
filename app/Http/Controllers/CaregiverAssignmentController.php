<?php

namespace App\Http\Controllers;

use App\Models\CaregiverAssignment;
use Illuminate\Http\Request;

class CaregiverAssignmentController extends Controller
{
    public function index(Request $request)
    {
        if (
            $request->user()->role !== 'caregiver'
            || $request->user()->status !== 'active'
        ) {
            abort(403);
        }

        $assignments = CaregiverAssignment::where(
            'caregiver_id',
            $request->user()->id
        )
            ->with([
                'booking.child',
                'booking.service',
            ])
            ->orderBy('assigned_at', 'desc')
            ->get();

        return view(
            'caregiver.assignments.index',
            compact('assignments')
        );
    }

    public function show(
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

        $assignment->load([
            'booking.child',
            'booking.service',
        ]);

        $activities = $assignment->activities()
            ->orderBy('activity_time', 'desc')
            ->get();

        return view(
            'caregiver.assignments.show',
            compact('assignment', 'activities')
        );
    }

    public function complete(
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

        $assignment->load('booking');

        if (
            $assignment->status !== 'assigned'
            || $assignment->booking->status !== 'confirmed'
        ) {
            return redirect()
                ->route('caregiver.assignments.show', $assignment)
                ->with(
                    'error',
                    'Only an active confirmed assignment can be completed.'
                );
        }

        if (!$assignment->activities()->exists()) {
            return redirect()
                ->route('caregiver.assignments.show', $assignment)
                ->with(
                    'error',
                    'Add at least one activity update before completing care.'
                );
        }

        $assignment->update([
            'status' => 'completed',
        ]);

        $assignment->booking->update([
            'status' => 'completed',
        ]);

        return redirect()
            ->route('caregiver.assignments.show', $assignment)
            ->with('success', 'Care assignment completed successfully.');
    }
}
