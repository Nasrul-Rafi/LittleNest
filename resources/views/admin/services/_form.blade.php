<div class="form-grid">
    <div class="form-group">
        <label for="name">
            Service Name <span class="required">*</span>
        </label>

        <input
            type="text"
            id="name"
            name="name"
            value="{{ old('name', $service->name ?? '') }}"
            placeholder="Example: Weekend Child Care"
            required
        >
    </div>

    <div class="form-group">
        <label for="price">
            Price (৳) <span class="required">*</span>
        </label>

        <input
            type="number"
            id="price"
            name="price"
            min="0"
            max="99999999.99"
            step="0.01"
            value="{{ old('price', $service->price ?? '') }}"
            required
        >
    </div>

    <div class="form-group">
        <label for="min_age">Minimum Age</label>
        <input type="number" id="min_age" name="min_age" min="0" max="18" value="{{ old('min_age', $service->min_age ?? '') }}" placeholder="Example: 2">
    </div>

    <div class="form-group">
        <label for="max_age">Maximum Age</label>
        <input type="number" id="max_age" name="max_age" min="0" max="18" value="{{ old('max_age', $service->max_age ?? '') }}" placeholder="Example: 6">
    </div>

    <div class="form-group">
        <label for="duration_minutes">
            Duration (Minutes) <span class="required">*</span>
        </label>

        <input
            type="number"
            id="duration_minutes"
            name="duration_minutes"
            min="15"
            max="1440"
            value="{{ old('duration_minutes', $service->duration_minutes ?? '') }}"
            placeholder="Example: 120"
            required
        >
    </div>

    <div class="form-group">
        <label for="status">
            Status <span class="required">*</span>
        </label>

        <select id="status" name="status" required>
            <option
                value="active"
                @selected(old('status', $service->status ?? 'active') === 'active')
            >
                Active
            </option>

            <option
                value="inactive"
                @selected(old('status', $service->status ?? 'active') === 'inactive')
            >
                Inactive
            </option>
        </select>
    </div>

    <div class="form-group form-group-full">
        <label for="description">Description</label>

        <textarea
            id="description"
            name="description"
            placeholder="Describe what is included in this service"
        >{{ old('description', $service->description ?? '') }}</textarea>
    </div>
</div>
