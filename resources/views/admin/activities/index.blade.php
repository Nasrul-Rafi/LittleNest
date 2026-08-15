@extends('layouts.parent', ['title' => 'Activity Monitoring'])

@section('content')
<div class="page-header">
    <div>
        <h1>Activity Monitoring</h1>
        <p>Monitor caregiver updates across all assigned bookings.</p>
    </div>
    <span class="badge badge-confirmed">{{ $activities->count() }} results</span>
</div>

<div class="panel">
    <form method="GET" action="{{ route('admin.activities.index') }}">
        <div class="form-grid-three">
            <div class="form-group">
                <label for="child">Child</label>
                <input id="child" name="child" value="{{ $child }}" placeholder="Child name">
            </div>

            <div class="form-group">
                <label for="caregiver">Caregiver</label>
                <input id="caregiver" name="caregiver" value="{{ $caregiver }}" placeholder="Caregiver name">
            </div>

            <div class="form-group">
                <label for="booking">Booking</label>
                <input id="booking" name="booking" value="{{ $booking }}" placeholder="LN-2026-0001">
            </div>

            <div class="form-group">
                <label for="activity_type">Activity Type</label>
                <input id="activity_type" name="activity_type" value="{{ $activityType }}" placeholder="Learning, meal, nap">
            </div>

            <div class="form-group">
                <label for="from_date">From Date</label>
                <input type="date" id="from_date" name="from_date" value="{{ $fromDate }}">
            </div>

            <div class="form-group">
                <label for="to_date">To Date</label>
                <input type="date" id="to_date" name="to_date" value="{{ $toDate }}">
            </div>
        </div>

        <div class="form-actions">
            <button class="button" type="submit">Apply Filters</button>
            <a class="button button-secondary" href="{{ route('admin.activities.index') }}">Clear</a>
        </div>
    </form>
</div>

<div class="panel table-wrap">
    <table>
        <thead>
            <tr>
                <th>Child</th>
                <th>Caregiver</th>
                <th>Activity</th>
                <th>Booking</th>
                <th>Date & Time</th>
                <th>Photo</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $activity)
                <tr>
                    <td>{{ $activity->assignment->booking->child->full_name }}</td>
                    <td>{{ $activity->assignment->caregiver->name }}</td>
                    <td>{{ $activity->activity_type }}</td>
                    <td>{{ $activity->assignment->booking->display_reference }}</td>
                    <td>{{ $activity->activity_time->format('d M Y, h:i A') }}</td>
                    <td>{{ $activity->photo_path ? 'Yes' : 'No' }}</td>
                    <td>
                        <a class="button button-secondary button-small" href="{{ route('admin.activities.show', $activity) }}">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No activities found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
