@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>My Assignments</h1>

            <p>View the children and bookings assigned to you.</p>
        </div>

        <span class="badge badge-confirmed">
            {{ $assignments->count() }} total
        </span>
    </div>

    <section class="panel">
        @if ($assignments->isEmpty())
            <div class="empty-state">
                <h2>No assignments found</h2>

                <p>New caregiver assignments will appear here.</p>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Child</th>
                            <th>Service</th>
                            <th>Schedule</th>
                            <th>Booking Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($assignments as $assignment)
                            <tr>
                                <td>{{ $assignment->booking->display_reference }}</td>

                                <td>
                                    <strong>
                                        {{ $assignment->booking->child->full_name }}
                                    </strong>
                                </td>

                                <td>{{ $assignment->booking->service->name }}</td>

                                <td>
                                    {{ $assignment->booking->booking_date->format('d M Y') }}
                                    <br>
                                    <span class="muted">
                                        {{ date('h:i A', strtotime($assignment->booking->booking_time)) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge badge-{{ $assignment->booking->status }}">
                                        {{ $assignment->booking->status }}
                                    </span>
                                </td>

                                <td>
                                    <a
                                        class="button button-small"
                                        href="{{ route('caregiver.assignments.show', $assignment) }}"
                                    >
                                        View Assignment
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
