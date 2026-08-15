@extends('layouts.public', ['title' => 'Reset Password - LittleNest'])

@section('content')
<div class="form-card" style="max-width:620px;">
    <div class="eyebrow">Secure Reset</div>
    <h2>Create a new password</h2>
    <p class="muted">
        Choose a new password with at least 8 characters.
    </p>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-grid" style="margin-top:20px;">
            <div class="full">
                <label for="email">Email Address</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email', $email) }}"
                    required
                >
            </div>

            <div>
                <label for="password">New Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                >
            </div>

            <div>
                <label for="password_confirmation">Confirm Password</label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                >
            </div>

            <div class="full">
                <button
                    class="btn btn-primary"
                    style="width:100%;"
                    type="submit"
                >
                    Reset Password
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
