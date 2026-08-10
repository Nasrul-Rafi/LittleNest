@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>New Booking</h1>
            <p>Select a child, service and appointment time.</p>
        </div>

        <a
            class="button button-secondary"
            href="{{ route('bookings.index') }}"
        >
            Back to Bookings
        </a>
    </div>

    <section class="panel">
        @if ($children->isEmpty())
            <div class="empty-state">
                <h2>No active child profile found</h2>

                <p>
                    Add an active child profile before making a booking.
                </p>

                <a class="button" href="{{ route('children.create') }}">
                    Add Child
                </a>
            </div>
        @elseif ($services->isEmpty())
            <div class="empty-state">
                <h2>No service is currently available</h2>

                <p>
                    Please try again after a service becomes available.
                </p>
            </div>
        @else
            <form
                method="POST"
                action="{{ route('bookings.store') }}"
            >
                @csrf

                <div class="form-grid">
                    <div class="form-group">
                        <label for="child_id">
                            Child <span class="required">*</span>
                        </label>

                        <select
                            id="child_id"
                            name="child_id"
                            required
                        >
                            <option value="">Select child</option>

                            @foreach ($children as $child)
                                <option
                                    value="{{ $child->child_id }}"
                                    @selected(
                                        old('child_id')
                                            == $child->child_id
                                    )
                                >
                                    {{ $child->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="service_id">
                            Service <span class="required">*</span>
                        </label>

                        <select
                            id="service_id"
                            name="service_id"
                            required
                        >
                            <option value="">Select service</option>

                            @foreach ($services as $service)
                                <option
                                    value="{{ $service->service_id }}"
                                    @selected(
                                        old('service_id')
                                            == $service->service_id
                                    )
                                >
                                    {{ $service->name }}
                                    — ৳{{ number_format(
                                        (float) $service->price,
                                        2
                                    ) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="booking_date">
                            Booking Date
                            <span class="required">*</span>
                        </label>

                        <input
                            id="booking_date"
                            type="date"
                            name="booking_date"
                            min="{{ now()->format('Y-m-d') }}"
                            value="{{ old(
                                'booking_date',
                                now()->addDay()->format('Y-m-d')
                            ) }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="booking_time">
                            Booking Time
                            <span class="required">*</span>
                        </label>

                        <input
                            id="booking_time"
                            type="time"
                            name="booking_time"
                            value="{{ old('booking_time') }}"
                            required
                        >
                    </div>

                    <div class="form-group form-group-full">
                        <label for="special_instructions">
                            Special Instructions
                        </label>

                        <textarea
                            id="special_instructions"
                            name="special_instructions"
                            maxlength="2000"
                            placeholder="Allergies, pickup instructions or other information..."
                        >{{ old('special_instructions') }}</textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="button" type="submit">
                        Submit Booking
                    </button>

                    <a
                        class="button button-secondary"
                        href="{{ route('bookings.index') }}"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        @endif
    </section>
@endsection