@extends('layouts.parent')

@section('content')
    @php
        $childCount = auth()->user()
            ->parentProfile
            ?->children()
            ->count() ?? 0;
    @endphp

    <div class="page-header">
        <div>
            <h1>Parent Dashboard</h1>
            <p>
                Welcome back, {{ auth()->user()->name }}!
            </p>
        </div>
    </div>

    <div class="dashboard-grid">
        <section class="panel">
            <h2>My Children</h2>

            <div class="stat-number">
                {{ $childCount }}
            </div>

            <p class="muted">
                Total child profiles connected to your account.
            </p>

            <a class="button" href="{{ route('children.index') }}">
                Manage Children
            </a>
        </section>

        <section class="panel">
            <h2>Parent Account</h2>

            <p>
                <strong>Name:</strong>
                {{ auth()->user()->name }}
            </p>

            <p>
                <strong>Email:</strong>
                {{ auth()->user()->email }}
            </p>

            <p>
                <strong>Role:</strong>
                {{ ucfirst(auth()->user()->role) }}
            </p>
        </section>
    </div>
@endsection