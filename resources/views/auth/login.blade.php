@extends('layouts.public', ['title' => 'Login - LittleNest'])
@section('content')
<div class="form-card" style="max-width:980px;padding:0;overflow:hidden;display:grid;grid-template-columns:.9fr 1.1fr;">
    <div style="padding:42px;background:#E8F0EC;">
        <div class="eyebrow">Welcome back</div>
        <h2 style="font-size:38px;margin-top:10px;">Better care, made simple.</h2>
        <p class="muted">Book care and stay close to every little update.</p>
    </div>
    <div style="padding:42px;">
        <h2>Sign in to LittleNest</h2>
        <p class="muted">Enter your account details to continue.</p>
        <form method="POST" action="{{ route('login.store') }}">@csrf
            <div style="margin-top:20px;"><label for="email">Email Address</label><input type="email" id="email" name="email" value="{{ old('email') }}" required></div>
            <div style="margin-top:16px;"><label for="password">Password</label><input type="password" id="password" name="password" required></div>
            <label style="margin-top:16px;display:flex;gap:8px;align-items:center;font-weight:500;"><input style="width:auto" type="checkbox" name="remember" value="1"> Remember me</label>
            <button class="btn btn-primary" style="width:100%;margin-top:20px;" type="submit">Login</button>
        </form>
        <p class="muted" style="margin-top:18px;"><a href="{{ route('password.request') }}">Forgot password?</a> &nbsp;•&nbsp; New parent? <a href="{{ route('register') }}">Create a parent account</a></p>
    </div>
</div>
<style>@media(max-width:760px){.form-card{grid-template-columns:1fr!important;}}</style>
@endsection
