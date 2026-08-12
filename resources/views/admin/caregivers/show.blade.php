@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>{{ $caregiver->name }}</h1>

            <p>Caregiver account and professional information.</p>
        </div>

        <div class="action-group">
            <a
                class="button button-secondary"
                href="{{ route('admin.caregivers.index') }}"
            >
                Back to Caregivers
            </a>

            <a
                class="button"
                href="{{ route('admin.caregivers.edit', $caregiver) }}"
            >
                Edit Caregiver
            </a>
        </div>
    </div>

    <section class="panel">
        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Full Name</span>
                <p class="detail-value">{{ $caregiver->name }}</p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Email Address</span>
                <p class="detail-value">{{ $caregiver->email }}</p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Qualification</span>
                <p class="detail-value">
                    {{ $caregiver->caregiverProfile->qualification }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Experience</span>
                <p class="detail-value">
                    {{ $caregiver->caregiverProfile->experience_years }} years
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Specialization</span>
                <p class="detail-value">
                    {{ $caregiver->caregiverProfile->specialization ?: 'Not provided' }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Availability</span>
                <p class="detail-value">
                    <span class="badge badge-{{ $caregiver->caregiverProfile->availability_status }}">
                        {{ $caregiver->caregiverProfile->availability_status }}
                    </span>
                </p>
            </div>

            <div class="detail-item detail-item-full">
                <span class="detail-label">Skills</span>
                <p class="detail-value">
                    {{ $caregiver->caregiverProfile->skills ?: 'Not provided' }}
                </p>
            </div>

            <div class="detail-item detail-item-full">
                <span class="detail-label">Bio</span>
                <p class="detail-value">
                    {{ $caregiver->caregiverProfile->bio ?: 'Not provided' }}
                </p>
            </div>

            <div class="detail-item detail-item-full">
                <span class="detail-label">Account Status</span>

                <div class="action-group">
                    <span class="badge badge-{{ $caregiver->status }}">
                        {{ $caregiver->status }}
                    </span>

                    <form
                        method="POST"
                        action="{{ route('admin.caregivers.status', $caregiver) }}"
                        onsubmit="return confirm('Change this caregiver account status?');"
                    >
                        @csrf

                        <button
                            class="button button-small {{ $caregiver->status === 'active' ? 'button-danger' : '' }}"
                            type="submit"
                        >
                            {{ $caregiver->status === 'active' ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
