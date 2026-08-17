@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>New Booking</h1>
            <p>Select your child and an available service time slot.</p>
        </div>

        <a
            class="button button-secondary"
            href="{{ route('parent.services.index') }}"
        >
            Back to Services
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
        @elseif ($timeSlots->isEmpty())
            <div class="empty-state">
                <h2>No available time slot found</h2>

                <p>
                    No service slot currently has available capacity.
                    Please choose another service or check again later.
                </p>

                <a class="button" href="{{ route('parent.services.index') }}">
                    View Services
                </a>
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
                        <label for="slot_id">
                            Available Time Slot
                            <span class="required">*</span>
                        </label>

                        <select
                            id="slot_id"
                            name="slot_id"
                            required
                        >
                            <option value="">Select service and time</option>

                            @foreach ($timeSlots as $timeSlot)
                                <option
                                    value="{{ $timeSlot->slot_id }}"
                                    @selected((int) old('slot_id', $selectedSlotId) === $timeSlot->slot_id)
                                >
                                    {{ $timeSlot->service->name }}
                                    — {{ $timeSlot->slot_date->format('d M Y') }}
                                    — {{ date('h:i A', strtotime($timeSlot->start_time)) }}
                                    to {{ date('h:i A', strtotime($timeSlot->end_time)) }}
                                    — {{ $timeSlot->remainingCapacity() }} place(s) left
                                    — ৳{{ number_format((float) $timeSlot->service->price, 2) }}
                                </option>
                            @endforeach
                        </select>
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

                <div class="alert alert-success">
                    New bookings are created as Pending. Admin will review the
                    booking before it becomes Confirmed.
                </div>

                <div class="form-actions">
                    <button class="button" type="submit">
                        Submit Booking
                    </button>

                    <a
                        class="button button-secondary"
                        href="{{ route('parent.services.index') }}"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        @endif
    </section>
@endsection
