@extends('layouts.parent', ['title' => 'Assignment Details'])

@section('content')
<div class="page-header">
    <div>
        <h1>Assignment Details</h1>
        <p>{{ $assignment->booking->display_reference }} · {{ $assignment->booking->child->full_name }}</p>
    </div>
    <div class="action-group">
        <a class="button" href="{{ route('admin.bookings.show', $assignment->booking) }}">Open Booking</a>
        <a class="button button-secondary" href="{{ route('admin.assignments.index') }}">Back</a>
    </div>
</div>

<div class="panel">
    <div class="detail-grid">
        <div class="detail-item"><span class="detail-label">Caregiver</span><p class="detail-value">{{ $assignment->caregiver->name }}</p></div>
        <div class="detail-item"><span class="detail-label">Qualification</span><p class="detail-value">{{ $assignment->caregiver->caregiverProfile?->qualification ?: 'Not provided' }}</p></div>
        <div class="detail-item"><span class="detail-label">Parent</span><p class="detail-value">{{ $assignment->booking->child->parentProfile->user->name }}</p></div>
        <div class="detail-item"><span class="detail-label">Child</span><p class="detail-value">{{ $assignment->booking->child->full_name }}</p></div>
        <div class="detail-item"><span class="detail-label">Service</span><p class="detail-value">{{ $assignment->booking->service->name }}</p></div>
        <div class="detail-item"><span class="detail-label">Schedule</span><p class="detail-value">{{ $assignment->booking->booking_date->format('d M Y') }} · {{ $assignment->booking->booking_time }}</p></div>
        <div class="detail-item"><span class="detail-label">Assigned By</span><p class="detail-value">{{ $assignment->assignedBy->name }}</p></div>
        <div class="detail-item"><span class="detail-label">Status</span><p class="detail-value"><span class="badge badge-{{ $assignment->status }}">{{ $assignment->status }}</span></p></div>
    </div>
</div>

<div class="panel">
    <h2>Activity Updates</h2>
    @forelse($assignment->activities->sortByDesc('activity_time') as $activity)
        <div class="activity-item">
            <strong>{{ ucfirst($activity->activity_type) }}</strong>
            <span class="muted"> · {{ $activity->activity_time->format('d M Y, h:i A') }}</span>
            <p>{{ $activity->details }}</p>
        </div>
    @empty
        <p class="muted">No activity updates have been recorded yet.</p>
    @endforelse
</div>
@endsection
