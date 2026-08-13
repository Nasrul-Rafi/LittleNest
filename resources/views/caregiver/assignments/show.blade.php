@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Assignment #{{ $assignment->assignment_id }}</h1>

            <p>View the assigned child, booking and activity history.</p>
        </div>

        <a
            class="button button-secondary"
            href="{{ route('caregiver.assignments.index') }}"
        >
            Back to Assignments
        </a>
    </div>

    <section class="panel">
        <h2>Booking and Child Information</h2>

        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Child Name</span>
                <p class="detail-value">
                    {{ $assignment->booking->child->full_name }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Date of Birth</span>
                <p class="detail-value">
                    {{ $assignment->booking->child->date_of_birth->format('d M Y') }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Service</span>
                <p class="detail-value">
                    {{ $assignment->booking->service->name }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Schedule</span>
                <p class="detail-value">
                    {{ $assignment->booking->booking_date->format('d M Y') }},
                    {{ date('h:i A', strtotime($assignment->booking->booking_time)) }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Booking Status</span>
                <p class="detail-value">
                    <span class="badge badge-{{ $assignment->booking->status }}">
                        {{ $assignment->booking->status }}
                    </span>
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Assignment Status</span>
                <p class="detail-value">
                    <span class="badge badge-active">
                        {{ $assignment->status }}
                    </span>
                </p>
            </div>

            <div class="detail-item detail-item-full">
                <span class="detail-label">Allergies</span>
                <p class="detail-value">
                    {{ $assignment->booking->child->allergies ?: 'None provided' }}
                </p>
            </div>

            <div class="detail-item detail-item-full">
                <span class="detail-label">Medical Notes</span>
                <p class="detail-value">
                    {{ $assignment->booking->child->medical_notes ?: 'None provided' }}
                </p>
            </div>

            <div class="detail-item detail-item-full">
                <span class="detail-label">Special Needs</span>
                <p class="detail-value">
                    {{ $assignment->booking->child->special_needs ?: 'None provided' }}
                </p>
            </div>

            <div class="detail-item detail-item-full">
                <span class="detail-label">Parent Instructions</span>
                <p class="detail-value">
                    {{ $assignment->booking->special_instructions ?: 'No special instructions' }}
                </p>
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="page-header">
            <div>
                <h2>Activity Updates</h2>
                <p>Record meals, naps, learning and other child activities.</p>
            </div>

            @if ($assignment->booking->status === 'confirmed')
                <a
                    class="button"
                    href="{{ route('caregiver.activities.create', $assignment) }}"
                >
                    Add Activity
                </a>
            @endif
        </div>

        @if ($activities->isEmpty())
            <div class="empty-state">
                <h2>No activities yet</h2>
                <p>Add the first activity update for this child.</p>
            </div>
        @else
            <div class="activity-list">
                @foreach ($activities as $activity)
                    <article class="activity-item">
                        <div class="page-header">
                            <div>
                                <strong>
                                    {{ ucwords(str_replace('-', ' ', $activity->activity_type)) }}
                                </strong>

                                <p class="muted">
                                    {{ $activity->activity_time->format('d M Y, h:i A') }}
                                </p>
                            </div>

                            <a
                                class="button button-small button-secondary"
                                href="{{ route('caregiver.activities.edit', $activity) }}"
                            >
                                Edit
                            </a>
                        </div>

                        <p>{{ $activity->details ?: 'No additional details.' }}</p>

                        @if ($activity->photo_path)
                            <img
                                class="activity-photo"
                                src="{{ asset('storage/' . $activity->photo_path) }}"
                                alt="Activity photo"
                            >
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
