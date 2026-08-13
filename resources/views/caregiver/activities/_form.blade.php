<div class="form-grid">
    <div class="form-group">
        <label for="activity_type">
            Activity Type <span class="required">*</span>
        </label>

        <select id="activity_type" name="activity_type" required>
            <option value="">Choose an activity</option>

            @foreach ([
                'check-in' => 'Check-in',
                'check-out' => 'Check-out',
                'meal' => 'Meal / Feeding',
                'nap' => 'Nap',
                'play' => 'Play',
                'learning' => 'Learning',
                'toilet' => 'Toilet / Diaper',
                'health' => 'Health',
                'medicine' => 'Medicine',
                'mood' => 'Mood / Behaviour',
                'special-notes' => 'Special Notes',
            ] as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(old('activity_type', $activity->activity_type ?? '') === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="activity_time">
            Activity Date and Time <span class="required">*</span>
        </label>

        <input
            type="datetime-local"
            id="activity_time"
            name="activity_time"
            max="{{ now()->format('Y-m-d\TH:i') }}"
            value="{{ old(
                'activity_time',
                isset($activity)
                    ? $activity->activity_time->format('Y-m-d\TH:i')
                    : now()->format('Y-m-d\TH:i')
            ) }}"
            required
        >
    </div>

    <div class="form-group form-group-full">
        <label for="details">Details</label>

        <textarea
            id="details"
            name="details"
            placeholder="Write a short activity update"
        >{{ old('details', $activity->details ?? '') }}</textarea>
    </div>

    <div class="form-group form-group-full">
        <label for="photo">Optional Photo</label>

        <input
            type="file"
            id="photo"
            name="photo"
            accept=".jpg,.jpeg,.png"
        >

        <small class="muted">
            JPG or PNG only. Maximum size: 2 MB.
        </small>

        @if (isset($activity) && $activity->photo_path)
            <img
                class="activity-photo"
                src="{{ asset('storage/' . $activity->photo_path) }}"
                alt="Current activity photo"
            >
        @endif
    </div>
</div>
