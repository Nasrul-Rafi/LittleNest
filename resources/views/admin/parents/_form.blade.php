<div class="form-grid">
    <div class="form-group">
        <label for="name">Full Name <span class="required">*</span></label>
        <input id="name" name="name" type="text" value="{{ old('name', $parent->name ?? '') }}" required>
    </div>

    <div class="form-group">
        <label for="email">Email <span class="required">*</span></label>
        <input id="email" name="email" type="email" value="{{ old('email', $parent->email ?? '') }}" required>
    </div>

    <div class="form-group">
        <label for="phone">Phone <span class="required">*</span></label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $parent->phone ?? '') }}" required>
    </div>

    <div class="form-group">
        <label for="address">Address</label>
        <input id="address" name="address" type="text" value="{{ old('address', $parent->parentProfile->address ?? '') }}">
    </div>

    <div class="form-group">
        <label for="emergency_contact_name">Emergency Contact Name</label>
        <input id="emergency_contact_name" name="emergency_contact_name" type="text" value="{{ old('emergency_contact_name', $parent->parentProfile->emergency_contact_name ?? '') }}">
    </div>

    <div class="form-group">
        <label for="emergency_contact_phone">Emergency Contact Phone</label>
        <input id="emergency_contact_phone" name="emergency_contact_phone" type="text" value="{{ old('emergency_contact_phone', $parent->parentProfile->emergency_contact_phone ?? '') }}">
    </div>

    <div class="form-group">
        <label for="password">{{ isset($parent) ? 'New Password' : 'Password' }} {{ isset($parent) ? '' : '*' }}</label>
        <input id="password" name="password" type="password" {{ isset($parent) ? '' : 'required' }}>
    </div>

    <div class="form-group">
        <label for="password_confirmation">Confirm Password {{ isset($parent) ? '' : '*' }}</label>
        <input id="password_confirmation" name="password_confirmation" type="password" {{ isset($parent) ? '' : 'required' }}>
    </div>
</div>
