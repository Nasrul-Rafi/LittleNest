@extends('layouts.public', ['title' => 'Forgot Password - LittleNest'])

@section('content')
<div class="form-card" style="max-width:560px;">
    <div class="eyebrow">Account Recovery</div>
    <h2>Reset your password</h2>
    <p class="muted">
        Enter the email address connected to your LittleNest account.
        We will send you a secure reset link.
    </p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div style="margin-top:20px;">
            <label for="email">Email Address</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
            >
        </div>

        <button
            class="btn btn-primary"
            style="width:100%; margin-top:18px;"
            type="submit"
        >
            Send Reset Link
        </button>
    </form>

    <p class="muted" style="margin-top:18px;">
        Remembered your password?
        <a href="{{ route('login') }}">Back to login</a>
    </p>
</div>
@endsection
