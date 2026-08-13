@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Edit Activity Update</h1>

            <p>Change the selected child activity information.</p>
        </div>

        <a
            class="button button-secondary"
            href="{{ route('caregiver.assignments.show', $activity->assignment) }}"
        >
            Back to Assignment
        </a>
    </div>

    <section class="panel">
        <form
            method="POST"
            action="{{ route('caregiver.activities.update', $activity) }}"
            enctype="multipart/form-data"
        >
            @csrf

            @include('caregiver.activities._form')

            <div class="form-actions">
                <button class="button" type="submit">
                    Save Changes
                </button>

                <a
                    class="button button-secondary"
                    href="{{ route('caregiver.assignments.show', $activity->assignment) }}"
                >
                    Cancel
                </a>
            </div>
        </form>
    </section>
@endsection
