@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Edit My Profile</h1>
            <p>Update your account and emergency contact information.</p>
        </div>

        <a
            class="button button-secondary"
            href="{{ route('profile.show') }}"
        >
            Back to Profile
        </a>
    </div>

    <section class="panel">
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label for="name">
                        Full Name <span class="required">*</span>
                    </label>

                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $parentProfile->user->name) }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">
                        Email Address <span class="required">*</span>
                    </label>

                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $parentProfile->user->email) }}"
                        required
                    >
                </div>

                <div class="form-group form-group-full">
                    <label for="address">Address</label>

                    <input
                        id="address"
                        name="address"
                        type="text"
                        value="{{ old('address', $parentProfile->address) }}"
                    >
                </div>

                <div class="form-group">
                    <label for="emergency_contact_name">
                        Emergency Contact Name
                    </label>

                    <input
                        id="emergency_contact_name"
                        name="emergency_contact_name"
                        type="text"
                        value="{{ old(
                            'emergency_contact_name',
                            $parentProfile->emergency_contact_name
                        ) }}"
                    >
                </div>

                <div class="form-group">
                    <label for="emergency_contact_phone">
                        Emergency Contact Phone
                    </label>

                    <input
                        id="emergency_contact_phone"
                        name="emergency_contact_phone"
                        type="text"
                        value="{{ old(
                            'emergency_contact_phone',
                            $parentProfile->emergency_contact_phone
                        ) }}"
                    >
                </div>
            </div>

            <div class="form-actions">
                <button class="button" type="submit">
                    Save Changes
                </button>
            </div>
        </form>
    </section>
@endsection
