@extends('layouts.parent', ['title' => 'Child Activities'])
@section('content')
<div class="page-header"><div><h1>Child Activity Timeline</h1><p>Follow timestamped updates posted by your child’s assigned caregiver.</p></div></div>
<div class="panel">
    <form method="GET" class="form-grid">
        <div class="form-group"><label for="child_id">Child</label><select name="child_id" id="child_id"><option value="">All children</option>@foreach($children as $child)<option value="{{ $child->child_id }}" @selected((string)request('child_id') === (string)$child->child_id)>{{ $child->full_name }}</option>@endforeach</select></div>
        <div class="form-group"><label for="activity_date">Date</label><input type="date" name="activity_date" id="activity_date" value="{{ request('activity_date') }}"></div>
        <div class="form-group form-group-full"><div class="action-group"><button class="button" type="submit">Filter</button><a class="button button-secondary" href="{{ route('activities.index') }}">Clear</a></div></div>
    </form>
</div>
<div class="activity-list">
@forelse($activities as $activity)
    <div class="activity-item">
        <strong>{{ $activity->activity_time->format('h:i A') }} · {{ $activity->activity_type }}</strong>
        <p>{{ $activity->assignment->booking->child->full_name }} · {{ $activity->assignment->booking->display_reference }}</p>
        <p class="muted">{{ $activity->details ?: 'No additional notes.' }}</p>
        <p class="muted">Caregiver: {{ $activity->assignment->caregiver->name }}</p>
        <a class="button button-secondary button-small" href="{{ route('activities.show', $activity) }}">View Details</a>
    </div>
@empty
    <div class="panel empty-state"><h2>No activities found</h2><p>Caregiver updates for your children will appear here.</p></div>
@endforelse
</div>
@endsection
