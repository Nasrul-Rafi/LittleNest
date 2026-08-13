@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Service Management</h1>

            <p>Create services and manage their price and availability.</p>
        </div>

        <a class="button" href="{{ route('admin.services.create') }}">
            Add Service
        </a>
    </div>

    <section class="panel">
        @if ($services->isEmpty())
            <div class="empty-state">
                <h2>No services found</h2>
                <p>Add the first child-care service to get started.</p>

                <a class="button" href="{{ route('admin.services.create') }}">
                    Add Service
                </a>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Price</th>
                            <th>Duration</th>
                            <th>Bookings</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($services as $service)
                            <tr>
                                <td><strong>{{ $service->name }}</strong></td>
                                <td>৳{{ number_format((float) $service->price, 2) }}</td>
                                <td>{{ $service->duration_minutes }} minutes</td>
                                <td>{{ $service->bookings_count }}</td>
                                <td>
                                    <span class="badge badge-{{ $service->status }}">
                                        {{ $service->status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <a
                                            class="button button-small button-secondary"
                                            href="{{ route('admin.services.show', $service) }}"
                                        >
                                            View
                                        </a>

                                        <a
                                            class="button button-small"
                                            href="{{ route('admin.services.edit', $service) }}"
                                        >
                                            Edit
                                        </a>
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
