@extends('layouts.parent')

@section('content')
@if ($dashboardType === 'caregiver')
    <div class="page-header">
        <div>
            <h1>Caregiver Dashboard</h1>
            <p>See today’s care schedule, assignments and activity progress.</p>
        </div>
    </div>

    <div class="dashboard-grid">
        <section class="panel">
            <h2>Today's Schedule</h2>
            <div class="stat-number">{{ $todayAssignments }}</div>
            <p class="muted">Confirmed assignments scheduled for today.</p>
            <a class="button" href="{{ route('caregiver.schedule.index') }}">View Schedule</a>
        </section>

        <section class="panel">
            <h2>Upcoming Assignments</h2>
            <div class="stat-number">{{ $upcomingAssignments->count() }}</div>
            <p class="muted">Assigned child-care bookings from today onward.</p>
            <a class="button" href="{{ route('caregiver.assignments.index') }}">View Assigned Children</a>
        </section>

        <section class="panel">
            <h2>Activity Updates</h2>
            <div class="stat-number">{{ $activityCount }}</div>
            <p class="muted">Total care updates recorded by you.</p>
            <a class="button" href="{{ route('caregiver.activities.index') }}">Activity History</a>
        </section>

        <section class="panel">
            <h2>Availability</h2>
            <p><span class="badge badge-{{ $profile?->availability_status }}">{{ $profile?->availability_status ?? 'unavailable' }}</span></p>
            <p class="muted">{{ $profile?->qualification ?: 'Professional profile not completed.' }}</p>
            <a class="button button-secondary" href="{{ route('caregiver.profile.show') }}">View Profile</a>
        </section>
    </div>

    <section class="panel">
        <h2>Upcoming Care</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Booking</th>
                        <th>Child</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($upcomingAssignments->take(5) as $assignment)
                        <tr>
                            <td>{{ $assignment->booking->display_reference }}</td>
                            <td>{{ $assignment->booking->child->full_name }}</td>
                            <td>{{ $assignment->booking->service->name }}</td>
                            <td>{{ $assignment->booking->booking_date->format('d M Y') }}</td>
                            <td>{{ $assignment->booking->booking_time }}</td>
                            <td><a class="button button-secondary button-small" href="{{ route('caregiver.assignments.show', $assignment) }}">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No upcoming assignments.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@else
    <div class="page-header">
        <div>
            <h1>Parent Dashboard</h1>
            <p>Manage your family’s care journey in one place.</p>
        </div>
        <a class="button" href="{{ route('parent.services.index') }}">Book a Service</a>
    </div>

    <div class="dashboard-grid">
        <section class="panel">
            <h2>Active Children</h2>
            <div class="stat-number">{{ $activeChildrenCount }}</div>
            <p class="muted">Child profiles currently active.</p>
            <a class="button button-secondary" href="{{ route('children.index') }}">My Children</a>
        </section>

        <section class="panel">
            <h2>Upcoming Bookings</h2>
            <div class="stat-number">{{ $upcomingBookings->count() }}</div>
            <p class="muted">
                @if($upcomingBookings->first())
                    Next: {{ $upcomingBookings->first()->booking_date->format('d M') }}
                @else
                    No upcoming booking
                @endif
            </p>
            <a class="button button-secondary" href="{{ route('bookings.index') }}">My Bookings</a>
        </section>

        <section class="panel">
            <h2>Payments Due</h2>
            <div class="stat-number compact-stat">৳{{ number_format((float) $dueAmount) }}</div>
            <p class="muted">{{ $unpaidCount }} confirmed booking{{ $unpaidCount === 1 ? '' : 's' }} without a completed payment.</p>
            <a class="button button-secondary" href="{{ route('payments.index') }}">Payments</a>
        </section>

        <section class="panel">
            <h2>Assigned Caregiver</h2>
            @if($assignedCaregiver)
                <div class="stat-text">{{ $assignedCaregiver->name }}</div>
                <p class="muted">{{ $assignedCaregiver->caregiverProfile?->qualification ?: 'Caregiver' }}</p>
            @else
                <div class="stat-text">Not assigned</div>
                <p class="muted">A caregiver appears here after Admin confirms an assignment.</p>
            @endif
        </section>
    </div>

    <div class="dashboard-grid">
        <section class="panel">
            <h2>Upcoming Booking</h2>
            @if($upcomingBookings->first())
                @php($booking = $upcomingBookings->first())
                <p><strong>{{ $booking->display_reference }}</strong></p>
                <p>{{ $booking->child->full_name }} · {{ $booking->service->name }}</p>
                <p class="muted">{{ $booking->booking_date->format('d M Y') }} · {{ $booking->booking_time }}</p>
                <p><span class="badge badge-{{ $booking->status }}">{{ $booking->status }}</span></p>
                <a class="button button-secondary" href="{{ route('bookings.show', $booking) }}">View Booking</a>
            @else
                <p class="muted">No upcoming booking yet.</p>
                <a class="button" href="{{ route('parent.services.index') }}">Browse Services</a>
            @endif
        </section>

        <section class="panel">
            <h2>Latest Activity</h2>
            @if($latestActivity)
                <p><strong>{{ ucfirst($latestActivity->activity_type) }}</strong></p>
                <p>{{ $latestActivity->details }}</p>
                <p class="muted">
                    {{ $latestActivity->activity_time->format('d M Y, h:i A') }}
                    · {{ $latestActivity->assignment->caregiver->name }}
                </p>
                <a class="button button-secondary" href="{{ route('activities.show', $latestActivity) }}">View Activity</a>
            @else
                <p class="muted">No caregiver activity has been posted yet.</p>
                <a class="button button-secondary" href="{{ route('activities.index') }}">Child Activities</a>
            @endif
        </section>
    </div>

    <section class="panel">
        <h2>Recent Bookings</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Booking</th>
                        <th>Child</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestBookings as $booking)
                        <tr>
                            <td>{{ $booking->display_reference }}</td>
                            <td>{{ $booking->child->full_name }}</td>
                            <td>{{ $booking->service->name }}</td>
                            <td>{{ $booking->booking_date->format('d M Y') }}</td>
                            <td><span class="badge badge-{{ $booking->status }}">{{ $booking->status }}</span></td>
                            <td><a class="button button-secondary button-small" href="{{ route('bookings.show', $booking) }}">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No bookings created yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endif
@endsection
