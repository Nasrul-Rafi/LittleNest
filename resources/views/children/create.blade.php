@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Add Child</h1>
            <p>Create a new child profile.</p>
        </div>

        <a
            class="button button-secondary"
            href="{{ route('children.index') }}"
        >
            Back to List
        </a>
    </div>

    <section class="panel">
        <form method="POST" action="{{ route('children.store') }}">
            @csrf

            @include('children._form', [
                'child' => null,
            ])

            <div class="form-actions">
                <button class="button" type="submit">
                    Save Child
                </button>

                <a
                    class="button button-secondary"
                    href="{{ route('children.index') }}"
                >
                    Cancel
                </a>
            </div>
        </form>
    </section>
@endsection
