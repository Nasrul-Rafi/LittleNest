@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Activity History</h1>
            <p>View and filter all child activities recorded by you.</p>
        </div>

        <span class="badge badge-confirmed">
            {{ $activities->count() }} found
        </span>
    </div>

    <section class="panel">
        <h2>Filter Activities</h2>

        <form method="GET" action="{{ route('caregiver.activities.index') }}">
            <div class="form-grid">
                <div class="form-group">
                    <label for="activity_type">Activity Type</label>

                    <select id="activity_type" name="activity_type">
                        <option value="">All activity types</option>

                        @foreach ($activityTypes as $activityType)
                            <option
                                value="{{ $activityType }}"
                                @selected(request('activity_type') === $activityType)
                            >
                                {{ ucwords(str_replace('-', ' ', $activityType)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="activity_date">Activity Date</label>

                    <input
                        id="activity_date"
                        name="activity_date"
                        type="date"
                        value="{{ request('activity_date') }}"
                    >
                </div>
            </div>

            <div class="form-actions">
                <button class="button" type="submit">
                    Apply Filter
                </button>

                <a
                    class="button button-secondary"
                    href="{{ route('caregiver.activities.index') }}"
                >
                    Clear Filter
                </a>
            </div>
        </form>
    </section>

    <section class="panel">
        @if ($activities->isEmpty())
            <div class="empty-state">
                <h2>No Activities Found</h2>
                <p>No activity matches the selected filters.</p>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Date and Time</th>
                            <th>Child</th>
                            <th>Booking</th>
                            <th>Activity</th>
                            <th>Details</th>
                            <th>Photo</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($activities as $activity)
                            <tr>
                                <td>
                                    {{ $activity->activity_time
                                        ->format('d M Y, h:i A') }}
                                </td>

                                <td>
                                    <strong>
                                        {{ $activity->assignment->booking
                                            ->child->full_name }}
                                    </strong>
                                </td>

                                <td>
                                    #{{ $activity->assignment->booking_id }}
                                    <br>
                                    <span class="muted">
                                        {{ $activity->assignment->booking
                                            ->service->name }}
                                    </span>
                                </td>

                                <td>
                                    {{ ucwords(str_replace(
                                        '-',
                                        ' ',
                                        $activity->activity_type
                                    )) }}
                                </td>

                                <td>
                                    {{ $activity->details
                                        ?: 'No additional details.' }}
                                </td>

                                <td>
                                    {{ $activity->photo_path
                                        ? 'Available'
                                        : 'No photo' }}
                                </td>

                                <td>
                                    <div class="action-group">
                                        <a
                                            class="button button-small"
                                            href="{{ route(
                                                'caregiver.assignments.show',
                                                $activity->assignment
                                            ) }}"
                                        >
                                            Assignment
                                        </a>

                                        <a
                                            class="button button-small button-secondary"
                                            href="{{ route(
                                                'caregiver.activities.edit',
                                                $activity
                                            ) }}"
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
