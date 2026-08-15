<?php

namespace App\Http\Controllers;

use App\Models\ChildActivity;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'caregiver') {
            return $this->caregiverDashboard($user);
        }

        return $this->parentDashboard($user);
    }

    private function parentDashboard($user)
    {
        $parentProfile = $user->parentProfile;

        if (!$parentProfile) {
            return view('dashboard', [
                'dashboardType' => 'parent',
                'activeChildrenCount' => 0,
                'upcomingBookings' => collect(),
                'latestBookings' => collect(),
                'latestActivity' => null,
                'unpaidCount' => 0,
                'dueAmount' => 0,
                'assignedCaregiver' => null,
            ]);
        }

        $activeChildrenCount = $parentProfile
            ->children()
            ->where('status', 'active')
            ->count();

        $upcomingBookings = $parentProfile
            ->bookings()
            ->with([
                'child',
                'service',
                'caregiverAssignment.caregiver',
            ])
            ->whereIn('bookings.status', ['pending', 'confirmed'])
            ->whereDate('booking_date', '>=', today())
            ->orderBy('booking_date')
            ->take(5)
            ->get();

        $latestBookings = $parentProfile
            ->bookings()
            ->with(['child', 'service'])
            ->latest('booking_id')
            ->take(5)
            ->get();

        $latestActivity = ChildActivity::with([
            'assignment.caregiver',
            'assignment.booking.child',
        ])
            ->whereHas('assignment.booking.child', function ($query) use ($parentProfile) {
                $query->where(
                    'parent_profile_id',
                    $parentProfile->parent_profile_id
                );
            })
            ->latest('activity_time')
            ->first();

        $unpaidBookings = $parentProfile
            ->bookings()
            ->where('bookings.status', 'confirmed')
            ->whereDoesntHave('payments', function ($query) {
                $query->where('payment_status', 'paid')
                    ->whereNull('refunded_at');
            })
            ->get();

        $unpaidCount = $unpaidBookings->count();
        $dueAmount = $unpaidBookings->sum('total_amount');

        $assignedBooking = $upcomingBookings
            ->first(function ($booking) {
                return $booking->caregiverAssignment !== null;
            });

        $assignedCaregiver = $assignedBooking
            ?->caregiverAssignment
            ?->caregiver;

        return view('dashboard', compact(
            'activeChildrenCount',
            'upcomingBookings',
            'latestBookings',
            'latestActivity',
            'unpaidCount',
            'dueAmount',
            'assignedCaregiver'
        ) + [
            'dashboardType' => 'parent',
        ]);
    }

    private function caregiverDashboard($user)
    {
        $profile = $user->caregiverProfile;

        $upcomingAssignments = $user
            ->caregiverAssignments()
            ->with([
                'booking.child',
                'booking.service',
            ])
            ->where('status', 'assigned')
            ->whereHas('booking', function ($query) {
                $query->where('status', 'confirmed')
                    ->whereDate('booking_date', '>=', today());
            })
            ->latest('assigned_at')
            ->get()
            ->sortBy(function ($assignment) {
                return $assignment->booking->booking_date;
            })
            ->values();

        $todayAssignments = $upcomingAssignments
            ->filter(function ($assignment) {
                return $assignment->booking->booking_date->isToday();
            })
            ->count();

        $activityCount = ChildActivity::whereHas(
            'assignment',
            function ($query) use ($user) {
                $query->where('caregiver_id', $user->id);
            }
        )->count();

        return view('dashboard', [
            'dashboardType' => 'caregiver',
            'profile' => $profile,
            'upcomingAssignments' => $upcomingAssignments,
            'todayAssignments' => $todayAssignments,
            'activityCount' => $activityCount,
        ]);
    }
}
