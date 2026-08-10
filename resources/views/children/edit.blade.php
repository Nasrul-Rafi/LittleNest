@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Edit Child</h1>

            <p>
                Update {{ $child->full_name }}'s information.
            </p>
        </div>

        <a
            class="button button-secondary"
            href="{{ route('children.show', $child) }}"
        >
            Back to Details
        </a>
    </div>

    <section class="panel">
        <form
            method="POST"
            action="{{ route('children.update', $child) }}"
        >
            @csrf
            @method('PUT')

            @include('children._form', [
                'child' => $child,
            ])

            <div class="form-actions">
                <button class="button" type="submit">
                    Update Child
                </button>

                <a
                    class="button button-secondary"
                    href="{{ route('children.show', $child) }}"
                >
                    Cancel
                </a>
            </div>
        </form>
    </section>
@endsection