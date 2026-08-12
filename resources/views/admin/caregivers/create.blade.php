@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Add Caregiver</h1>

            <p>Create a login account and caregiver profile.</p>
        </div>

        <a class="button button-secondary" href="{{ route('admin.caregivers.index') }}">
            Back to Caregivers
        </a>
    </div>

    <section class="panel">
        <form method="POST" action="{{ route('admin.caregivers.store') }}">
            @csrf

            @include('admin.caregivers._form')

            <div class="form-actions">
                <button class="button" type="submit">
                    Create Caregiver
                </button>

                <a class="button button-secondary" href="{{ route('admin.caregivers.index') }}">
                    Cancel
                </a>
            </div>
        </form>
    </section>
@endsection
