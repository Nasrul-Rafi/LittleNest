@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Add Time Slot</h1>
            <p>Create an available date, time and capacity for a service.</p>
        </div>

        <a
            class="button button-secondary"
            href="{{ route('admin.time-slots.index') }}"
        >
            Back to Time Slots
        </a>
    </div>

    <section class="panel">
        @if ($services->isEmpty())
            <div class="empty-state">
                <h2>No active service found</h2>
                <p>Create or activate a service before adding time slots.</p>

                <a class="button" href="{{ route('admin.services.index') }}">
                    Manage Services
                </a>
            </div>
        @else
            <form method="POST" action="{{ route('admin.time-slots.store') }}">
                @csrf

                @include('admin.time-slots._form')

                <div class="form-actions">
                    <button class="button" type="submit">
                        Save Time Slot
                    </button>

                    <a
                        class="button button-secondary"
                        href="{{ route('admin.time-slots.index') }}"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        @endif
    </section>
@endsection
