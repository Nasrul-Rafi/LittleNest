@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Edit Caregiver Profile</h1>
            <p>Update your account and professional information.</p>
        </div>

        <a
            class="button button-secondary"
            href="{{ route('caregiver.profile.show') }}"
        >
            Back to Profile
        </a>
    </div>

    <section class="panel">
        <form
            method="POST"
            action="{{ route('caregiver.profile.update') }}"
        >
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
                        value="{{ old(
                            'name',
                            $caregiverProfile->user->name
                        ) }}"
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
                        value="{{ old(
                            'email',
                            $caregiverProfile->user->email
                        ) }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="qualification">
                        Qualification <span class="required">*</span>
                    </label>

                    <input
                        id="qualification"
                        name="qualification"
                        type="text"
                        value="{{ old(
                            'qualification',
                            $caregiverProfile->qualification
                        ) }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="experience_years">
                        Experience (Years)
                        <span class="required">*</span>
                    </label>

                    <input
                        id="experience_years"
                        name="experience_years"
                        type="number"
                        min="0"
                        max="60"
                        value="{{ old(
                            'experience_years',
                            $caregiverProfile->experience_years
                        ) }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="specialization">Specialization</label>

                    <input
                        id="specialization"
                        name="specialization"
                        type="text"
                        value="{{ old(
                            'specialization',
                            $caregiverProfile->specialization
                        ) }}"
                    >
                </div>

                <div class="form-group">
                    <label for="availability_status">
                        Availability <span class="required">*</span>
                    </label>

                    <select
                        id="availability_status"
                        name="availability_status"
                        required
                    >
                        <option
                            value="available"
                            @selected(old(
                                'availability_status',
                                $caregiverProfile->availability_status
                            ) === 'available')
                        >
                            Available
                        </option>

                        <option
                            value="unavailable"
                            @selected(old(
                                'availability_status',
                                $caregiverProfile->availability_status
                            ) === 'unavailable')
                        >
                            Unavailable
                        </option>
                    </select>
                </div>

                <div class="form-group form-group-full">
                    <label for="skills">Skills</label>
                    <textarea
                        id="skills"
                        name="skills"
                    >{{ old('skills', $caregiverProfile->skills) }}</textarea>
                </div>

                <div class="form-group form-group-full">
                    <label for="bio">Short Bio</label>
                    <textarea
                        id="bio"
                        name="bio"
                    >{{ old('bio', $caregiverProfile->bio) }}</textarea>
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
