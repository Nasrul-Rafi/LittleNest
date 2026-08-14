@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>My Schedule</h1>
            <p>View your upcoming confirmed child-care assignments.</p>
        </div>

        <span class="badge badge-confirmed">
            {{ $assignments->count() }} upcoming
        </span>
    </div>

    @if ($scheduleByDate->isEmpty())
        <section class="panel">
            <div class="empty-state">
                <h2>No Upcoming Schedule</h2>
                <p>
                    Your confirmed upcoming assignments will appear here.
                </p>
            </div>
        </section>
    @else
        @foreach ($scheduleByDate as $date => $dateAssignments)
            <section class="panel">
                <div class="page-header">
                    <div>
                        <h2>
                            {{ \Carbon\Carbon::parse($date)
                                ->format('l, d M Y') }}
                        </h2>

                        <p>
                            {{ $dateAssignments->count() }}
                            {{ $dateAssignments->count() === 1
                                ? 'assignment'
                                : 'assignments' }}
                        </p>
                    </div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Child</th>
                                <th>Service</th>
                                <th>Duration</th>
                                <th>Booking</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($dateAssignments as $assignment)
                                <tr>
                                    <td>
                                        <strong>
                                            {{ date(
                                                'h:i A',
                                                strtotime(
                                                    $assignment->booking
                                                        ->booking_time
                                                )
                                            ) }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{ $assignment->booking
                                            ->child->full_name }}
                                    </td>

                                    <td>
                                        {{ $assignment->booking
                                            ->service->name }}
                                    </td>

                                    <td>
                                        {{ $assignment->booking
                                            ->service->duration_minutes }}
                                        minutes
                                    </td>

                                    <td>
                                        {{ $assignment->booking->display_reference }}
                                        <br>
                                        <span class="badge badge-confirmed">
                                            confirmed
                                        </span>
                                    </td>

                                    <td>
                                        <a
                                            class="button button-small"
                                            href="{{ route(
                                                'caregiver.assignments.show',
                                                $assignment
                                            ) }}"
                                        >
                                            View Assignment
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endforeach
    @endif
@endsection
