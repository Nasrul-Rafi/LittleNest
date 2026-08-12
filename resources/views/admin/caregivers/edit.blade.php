@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Edit Caregiver</h1>

            <p>Update {{ $caregiver->name }}'s account and profile.</p>
        </div>

        <a class="button button-secondary" href="{{ route('admin.caregivers.show', $caregiver) }}">
            Back to Details
        </a>
    </div>

    <section class="panel">
        <form
            method="POST"
            action="{{ route('admin.caregivers.update', $caregiver) }}"
        >
            @csrf

            @include('admin.caregivers._form')

            <div class="form-actions">
                <button class="button" type="submit">
                    Save Changes
                </button>

                <a
                    class="button button-secondary"
                    href="{{ route('admin.caregivers.show', $caregiver) }}"
                >
                    Cancel
                </a>
            </div>
        </form>
    </section>
@endsection
