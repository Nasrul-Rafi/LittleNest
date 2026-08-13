@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>My Caregiver Profile</h1>
            <p>View your account and professional information.</p>
        </div>

        <a
            class="button"
            href="{{ route('caregiver.profile.edit') }}"
        >
            Edit Profile
        </a>
    </div>

    <section class="panel">
        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Full Name</span>
                <p class="detail-value">
                    {{ $caregiverProfile->user->name }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Email Address</span>
                <p class="detail-value">
                    {{ $caregiverProfile->user->email }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Account Status</span>
                <p class="detail-value">
                    <span
                        class="badge badge-{{ $caregiverProfile->user->status }}"
                    >
                        {{ $caregiverProfile->user->status }}
                    </span>
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Availability</span>
                <p class="detail-value">
                    <span
                        class="badge badge-{{ $caregiverProfile->availability_status }}"
                    >
                        {{ $caregiverProfile->availability_status }}
                    </span>
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Qualification</span>
                <p class="detail-value">
                    {{ $caregiverProfile->qualification }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Experience</span>
                <p class="detail-value">
                    {{ $caregiverProfile->experience_years }} years
                </p>
            </div>

            <div class="detail-item detail-item-full">
                <span class="detail-label">Specialization</span>
                <p class="detail-value">
                    {{ $caregiverProfile->specialization
                        ?: 'Not provided' }}
                </p>
            </div>

            <div class="detail-item detail-item-full">
                <span class="detail-label">Skills</span>
                <p class="detail-value">
                    {{ $caregiverProfile->skills ?: 'Not provided' }}
                </p>
            </div>

            <div class="detail-item detail-item-full">
                <span class="detail-label">Short Bio</span>
                <p class="detail-value">
                    {{ $caregiverProfile->bio ?: 'Not provided' }}
                </p>
            </div>
        </div>
    </section>
@endsection
