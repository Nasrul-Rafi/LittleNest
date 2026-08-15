<?php

namespace App\Http\Controllers;

use App\Models\CaregiverAssignment;
use App\Models\User;
use Illuminate\Http\Request;

class AdminAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $this->adminOnly($request);

        $query = CaregiverAssignment::with([
            'caregiver.caregiverProfile',
            'booking.child.parentProfile.user',
            'booking.service',
        ]);

        $status = $request->input('status');
        $caregiverId = $request->input('caregiver_id');

        if (in_array($status, ['assigned', 'completed'], true)) {
            $query->where('status', $status);
        }

        if ($caregiverId) {
            $query->where('caregiver_id', $caregiverId);
        }

        $assignments = $query
            ->latest('assigned_at')
            ->get();

        $caregivers = User::where('role', 'caregiver')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.assignments.index', compact(
            'assignments',
            'caregivers',
            'status',
            'caregiverId'
        ));
    }

    public function show(Request $request, CaregiverAssignment $assignment)
    {
        $this->adminOnly($request);

        $assignment->load([
            'caregiver.caregiverProfile',
            'assignedBy',
            'booking.child.parentProfile.user',
            'booking.service',
            'booking.timeSlot',
            'activities',
        ]);

        return view('admin.assignments.show', compact('assignment'));
    }

    private function adminOnly(Request $request): void
    {
        abort_unless($request->user()->role === 'admin', 403);
    }
}
