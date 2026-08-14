@extends('layouts.public', ['title' => 'Parent Registration - LittleNest'])
@section('content')
<div class="form-card">
    <div class="eyebrow">Parent Registration</div>
    <h2>Create your LittleNest account</h2>
    <p class="muted">Register securely to manage children, bookings, activities and payments.</p>
    <form method="POST" action="{{ route('register.store') }}">@csrf
        <div class="form-grid" style="margin-top:20px;">
            <div><label for="name">Full Name</label><input type="text" id="name" name="name" value="{{ old('name') }}" required></div>
            <div><label for="email">Email Address</label><input type="email" id="email" name="email" value="{{ old('email') }}" required></div>
            <div><label for="password">Password</label><input type="password" id="password" name="password" required></div>
            <div><label for="password_confirmation">Confirm Password</label><input type="password" id="password_confirmation" name="password_confirmation" required></div>
            <div class="full"><button class="btn btn-primary" style="width:100%" type="submit">Create Account</button></div>
        </div>
    </form>
    <p class="muted">Already registered? <a href="{{ route('login') }}">Login</a></p>
</div>
@endsection
