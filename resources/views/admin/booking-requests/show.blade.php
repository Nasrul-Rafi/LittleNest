@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Booking Request #{{ $bookingRequest->request_id }}</h1>
            <p>Review the Parent's requested booking change.</p>
        </div>

        <a
            class="button button-secondary"
            href="{{ route('admin.booking-requests.index') }}"
        >
            Back to Requests
        </a>
    </div>

    <section class="panel">
        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Booking</span>
                <p class="detail-value">
                    <a href="{{ route(
                        'admin.bookings.show',
                        $bookingRequest->booking
                    ) }}">
                        {{ $bookingRequest->booking->display_reference }}
                    </a>
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Request Status</span>
                <p class="detail-value">
                    <span
                        class="badge badge-{{ $bookingRequest->request_status }}"
                    >
                        {{ $bookingRequest->request_status }}
                    </span>
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Parent</span>
                <p class="detail-value">
                    {{ $bookingRequest->booking
                        ->child->parentProfile->user->name }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Child</span>
                <p class="detail-value">
                    {{ $bookingRequest->booking->child->full_name }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Request Type</span>
                <p class="detail-value">
                    {{ ucfirst($bookingRequest->request_type) }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Current Booking Schedule</span>
                <p class="detail-value">
                    {{ $bookingRequest->booking->booking_date
                        ->format('d M Y') }}
                    at
                    {{ date(
                        'h:i A',
                        strtotime($bookingRequest->booking->booking_time)
                    ) }}
                </p>
            </div>

            @if ($bookingRequest->request_type === 'reschedule')
                <div class="detail-item detail-item-full">
                    <span class="detail-label">Requested New Schedule</span>
                    <p class="detail-value">
                        {{ $bookingRequest->requested_date->format('d M Y') }}
                        at
                        {{ date(
                            'h:i A',
                            strtotime($bookingRequest->requested_time)
                        ) }}

                        @if ($bookingRequest->requestedSlot)
                            – {{ date(
                                'h:i A',
                                strtotime($bookingRequest->requestedSlot->end_time)
                            ) }}
                        @endif
                    </p>
                </div>
            @endif

            <div class="detail-item detail-item-full">
                <span class="detail-label">Parent's Reason</span>
                <p class="detail-value">{{ $bookingRequest->reason }}</p>
            </div>

            @if ($bookingRequest->request_status !== 'pending')
                <div class="detail-item detail-item-full">
                    <span class="detail-label">Review Information</span>
                    <p class="detail-value">
                        Reviewed by {{ $bookingRequest->reviewer->name }}
                        on {{ $bookingRequest->reviewed_at
                            ->format('d M Y, h:i A') }}.
                    </p>

                    <p>
                        {{ $bookingRequest->admin_note
                            ?: 'No Admin note provided.' }}
                    </p>
                </div>
            @endif
        </div>
    </section>

    @if ($bookingRequest->request_status === 'pending')
        <section class="panel">
            <h2>Admin Decision</h2>

            <form
                method="POST"
                action="{{ route(
                    'admin.booking-requests.approve',
                    $bookingRequest
                ) }}"
            >
                @csrf

                <div class="form-group">
                    <label for="approve_admin_note">
                        Approval Note (optional)
                    </label>
                    <textarea
                        id="approve_admin_note"
                        name="admin_note"
                    ></textarea>
                </div>

                <div class="form-actions">
                    <button class="button" type="submit">
                        Approve Request
                    </button>
                </div>
            </form>

            <hr style="margin: 28px 0; border: 0; border-top: 1px solid var(--border);">

            <form
                method="POST"
                action="{{ route(
                    'admin.booking-requests.reject',
                    $bookingRequest
                ) }}"
            >
                @csrf

                <div class="form-group">
                    <label for="reject_admin_note">
                        Rejection Reason <span class="required">*</span>
                    </label>
                    <textarea
                        id="reject_admin_note"
                        name="admin_note"
                        required
                    ></textarea>
                </div>

                <div class="form-actions">
                    <button class="button button-danger" type="submit">
                        Reject Request
                    </button>
                </div>
            </form>
        </section>
    @endif
@endsection
