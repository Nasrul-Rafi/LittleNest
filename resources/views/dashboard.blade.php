@extends('layouts.parent')

@section('content')
    @if (auth()->user()->role === 'caregiver')
        @php
            $caregiverProfile = auth()->user()->caregiverProfile;
            $assignmentCount = auth()->user()
                ->caregiverAssignments()
                ->count();
        @endphp

        <div class="page-header">
            <div>
                <h1>Caregiver Dashboard</h1>

                <p>
                    Welcome back, {{ auth()->user()->name }}!
                </p>
            </div>
        </div>

        <div class="dashboard-grid">
            <section class="panel">
                <h2>Account Status</h2>

                <p>
                    <span class="badge badge-{{ auth()->user()->status }}">
                        {{ auth()->user()->status }}
                    </span>
                </p>

                <p class="muted">
                    Your caregiver login account is active.
                </p>
            </section>

            <section class="panel">
                <h2>Professional Profile</h2>

                <p>
                    <strong>Qualification:</strong>
                    {{ $caregiverProfile->qualification }}
                </p>

                <p>
                    <strong>Experience:</strong>
                    {{ $caregiverProfile->experience_years }} years
                </p>

                <p>
                    <strong>Specialization:</strong>
                    {{ $caregiverProfile->specialization ?: 'Not provided' }}
                </p>
            </section>

            <section class="panel">
                <h2>My Assignments</h2>

                <div class="stat-number">
                    {{ $assignmentCount }}
                </div>


                <p class="muted">
                    Total child-care bookings assigned to you.
                </p>

                <a class="button" href="{{ route('caregiver.assignments.index') }}">
                    View Assignments
                </a>

                <a
                    class="button button-secondary"
                    href="{{ route('caregiver.schedule.index') }}"
                >
                    View Schedule
                </a>
            </section>

            <section class="panel">
                <h2>Availability</h2>

                <p>
                    <span class="badge badge-{{ $caregiverProfile->availability_status }}">
                        {{ $caregiverProfile->availability_status }}
                    </span>
                </p>

                <p class="muted">
                    Admin can assign new bookings while you are available.
                </p>

                <a
                    class="button button-secondary"
                    href="{{ route('caregiver.profile.show') }}"
                >
                    View Profile
                </a>
            </section>
        </div>
    @else
    @php
        $parentProfile = auth()->user()->parentProfile;

        $childCount = $parentProfile
            ?->children()
            ->count() ?? 0;

        $bookingCount = $parentProfile
            ?->bookings()
            ->count() ?? 0;
    @endphp

    <div class="page-header">
        <div>
            <h1>Parent Dashboard</h1>

            <p>
                Welcome back, {{ auth()->user()->name }}!
            </p>
        </div>
    </div>

    <div class="dashboard-grid">
        <section class="panel">
            <h2>My Children</h2>

            <div class="stat-number">
                {{ $childCount }}
            </div>

            <p class="muted">
                Total child profiles connected to your account.
            </p>

            <div class="action-group">
                <a
                    class="button"
                    href="{{ route('children.index') }}"
                >
                    Manage Children
                </a>
            </div>
        </section>

        <section class="panel">
            <h2>My Bookings</h2>

            <div class="stat-number">
                {{ $bookingCount }}
            </div>

            <p class="muted">
                Total child care bookings submitted by you.
            </p>

            <div class="action-group">
                <a
                    class="button"
                    href="{{ route('bookings.index') }}"
                >
                    View Bookings
                </a>

                <a
                    class="button button-secondary"
                    href="{{ route('bookings.create') }}"
                >
                    New Booking
                </a>
            </div>
        </section>

        <section class="panel">
            <h2>Parent Account</h2>

            <p>
                <strong>Name:</strong>
                {{ auth()->user()->name }}
            </p>

            <p>
                <strong>Email:</strong>
                {{ auth()->user()->email }}
            </p>

            <p>
                <strong>Role:</strong>
                {{ ucfirst(auth()->user()->role) }}
            </p>

            <a class="button" href="{{ route('profile.show') }}">
                View Profile
            </a>
        </section>
    </div>
    @endif
@endsection
