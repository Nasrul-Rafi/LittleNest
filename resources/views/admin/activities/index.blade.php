@extends('layouts.parent', ['title' => 'Activity Monitoring'])
@section('content')
<div class="page-header"><div><h1>Activity Monitoring</h1><p>Monitor caregiver updates across all assigned bookings.</p></div></div>
<div class="panel"><form method="GET" class="form-grid"><div class="form-group"><label>Activity Type</label><input name="activity_type" value="{{ request('activity_type') }}" placeholder="e.g. Learning"></div><div class="form-group"><label>Date</label><input type="date" name="activity_date" value="{{ request('activity_date') }}"></div><div class="form-group form-group-full"><div class="action-group"><button class="button">Filter</button><a class="button button-secondary" href="{{ route('admin.activities.index') }}">Clear</a></div></div></form></div>
<div class="panel table-wrap"><table><thead><tr><th>Child</th><th>Caregiver</th><th>Activity</th><th>Booking</th><th>Date & Time</th><th>Photo</th><th>Action</th></tr></thead><tbody>
@forelse($activities as $activity)<tr><td>{{ $activity->assignment->booking->child->full_name }}</td><td>{{ $activity->assignment->caregiver->name }}</td><td>{{ $activity->activity_type }}</td><td>{{ $activity->assignment->booking->display_reference }}</td><td>{{ $activity->activity_time->format('d M, h:i A') }}</td><td>{{ $activity->photo_path ? 'Yes' : 'No' }}</td><td><a class="button button-secondary button-small" href="{{ route('admin.activities.show', $activity) }}">View</a></td></tr>@empty<tr><td colspan="7">No activities found.</td></tr>@endforelse
</tbody></table></div>
@endsection
