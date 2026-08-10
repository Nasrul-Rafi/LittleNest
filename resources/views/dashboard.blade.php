<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LittleNest</title>
</head>
<body>
    <h1>LittleNest Parent Dashboard</h1>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <p>Welcome, {{ auth()->user()->name }}!</p>
    <p>Email: {{ auth()->user()->email }}</p>
    <p>Role: {{ ucfirst(auth()->user()->role) }}</p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>
</html>