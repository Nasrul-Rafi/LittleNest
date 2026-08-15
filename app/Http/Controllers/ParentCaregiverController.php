<?php

namespace App\Http\Controllers;

use App\Models\CaregiverAssignment;
use Illuminate\Http\Request;

class ParentCaregiverController extends Controller
{
    public function show(Request $request, CaregiverAssignment $assignment)
    {
        abort_unless($request->user()->role === 'parent', 403);

        $parentProfile = $request->user()->parentProfile()->firstOrCreate();

        $assignment->load([
            'caregiver.caregiverProfile',
            'booking.child',
            'booking.service',
        ]);

        abort_unless(
            $assignment->booking->child->parent_profile_id
                === $parentProfile->parent_profile_id,
            403
        );

        $currentWorkload = $assignment->caregiver
            ->caregiverAssignments()
            ->where('status', 'assigned')
            ->count();

        return view('caregivers.show', compact(
            'assignment',
            'currentWorkload'
        ));
    }
}
