@extends('layouts.public')
@section('content')
<div class="form-card">
    <div class="eyebrow">Contact LittleNest</div><h2>Send an enquiry</h2><p class="muted">Our team can review your message from the Admin portal.</p>
    <form method="POST" action="{{ route('contact.store') }}">@csrf
        <div class="form-grid">
            <div><label>Full name</label><input name="full_name" value="{{ old('full_name') }}" required></div>
            <div><label>Email address</label><input type="email" name="email" value="{{ old('email') }}" required></div>
            <div><label>Phone number</label><input name="phone" value="{{ old('phone') }}"></div>
            <div><label>Subject</label><input name="subject" value="{{ old('subject') }}" required></div>
            <div class="full"><label>Message</label><textarea name="message" required>{{ old('message') }}</textarea></div>
            <div class="full"><button class="btn btn-primary" type="submit">Send Enquiry</button></div>
        </div>
    </form>
</div>
@endsection
