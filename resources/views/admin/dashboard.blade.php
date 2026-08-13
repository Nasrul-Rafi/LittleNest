@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Admin Dashboard</h1>

            <p>Overview of LittleNest bookings, services and payments.</p>
        </div>
    </div>

    <div class="dashboard-grid">
        <section class="panel">
            <h2>Pending Bookings</h2>
            <div class="stat-number">{{ $pendingBookingCount }}</div>
            <a class="button" href="{{ route('admin.bookings.index') }}">
                View Bookings
            </a>
        </section>

        <section class="panel">
            <h2>Confirmed Bookings</h2>
            <div class="stat-number">{{ $confirmedBookingCount }}</div>
            <p class="muted">Bookings ready for service and assignment.</p>
        </section>

        <section class="panel">
            <h2>Pending Requests</h2>
            <div class="stat-number">{{ $pendingRequestCount }}</div>
            <a
                class="button"
                href="{{ route('admin.booking-requests.index') }}"
            >
                Review Requests
            </a>
        </section>

        <section class="panel">
            <h2>Active Caregivers</h2>
            <div class="stat-number">{{ $activeCaregiverCount }}</div>
            <a class="button" href="{{ route('admin.caregivers.index') }}">
                Manage Caregivers
            </a>
        </section>

        <section class="panel">
            <h2>Active Services</h2>
            <div class="stat-number">{{ $activeServiceCount }}</div>
            <a class="button" href="{{ route('admin.services.index') }}">
                Manage Services
            </a>
        </section>

        <section class="panel">
            <h2>Pending Payments</h2>
            <div class="stat-number">{{ $pendingPaymentCount }}</div>
            <a class="button" href="{{ route('admin.payments.index') }}">
                Review Payments
            </a>
        </section>

        <section class="panel">
            <h2>Total Paid Amount</h2>
            <div class="stat-number">
                ৳{{ number_format((float) $paidTotal, 2) }}
            </div>
            <p class="muted">Sum of payments marked as paid.</p>
        </section>
    </div>

    <section class="panel">
        <div class="page-header">
            <div>
                <h2>Recent Bookings</h2>
                <p>The five most recently created bookings.</p>
            </div>

            <a
                class="button button-secondary"
                href="{{ route('admin.bookings.index') }}"
            >
                View All
            </a>
        </div>

        @if ($recentBookings->isEmpty())
            <p class="muted">No booking has been created yet.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Parent</th>
                            <th>Child</th>
                            <th>Service</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($recentBookings as $booking)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.bookings.show', $booking) }}">
                                        #{{ $booking->booking_id }}
                                    </a>
                                </td>
                                <td>{{ $booking->child->parentProfile->user->name }}</td>
                                <td>{{ $booking->child->full_name }}</td>
                                <td>{{ $booking->service->name }}</td>
                                <td>
                                    <span class="badge badge-{{ $booking->status }}">
                                        {{ $booking->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="panel">
        <div class="page-header">
            <div>
                <h2>Recent Payments</h2>
                <p>The five most recently submitted payments.</p>
            </div>

            <a
                class="button button-secondary"
                href="{{ route('admin.payments.index') }}"
            >
                View All
            </a>
        </div>

        @if ($recentPayments->isEmpty())
            <p class="muted">No payment has been submitted yet.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Payment</th>
                            <th>Parent</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($recentPayments as $payment)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.payments.show', $payment) }}">
                                        #{{ $payment->payment_id }}
                                    </a>
                                </td>
                                <td>
                                    {{ $payment->booking->child->parentProfile->user->name }}
                                </td>
                                <td>৳{{ number_format((float) $payment->amount, 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ $payment->payment_status }}">
                                        {{ $payment->payment_status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
