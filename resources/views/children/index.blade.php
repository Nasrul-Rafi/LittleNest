@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>My Children</h1>
            <p>View and manage your child profiles.</p>
        </div>

        <a class="button" href="{{ route('children.create') }}">
            Add Child
        </a>
    </div>

    <section class="panel">
        @if ($children->isEmpty())
            <div class="empty-state">
                <h2>No child profiles found</h2>

                <p>
                    Add your first child profile to get started.
                </p>

                <a class="button" href="{{ route('children.create') }}">
                    Add First Child
                </a>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Date of Birth</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($children as $child)
                            <tr>
                                <td>
                                    <strong>{{ $child->full_name }}</strong>
                                </td>

                                <td>
                                    {{ $child->date_of_birth->format('d M Y') }}
                                </td>

                                <td>
                                    {{ $child->date_of_birth->age }} years
                                </td>

                                <td>
                                    {{ $child->gender
                                        ? ucfirst($child->gender)
                                        : 'Not provided' }}
                                </td>

                                <td>
                                    <span
                                        class="badge badge-{{ $child->status }}"
                                    >
                                        {{ $child->status }}
                                    </span>
                                </td>

                                <td>
                                    <div class="action-group">
                                        <a
                                            class="button button-small button-secondary"
                                            href="{{ route(
                                                'children.show',
                                                $child
                                            ) }}"
                                        >
                                            View
                                        </a>

                                        <a
                                            class="button button-small"
                                            href="{{ route(
                                                'children.edit',
                                                $child
                                            ) }}"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'children.destroy',
                                                $child
                                            ) }}"
                                            onsubmit="return confirm(
                                                'Are you sure you want to delete this child profile?'
                                            );"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                class="button button-small button-danger"
                                                type="submit"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection