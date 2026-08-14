@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Booking {{ $booking->display_reference }}</h1>

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
                    @if ($booking->timeSlot)
                        – {{ date('h:i A', strtotime($booking->timeSlot->end_time)) }}
                    @endif
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

                <form method="POST"
                    action="{{ route('admin.bookings.reject', $booking) }}"
                    onsubmit="return confirm('Reject this booking?');">
                    @csrf

                    <button class="button button-danger" type="submit">
                        Reject Booking
                    </button>
                </form>
            </div>
        @endif
    </section>

    <section class="panel">
        <h2>Caregiver Assignment</h2>

        @if ($booking->caregiverAssignment)
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Assigned Caregiver</span>

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

                <div class="detail-item">
                    <span class="detail-label">Assigned At</span>

                    <p class="detail-value">
                        {{ $booking->caregiverAssignment->assigned_at->format('d M Y, h:i A') }}
                    </p>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Assignment Status</span>

                    <p class="detail-value">
                        <span class="badge badge-active">
                            {{ $booking->caregiverAssignment->status }}
                        </span>
                    </p>
                </div>
            </div>
        @else
            <p class="muted">No caregiver has been assigned yet.</p>
        @endif

        @if ($booking->status === 'confirmed')
            @if ($caregivers->isEmpty())
                <p class="alert alert-error">
                    No active and available caregiver is currently available.
                </p>
            @else
                <form
                    method="POST"
                    action="{{ route('admin.bookings.assign-caregiver', $booking) }}"
                >
                    @csrf

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="caregiver_id">
                                {{ $booking->caregiverAssignment
                                    ? 'Reassign Caregiver'
                                    : 'Select Caregiver' }}
                                <span class="required">*</span>
                            </label>

                            <select id="caregiver_id" name="caregiver_id" required>
                                <option value="">Choose a caregiver</option>

                                @foreach ($caregivers as $caregiver)
                                    <option
                                        value="{{ $caregiver->id }}"
                                        @selected(old('caregiver_id') == $caregiver->id)
                                    >
                                        {{ $caregiver->name }}
                                        — {{ $caregiver->caregiverProfile->specialization ?: 'General Care' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button class="button" type="submit">
                            {{ $booking->caregiverAssignment
                                ? 'Change Caregiver'
                                : 'Assign Caregiver' }}
                        </button>
                    </div>
                </form>
            @endif
        @elseif ($booking->status === 'pending')
            <p class="muted">
                Confirm this booking before assigning a caregiver.
            </p>
        @endif
    </section>

    @if ($booking->caregiverAssignment)
        <section class="panel">
            <h2>Activity Updates</h2>

            @if ($booking->caregiverAssignment->activities->isEmpty())
                <p class="muted">No activity update has been added yet.</p>
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
