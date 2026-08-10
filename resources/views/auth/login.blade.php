<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LittleNest</title>
</head>
<body>
    <h1>Login to LittleNest</h1>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <div>
            <strong>Login failed:</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}">
        @csrf

        <div>
            <label for="email">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
            >
        </div>

        <div>
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
            >
        </div>

        <div>
            <label>
                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                >
                Remember me
            </label>
        </div>

        <button type="submit">Login</button>
    </form>

    <p>
        Do not have an account?
        <a href="{{ route('register') }}">Create Parent Account</a>
    </p>
</body>
</html>