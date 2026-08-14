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
                Booking {{ $booking->display_reference }} will remain unchanged
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
                    <div class="form-group form-group-full">
                        <label for="requested_slot_id">
                            New Available Time Slot
                            <span class="required">*</span>
                        </label>

                        @if ($timeSlots->isEmpty())
                            <div class="alert alert-error">
                                No alternative slot is currently available for
                                {{ $booking->service->name }}.
                            </div>
                        @else
                            <select
                                id="requested_slot_id"
                                name="requested_slot_id"
                                required
                            >
                                <option value="">Select a new slot</option>

                                @foreach ($timeSlots as $timeSlot)
                                    <option
                                        value="{{ $timeSlot->slot_id }}"
                                        @selected(
                                            old('requested_slot_id')
                                                == $timeSlot->slot_id
                                        )
                                    >
                                        {{ $timeSlot->slot_date->format('d M Y') }}
                                        — {{ date('h:i A', strtotime($timeSlot->start_time)) }}
                                        to {{ date('h:i A', strtotime($timeSlot->end_time)) }}
                                        — {{ $timeSlot->remainingCapacity() }} place(s) left
                                    </option>
                                @endforeach
                            </select>
                        @endif
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
                <button
                    class="button"
                    type="submit"
                    @disabled($type === 'reschedule' && $timeSlots->isEmpty())
                >
                    Submit Request
                </button>
            </div>
        </form>
    </section>
@endsection
