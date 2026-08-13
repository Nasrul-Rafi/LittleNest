@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>
                {{ $type === 'reschedule'
                    ? 'Request Reschedule'
                    : 'Request Cancellation' }}
            </h1>

            <p>
                Booking #{{ $booking->booking_id }} will remain unchanged
                until Admin approves this request.
            </p>
        </div>

        <a
            class="button button-secondary"
            href="{{ route('bookings.show', $booking) }}"
        >
            Back to Booking
        </a>
    </div>

    <section class="panel">
        <form
            method="POST"
            action="{{ route('booking-requests.store', $booking) }}"
        >
            @csrf

            <input name="request_type" type="hidden" value="{{ $type }}">

            <div class="form-grid">
                @if ($type === 'reschedule')
                    <div class="form-group">
                        <label for="requested_date">
                            New Date <span class="required">*</span>
                        </label>

                        <input
                            id="requested_date"
                            name="requested_date"
                            type="date"
                            min="{{ now()->format('Y-m-d') }}"
                            value="{{ old('requested_date') }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="requested_time">
                            New Time <span class="required">*</span>
                        </label>

                        <input
                            id="requested_time"
                            name="requested_time"
                            type="time"
                            value="{{ old('requested_time') }}"
                            required
                        >
                    </div>
                @endif

                <div class="form-group form-group-full">
                    <label for="reason">
                        Reason <span class="required">*</span>
                    </label>

                    <textarea
                        id="reason"
                        name="reason"
                        required
                    >{{ old('reason') }}</textarea>
                </div>
            </div>

            <div class="form-actions">
                <button class="button" type="submit">
                    Submit Request
                </button>
            </div>
        </form>
    </section>
@endsection
