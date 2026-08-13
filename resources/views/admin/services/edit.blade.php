@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Edit Service</h1>

            <p>Update {{ $service->name }}.</p>
        </div>

        <a
            class="button button-secondary"
            href="{{ route('admin.services.show', $service) }}"
        >
            Back to Details
        </a>
    </div>

    <section class="panel">
        <form
            method="POST"
            action="{{ route('admin.services.update', $service) }}"
        >
            @csrf

            @include('admin.services._form')

            <div class="form-actions">
                <button class="button" type="submit">
                    Save Changes
                </button>

                <a
                    class="button button-secondary"
                    href="{{ route('admin.services.show', $service) }}"
                >
                    Cancel
                </a>
            </div>
        </form>
    </section>
@endsection
