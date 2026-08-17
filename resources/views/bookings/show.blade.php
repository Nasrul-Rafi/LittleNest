@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Booking {{ $booking->display_reference }}</h1>

            <p>View booking information and current status.</p>
        </div>

        <a
            class="button button-secondary"
            href="{{ route('bookings.index') }}"
        >
            Back to Bookings
        </a>
    </div>

    <section class="panel">
        <div class="detail-grid">
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
                    {{ date(
                        'h:i A',
                        strtotime($booking->booking_time)
                    ) }}

                    @if ($booking->timeSlot)
                        – {{ date(
                            'h:i A',
                            strtotime($booking->timeSlot->end_time)
                        ) }}
                    @endif
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Total Amount</span>

                <p class="detail-value">
                    ৳{{ number_format(
                        (float) $booking->total_amount,
                        2
                    ) }}
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
                    {{ $booking->special_instructions
                        ?: 'No special instructions provided.' }}
                </p>
            </div>
        </div>

        @if ($booking->status === 'pending')
            <div class="form-actions">
                <form
                    method="POST"
                    action="{{ route(
                        'bookings.cancel',
                        $booking
                    ) }}"
                    onsubmit="return confirm(
                        'Are you sure you want to cancel this booking?'
                    );"
                >
                    @csrf
                    @method('PATCH')

                    <button
                        class="button button-danger"
                        type="submit"
                    >
                        Cancel Booking
                    </button>
                </form>
            </div>
        @endif
    </section>

    @if ($booking->status === 'confirmed' || $bookingRequests->isNotEmpty())
        <section class="panel">
            <div class="page-header">
                <div>
                    <h2>Booking Change Requests</h2>
                    <p>
                        Request a new schedule or cancellation from Admin.
                    </p>
                </div>

                @if ($booking->status === 'confirmed' && !$pendingRequest)
                    <div class="action-group">
                        <a
                            class="button button-secondary"
                            href="{{ route(
                                'booking-requests.create',
                                [$booking, 'reschedule']
                            ) }}"
                        >
                            Request Reschedule
                        </a>

                        <a
                            class="button button-danger"
                            href="{{ route(
                                'booking-requests.create',
                                [$booking, 'cancellation']
                            ) }}"
                        >
                            Request Cancellation
                        </a>
                    </div>
                @endif
            </div>

            @if ($pendingRequest)
                <div class="alert alert-error">
                    Request #{{ $pendingRequest->request_id }} is waiting
                    for Admin review. You cannot submit another request yet.
                </div>
            @endif

            @if ($bookingRequests->isEmpty())
                <p class="muted">
                    No change request has been submitted for this booking.
                </p>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Request</th>
                                <th>Type</th>
                                <th>Requested Schedule</th>
                                <th>Status</th>
                                <th>Admin Note</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($bookingRequests as $bookingRequest)
                                <tr>
                                    <td>#{{ $bookingRequest->request_id }}</td>
                                    <td>
                                        {{ ucfirst($bookingRequest->request_type) }}
                                    </td>
                                    <td>
                                        @if ($bookingRequest->request_type === 'reschedule')
                                            {{ $bookingRequest->requested_date
                                                ->format('d M Y') }}
                                            at
                                            {{ date(
                                                'h:i A',
                                                strtotime($bookingRequest->requested_time)
                                            ) }}
                                        @else
                                            Not applicable
                                        @endif
                                    </td>
                                    <td>
                                        <span
                                            class="badge badge-{{ $bookingRequest->request_status }}"
                                        >
                                            {{ $bookingRequest->request_status }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $bookingRequest->admin_note
                                            ?: 'No note yet' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endif

    <section class="panel">
        <div class="page-header">
            <div>
                <h2>Payment Information</h2>
                <p>View payment status and previous payment attempts.</p>
            </div>

            @if (
                $booking->status === 'confirmed'
                && (!$latestPayment || $latestPayment->payment_status === 'failed')
            )
                <a
                    class="button"
                    href="{{ route('payments.create', $booking) }}"
                >
                    {{ $latestPayment ? 'Retry Payment' : 'Make Payment' }}
                </a>
            @endif
        </div>

        @if ($booking->status === 'pending')
            <p class="muted">
                Payment will be available after Admin confirms the booking.
            </p>
        @endif

        @if ($payments->isEmpty())
            <p class="muted">No payment has been submitted yet.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Payment</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Transaction ID</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($payments as $payment)
                            <tr>
                                <td>#{{ $payment->payment_id }}</td>
                                <td>৳{{ number_format((float) $payment->amount, 2) }}</td>
                                <td>
                                    {{ $payment->display_method }}
                                </td>
                                <td>{{ $payment->transaction_id ?: 'Not required' }}</td>
                                <td>
                                    <span class="badge badge-{{ $payment->display_status }}">
                                        {{ $payment->display_status }}
                                    </span>
                                </td>
                                <td>{{ $payment->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    <a
                                        class="button button-small button-secondary"
                                        href="{{ route('payments.show', $payment) }}"
                                    >
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="panel">
        <h2>Assigned Caregiver</h2>

        @if ($booking->caregiverAssignment)
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Caregiver Name</span>
                    <p class="detail-value">
                        {{ $booking->caregiverAssignment->caregiver->name }}
                    </p>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Caregiver Email</span>
                    <p class="detail-value">
                        {{ $booking->caregiverAssignment->caregiver->email }}
                    </p>
                </div>
            </div>

            <div class="form-actions">
                <a class="button button-secondary" href="{{ route('caregivers.show', $booking->caregiverAssignment) }}">
                    View Caregiver Profile
                </a>
            </div>
        @else
            <p class="muted">
                A caregiver has not been assigned to this booking yet.
            </p>
        @endif
    </section>

    @if ($booking->caregiverAssignment)
        <section class="panel">
            <h2>Child Activity Timeline</h2>

            @if ($booking->caregiverAssignment->activities->isEmpty())
                <p class="muted">
                    The caregiver has not posted an activity update yet.
                </p>
            @else
                <div class="activity-list">
                    @foreach ($booking->caregiverAssignment->activities->sortByDesc('activity_time') as $activity)
                        <article class="activity-item">
                            <div>
                                <strong>
                                    {{ ucwords(str_replace('-', ' ', $activity->activity_type)) }}
                                </strong>

                                <span class="muted">
                                    — {{ $activity->activity_time->format('d M Y, h:i A') }}
                                </span>
                            </div>

                            <p>{{ $activity->details ?: 'No additional details.' }}</p>

                            @if ($activity->photo_path)
                                <img
                                    class="activity-photo"
                                    src="{{ asset('storage/' . $activity->photo_path) }}"
                                    alt="Activity photo"
                                >
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    @endif
@endsection
