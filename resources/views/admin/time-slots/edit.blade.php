@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Edit Time Slot</h1>
            <p>Update capacity or availability for this service slot.</p>
        </div>

        <a
            class="button button-secondary"
            href="{{ route('admin.time-slots.index') }}"
        >
            Back to Time Slots
        </a>
    </div>

    <section class="panel">
        @if ($timeSlot->activeBookingsCount() > 0)
            <div class="alert alert-success">
                This slot already has active bookings. You can change capacity
                or status, but service, date and time must stay unchanged.
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('admin.time-slots.update', $timeSlot) }}"
        >
            @csrf

            @include('admin.time-slots._form')

            <div class="form-actions">
                <button class="button" type="submit">
                    Save Changes
                </button>

                <a
                    class="button button-secondary"
                    href="{{ route('admin.time-slots.index') }}"
                >
                    Cancel
                </a>
            </div>
        </form>
    </section>
@endsection
