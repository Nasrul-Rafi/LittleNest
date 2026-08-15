<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ChildActivity;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
{
    public function index(Request $request)
    {
        $this->adminOnly($request);

        $filters = $this->validatedFilters($request);
        $data = $this->reportData(
            $filters['from_date'] ?? null,
            $filters['to_date'] ?? null
        );

        return view('admin.reports.index', $data);
    }

    public function print(Request $request)
    {
        $this->adminOnly($request);

        $filters = $this->validatedFilters($request);
        $data = $this->reportData(
            $filters['from_date'] ?? null,
            $filters['to_date'] ?? null
        );

        return view('admin.reports.print', $data);
    }

    public function exportBookings(Request $request): StreamedResponse
    {
        $this->adminOnly($request);

        $validated = $this->validatedFilters($request);

        $bookings = Booking::with([
            'child.parentProfile.user',
            'service',
        ]);

        $this->applyDateRange(
            $bookings,
            'booking_date',
            $validated['from_date'] ?? null,
            $validated['to_date'] ?? null
        );

        $bookings = $bookings
            ->orderBy('booking_id')
            ->get();

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

    private function reportData(
        ?string $fromDate,
        ?string $toDate
    ): array {
        $bookingQuery = Booking::query();
        $this->applyDateRange(
            $bookingQuery,
            'booking_date',
            $fromDate,
            $toDate
        );

        $paymentQuery = Payment::query();
        $this->applyDateRange(
            $paymentQuery,
            'created_at',
            $fromDate,
            $toDate
        );

        $activityQuery = ChildActivity::query();
        $this->applyDateRange(
            $activityQuery,
            'activity_time',
            $fromDate,
            $toDate
        );

        $summary = [
            'total_bookings' => (clone $bookingQuery)->count(),
            'completed_bookings' => (clone $bookingQuery)
                ->where('status', 'completed')
                ->count(),
            'paid_revenue' => (clone $paymentQuery)
                ->where('payment_status', 'paid')
                ->whereNull('refunded_at')
                ->sum('amount'),
            'refunded_amount' => (clone $paymentQuery)
                ->whereNotNull('refunded_at')
                ->sum('refund_amount'),
            'activity_updates' => (clone $activityQuery)->count(),
            'active_services' => Service::where('status', 'active')->count(),
            'active_caregivers' => User::where('role', 'caregiver')
                ->where('status', 'active')
                ->count(),
        ];

        $serviceUsage = Service::withCount([
            'bookings' => function ($query) use ($fromDate, $toDate) {
                $this->applyDateRange(
                    $query,
                    'booking_date',
                    $fromDate,
                    $toDate
                );
            },
        ])
            ->orderByDesc('bookings_count')
            ->get();

        $caregiverWorkload = User::where('role', 'caregiver')
            ->where('status', 'active')
            ->withCount([
                'caregiverAssignments as workload_count' => function ($query) use ($fromDate, $toDate) {
                    if ($fromDate || $toDate) {
                        $query->whereHas('booking', function ($bookingQuery) use ($fromDate, $toDate) {
                            $this->applyDateRange(
                                $bookingQuery,
                                'booking_date',
                                $fromDate,
                                $toDate
                            );
                        });
                    }
                },
            ])
            ->orderByDesc('workload_count')
            ->orderBy('name')
            ->get();

        $recentPayments = Payment::with([
            'booking.child.parentProfile.user',
            'booking.service',
        ]);

        $this->applyDateRange(
            $recentPayments,
            'created_at',
            $fromDate,
            $toDate
        );

        $recentPayments = $recentPayments
            ->latest('payment_id')
            ->take(10)
            ->get();

        return [
            'summary' => $summary,
            'serviceUsage' => $serviceUsage,
            'caregiverWorkload' => $caregiverWorkload,
            'recentPayments' => $recentPayments,
            'topService' => $serviceUsage->first(),
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ];
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => [
                'nullable',
                'date',
                'after_or_equal:from_date',
            ],
        ]);
    }

    private function applyDateRange(
        Builder $query,
        string $column,
        ?string $fromDate,
        ?string $toDate
    ): void {
        if ($fromDate) {
            $query->whereDate($column, '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate($column, '<=', $toDate);
        }
    }

    private function adminOnly(Request $request): void
    {
        abort_unless($request->user()->role === 'admin', 403);
    }
}
