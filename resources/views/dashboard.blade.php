@extends('layouts.parent')

@section('content')
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
        </section>
    </div>
@endsection