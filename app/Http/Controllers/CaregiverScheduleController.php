<?php

namespace App\Http\Controllers;

use App\Models\CaregiverAssignment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CaregiverScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureActiveCaregiver($request);

        $assignments = CaregiverAssignment::where(
            'caregiver_id',
            $request->user()->id
        )
            ->whereHas('booking', function ($query) {
                $query->where('status', 'confirmed')
                    ->whereDate('booking_date', '>=', today());
            })
            ->with([
                'booking.child',
                'booking.service',
            ])
            ->get()
            ->sortBy(function ($assignment) {
                return $assignment->booking->booking_date
                    ->format('Y-m-d')
                    . ' '
                    . $assignment->booking->booking_time;
            });

        $scheduleByDate = $assignments->groupBy(function ($assignment) {
            return $assignment->booking->booking_date
                ->format('Y-m-d');
        });

        return view(
            'caregiver.schedule.index',
            compact('assignments', 'scheduleByDate')
        );
    }

    private function ensureActiveCaregiver(Request $request): void
    {
        abort_unless(
            $request->user()->role === 'caregiver'
                && $request->user()->status === 'active',
            403
        );
    }
}
