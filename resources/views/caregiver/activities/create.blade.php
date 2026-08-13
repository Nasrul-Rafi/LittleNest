@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Add Activity Update</h1>

            <p>
                Add an update for {{ $assignment->booking->child->full_name }}.
            </p>
        </div>

        <a
            class="button button-secondary"
            href="{{ route('caregiver.assignments.show', $assignment) }}"
        >
            Back to Assignment
        </a>
    </div>

    <section class="panel">
        <form
            method="POST"
            action="{{ route('caregiver.activities.store', $assignment) }}"
            enctype="multipart/form-data"
        >
            @csrf

            @include('caregiver.activities._form')

            <div class="form-actions">
                <button class="button" type="submit">
                    Add Activity
                </button>

                <a
                    class="button button-secondary"
                    href="{{ route('caregiver.assignments.show', $assignment) }}"
                >
                    Cancel
                </a>
            </div>
        </form>
    </section>
@endsection
