@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>My Profile</h1>
            <p>View your account and emergency contact information.</p>
        </div>

        <a class="button" href="{{ route('profile.edit') }}">
            Edit Profile
        </a>
    </div>

    <section class="panel">
        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Full Name</span>
                <p class="detail-value">
                    {{ $parentProfile->user->name }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Email Address</span>
                <p class="detail-value">
                    {{ $parentProfile->user->email }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Phone Number</span>
                <p class="detail-value">
                    {{ $parentProfile->user->phone ?: 'Not provided' }}
                </p>
            </div>

            <div class="detail-item detail-item-full">
                <span class="detail-label">Address</span>
                <p class="detail-value">
                    {{ $parentProfile->address ?: 'Not provided' }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Emergency Contact Name</span>
                <p class="detail-value">
                    {{ $parentProfile->emergency_contact_name
                        ?: 'Not provided' }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Emergency Contact Phone</span>
                <p class="detail-value">
                    {{ $parentProfile->emergency_contact_phone
                        ?: 'Not provided' }}
                </p>
            </div>
        </div>
    </section>
@endsection
