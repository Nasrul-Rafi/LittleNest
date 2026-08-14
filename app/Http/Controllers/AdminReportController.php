<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ChildActivity;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
{
    public function index(Request $request)
    {
        $this->adminOnly($request);

        $summary = [
            'total_bookings' => Booking::count(),
            'completed_bookings' => Booking::where('status', 'completed')->count(),
            'paid_revenue' => Payment::where('payment_status', 'paid')->sum('amount'),
            'activity_updates' => ChildActivity::count(),
            'active_services' => Service::where('status', 'active')->count(),
            'active_caregivers' => User::where('role', 'caregiver')
                ->where('status', 'active')
                ->count(),
        ];

        $serviceUsage = Service::withCount('bookings')
            ->orderByDesc('bookings_count')
            ->get();

        $recentPayments = Payment::with([
            'booking.child.parentProfile.user',
            'booking.service',
        ])
            ->latest('payment_id')
            ->take(10)
            ->get();

        return view('admin.reports.index', compact(
            'summary',
            'serviceUsage',
            'recentPayments'
        ));
    }

    public function exportBookings(Request $request): StreamedResponse
    {
        $this->adminOnly($request);

        $bookings = Booking::with([
            'child.parentProfile.user',
            'service',
        ])->orderBy('booking_id')->get();

        return response()->streamDownload(function () use ($bookings) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Booking Reference',
                'Parent',
                'Child',
                'Service',
                'Date',
                'Time',
                'Status',
                'Amount',
            ]);

            foreach ($bookings as $booking) {
                fputcsv($handle, [
                    $booking->display_reference,
                    $booking->child->parentProfile->user->name,
                    $booking->child->full_name,
                    $booking->service->name,
                    $booking->booking_date->format('Y-m-d'),
                    $booking->booking_time,
                    $booking->status,
                    $booking->total_amount,
                ]);
            }

            fclose($handle);
        }, 'littlenest-bookings.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function adminOnly(Request $request): void
    {
        abort_unless($request->user()->role === 'admin', 403);
    }
}
