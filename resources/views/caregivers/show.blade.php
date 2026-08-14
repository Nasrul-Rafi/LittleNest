@extends('layouts.parent', ['title' => 'Assigned Caregiver'])
@section('content')
<div class="page-header"><div><h1>Assigned Caregiver Profile</h1><p>Professional information for the caregiver assigned to your booking.</p></div><a class="button button-secondary" href="{{ route('bookings.show', $assignment->booking) }}">Back to Booking</a></div>
<div class="panel"><div class="detail-grid">
    <div class="detail-item"><span class="detail-label">Name</span><p class="detail-value">{{ $assignment->caregiver->name }}</p></div>
    <div class="detail-item"><span class="detail-label">Booking</span><p class="detail-value">{{ $assignment->booking->display_reference }}</p></div>
    <div class="detail-item"><span class="detail-label">Qualification</span><p class="detail-value">{{ $assignment->caregiver->caregiverProfile?->qualification ?: 'Not provided' }}</p></div>
    <div class="detail-item"><span class="detail-label">Experience</span><p class="detail-value">{{ $assignment->caregiver->caregiverProfile?->experience_years ?? 0 }} years</p></div>
    <div class="detail-item"><span class="detail-label">Specialization</span><p class="detail-value">{{ $assignment->caregiver->caregiverProfile?->specialization ?: 'Not provided' }}</p></div>
    <div class="detail-item"><span class="detail-label">Availability</span><p class="detail-value">{{ ucfirst($assignment->caregiver->caregiverProfile?->availability_status ?? 'unknown') }}</p></div>
    <div class="detail-item detail-item-full"><span class="detail-label">Skills</span><p class="detail-value">{{ $assignment->caregiver->caregiverProfile?->skills ?: 'Not provided' }}</p></div>
    <div class="detail-item detail-item-full"><span class="detail-label">Biography</span><p class="detail-value">{{ $assignment->caregiver->caregiverProfile?->bio ?: 'Not provided' }}</p></div>
</div></div>
@endsection
