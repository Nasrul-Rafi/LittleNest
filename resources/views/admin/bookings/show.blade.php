@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Booking #{{ $booking->booking_id }}</h1>

            <p>
                Review the parent, child and service information.
            </p>
        </div>

        <a class="button button-secondary" href="{{ route('admin.bookings.index') }}">
            Back to All Bookings
        </a>
    </div>

    <section class="panel">
        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Parent</span>

                <p class="detail-value">
                    {{ $booking->child->parentProfile->user->name }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Parent Email</span>

                <p class="detail-value">
                    {{ $booking->child->parentProfile->user->email }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Child</span>

                <p class="detail-value">
                    {{ $booking->child->full_name }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Service</span>

                <p class="detail-value">
                    {{ $booking->service->name }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Booking Date</span>

                <p class="detail-value">
                    {{ $booking->booking_date->format('d M Y') }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Booking Time</span>

                <p class="detail-value">
                    {{ date('h:i A', strtotime($booking->booking_time)) }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Total Amount</span>

                <p class="detail-value">
                    ৳{{ number_format((float) $booking->total_amount, 2) }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Status</span>

                <p class="detail-value">
                    <span
                        class="badge badge-{{ $booking->status }}"
                    >
                        {{ $booking->status }}
                    </span>
                </p>
            </div>

            <div class="detail-item detail-item-full">
                <span class="detail-label">
                    Special Instructions
                </span>

                <p class="detail-value">
                    {{ $booking->special_instructions ?: 'No special instructions provided.' }}
                </p>
            </div>
        </div>

        @if ($booking->status === 'pending')
            <div class="form-actions">
                <form method="POST"
                    action="{{ route('admin.bookings.confirm', $booking) }}"
                    onsubmit="return confirm('Confirm this booking?');">
                    @csrf

                    <button class="button" type="submit">
                        Confirm Booking
                    </button>
                </form>
            </div>
        @endif
    </section>
@endsection
