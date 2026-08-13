@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Add Service</h1>

            <p>Create a new child-care service.</p>
        </div>

        <a
            class="button button-secondary"
            href="{{ route('admin.services.index') }}"
        >
            Back to Services
        </a>
    </div>

    <section class="panel">
        <form method="POST" action="{{ route('admin.services.store') }}">
            @csrf

            @include('admin.services._form')

            <div class="form-actions">
                <button class="button" type="submit">
                    Create Service
                </button>

                <a
                    class="button button-secondary"
                    href="{{ route('admin.services.index') }}"
                >
                    Cancel
                </a>
            </div>
        </form>
    </section>
@endsection
