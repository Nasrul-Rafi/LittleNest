@if ($errors->any())
    <div class="alert alert-error">
        <strong>Please correct the following errors:</strong>

        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-grid">
    <div class="form-group form-group-full">
        <label for="full_name">
            Full Name <span class="required">*</span>
        </label>

        <input
            type="text"
            id="full_name"
            name="full_name"
            value="{{ old('full_name', $child?->full_name) }}"
            maxlength="100"
            required
        >
    </div>

    <div class="form-group">
        <label for="date_of_birth">
            Date of Birth <span class="required">*</span>
        </label>

        <input
            type="date"
            id="date_of_birth"
            name="date_of_birth"
            value="{{ old(
                'date_of_birth',
                $child?->date_of_birth?->format('Y-m-d')
            ) }}"
            max="{{ now()->format('Y-m-d') }}"
            required
        >
    </div>

    <div class="form-group">
        <label for="gender">Gender</label>

        <select id="gender" name="gender">
            <option value="">Select gender</option>

            <option
                value="male"
                @selected(old('gender', $child?->gender) === 'male')
            >
                Male
            </option>

            <option
                value="female"
                @selected(old('gender', $child?->gender) === 'female')
            >
                Female
            </option>

            <option
                value="other"
                @selected(old('gender', $child?->gender) === 'other')
            >
                Other
            </option>
        </select>
    </div>


    <div class="form-group">
        <label for="guardian_relation">Guardian Relation</label>
        <input type="text" id="guardian_relation" name="guardian_relation" maxlength="50" value="{{ old('guardian_relation', $child?->guardian_relation) }}" placeholder="e.g. Mother, Father">
    </div>

    <div class="form-group">
        <label for="status">
            Status <span class="required">*</span>
        </label>

        <select id="status" name="status" required>
            <option
                value="active"
                @selected(
                    old('status', $child?->status ?? 'active') === 'active'
                )
            >
                Active
            </option>

            <option
                value="inactive"
                @selected(
                    old('status', $child?->status ?? 'active') === 'inactive'
                )
            >
                Inactive
            </option>
        </select>
    </div>

    <div class="form-group form-group-full">
        <label for="allergies">Allergies</label>

        <textarea
            id="allergies"
            name="allergies"
            maxlength="2000"
            placeholder="Write known allergies, if any."
        >{{ old('allergies', $child?->allergies) }}</textarea>
    </div>

    <div class="form-group form-group-full">
        <label for="medical_notes">Medical Notes</label>

        <textarea
            id="medical_notes"
            name="medical_notes"
            maxlength="2000"
            placeholder="Write important medical information."
        >{{ old('medical_notes', $child?->medical_notes) }}</textarea>
    </div>


    <div class="form-group form-group-full">
        <label for="medicine_instructions">Medicine Instructions</label>
        <textarea id="medicine_instructions" name="medicine_instructions" maxlength="2000" placeholder="Write medicine instructions, if any.">{{ old('medicine_instructions', $child?->medicine_instructions) }}</textarea>
    </div>

    <div class="form-group form-group-full">
        <label for="special_needs">Special Needs</label>

        <textarea
            id="special_needs"
            name="special_needs"
            maxlength="2000"
            placeholder="Write special care instructions, if any."
        >{{ old('special_needs', $child?->special_needs) }}</textarea>
    </div>
    <div class="form-group form-group-full">
        <label for="emergency_notes">Emergency Notes</label>
        <textarea id="emergency_notes" name="emergency_notes" maxlength="2000" placeholder="Add urgent care or emergency notes.">{{ old('emergency_notes', $child?->emergency_notes) }}</textarea>
    </div>
</div>