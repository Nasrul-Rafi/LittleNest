<div class="form-grid">
    <div class="form-group">
        <label for="service_id">
            Service <span class="required">*</span>
        </label>

        <select id="service_id" name="service_id" required>
            <option value="">Select service</option>

            @foreach ($services as $service)
                <option
                    value="{{ $service->service_id }}"
                    @selected(
                        old('service_id', $timeSlot->service_id ?? '')
                            == $service->service_id
                    )
                >
                    {{ $service->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="slot_date">
            Date <span class="required">*</span>
        </label>

        <input
            id="slot_date"
            type="date"
            name="slot_date"
            min="{{ now()->format('Y-m-d') }}"
            value="{{ old(
                'slot_date',
                isset($timeSlot)
                    ? $timeSlot->slot_date->format('Y-m-d')
                    : now()->addDay()->format('Y-m-d')
            ) }}"
            required
        >
    </div>

    <div class="form-group">
        <label for="start_time">
            Start Time <span class="required">*</span>
        </label>

        <input
            id="start_time"
            type="time"
            name="start_time"
            value="{{ old(
                'start_time',
                isset($timeSlot)
                    ? substr($timeSlot->start_time, 0, 5)
                    : ''
            ) }}"
            required
        >
    </div>

    <div class="form-group">
        <label for="end_time">
            End Time <span class="required">*</span>
        </label>

        <input
            id="end_time"
            type="time"
            name="end_time"
            value="{{ old(
                'end_time',
                isset($timeSlot)
                    ? substr($timeSlot->end_time, 0, 5)
                    : ''
            ) }}"
            required
        >
    </div>

    <div class="form-group">
        <label for="capacity">
            Capacity <span class="required">*</span>
        </label>

        <input
            id="capacity"
            type="number"
            name="capacity"
            min="1"
            max="500"
            value="{{ old('capacity', $timeSlot->capacity ?? 1) }}"
            required
        >
    </div>

    <div class="form-group">
        <label for="status">
            Status <span class="required">*</span>
        </label>

        <select id="status" name="status" required>
            <option
                value="open"
                @selected(old('status', $timeSlot->status ?? 'open') === 'open')
            >
                Open
            </option>

            <option
                value="closed"
                @selected(old('status', $timeSlot->status ?? 'open') === 'closed')
            >
                Closed
            </option>
        </select>
    </div>
</div>
