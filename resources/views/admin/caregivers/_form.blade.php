<div class="form-grid">
    <div class="form-group">
        <label for="name">
            Full Name <span class="required">*</span>
        </label>

        <input
            type="text"
            id="name"
            name="name"
            value="{{ old('name', $caregiver->name ?? '') }}"
            required
        >
    </div>

    <div class="form-group">
        <label for="email">
            Email Address <span class="required">*</span>
        </label>

        <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email', $caregiver->email ?? '') }}"
            required
        >
    </div>

    <div class="form-group">
        <label for="password">
            Password
            @if (!isset($caregiver))
                <span class="required">*</span>
            @endif
        </label>

        <input
            type="password"
            id="password"
            name="password"
            {{ !isset($caregiver) ? 'required' : '' }}
        >

        @if (isset($caregiver))
            <small class="muted">
                Leave blank to keep the current password.
            </small>
        @endif
    </div>

    <div class="form-group">
        <label for="password_confirmation">
            Confirm Password
            @if (!isset($caregiver))
                <span class="required">*</span>
            @endif
        </label>

        <input
            type="password"
            id="password_confirmation"
            name="password_confirmation"
            {{ !isset($caregiver) ? 'required' : '' }}
        >
    </div>

    <div class="form-group">
        <label for="qualification">
            Qualification <span class="required">*</span>
        </label>

        <input
            type="text"
            id="qualification"
            name="qualification"
            value="{{ old('qualification', $caregiver->caregiverProfile->qualification ?? '') }}"
            placeholder="Example: Diploma in Child Care"
            required
        >
    </div>

    <div class="form-group">
        <label for="experience_years">
            Experience (Years) <span class="required">*</span>
        </label>

        <input
            type="number"
            id="experience_years"
            name="experience_years"
            min="0"
            max="60"
            value="{{ old('experience_years', $caregiver->caregiverProfile->experience_years ?? 0) }}"
            required
        >
    </div>

    <div class="form-group">
        <label for="specialization">Specialization</label>

        <input
            type="text"
            id="specialization"
            name="specialization"
            value="{{ old('specialization', $caregiver->caregiverProfile->specialization ?? '') }}"
            placeholder="Example: Infant Care"
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
                    $caregiver->caregiverProfile->availability_status ?? 'available'
                ) === 'available')
            >
                Available
            </option>

            <option
                value="unavailable"
                @selected(old(
                    'availability_status',
                    $caregiver->caregiverProfile->availability_status ?? 'available'
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
            placeholder="Example: First aid, meal preparation, storytelling"
        >{{ old('skills', $caregiver->caregiverProfile->skills ?? '') }}</textarea>
    </div>

    <div class="form-group form-group-full">
        <label for="bio">Short Bio</label>

        <textarea
            id="bio"
            name="bio"
            placeholder="Write a short introduction about the caregiver"
        >{{ old('bio', $caregiver->caregiverProfile->bio ?? '') }}</textarea>
    </div>
</div>
