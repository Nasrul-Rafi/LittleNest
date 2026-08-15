<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>{{ $title ?? 'LittleNest Portal' }}</title>

    <style>
        :root {
            --primary: #6F8F83;
            --primary-dark: #58756B;
            --secondary: #6F8F83;
            --danger: #d63031;
            --warning: #e17055;
            --background: #FAF8F3;
            --surface: #ffffff;
            --text: #27332F;
            --muted: #68736F;
            --border: #DCE4E0;
            --success-bg: #dff7ed;
            --success-text: #087f5b;
            --error-bg: #ffe3e3;
            --error-text: #c92a2a;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: var(--text);
            background: var(--background);
            font-family: Inter, Arial, Helvetica, sans-serif;
            line-height: 1.6;
        }

        a {
            color: inherit;
        }

        .topbar {
            color: var(--text);
            background: transparent;
            box-shadow: none;
        }

        .topbar-inner {
            width: min(1120px, 92%);
            min-height: 64px;
            margin: 18px auto 0;
            padding: 10px 14px 10px 20px;
            background: white;
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 8px 28px rgba(39, 51, 47, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .brand {
            color: var(--primary-dark);
            font-size: 24px;
            font-weight: 700;
            text-decoration: none;
        }

        .navigation {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .navigation a,
        .navigation button {
            padding: 9px 14px;
            color: var(--muted);
            background: transparent;
            border: 0;
            border-radius: 7px;
            font: inherit;
            cursor: pointer;
            text-decoration: none;
        }

        .navigation a:hover,
        .navigation button:hover,
        .navigation a.active {
            color: var(--text);
            background: #E8F0EC;
        }

        .navigation form {
            margin: 0;
        }

        .container {
            width: min(1120px, 92%);
            min-height: calc(100vh - 140px);
            margin: 32px auto;
        }

        .page-header {
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
        }

        .page-header h1 {
            margin: 0 0 6px;
            font-size: 30px;
        }

        .page-header p {
            margin: 0;
            color: var(--muted);
        }

        .panel {
            padding: 24px;
            margin-bottom: 24px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(39, 51, 47, 0.04);
        }

        .panel h2 {
            margin-top: 0;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(
                auto-fit,
                minmax(280px, 1fr)
            );
            gap: 20px;
        }

        .stat-number {
            margin: 10px 0;
            color: var(--primary);
            font-size: 42px;
            font-weight: 700;
        }

        .muted {
            color: var(--muted);
        }

        .button {
            display: inline-block;
            padding: 10px 16px;
            color: var(--text);
            background: transparent;
            border: 1px solid var(--primary);
            border-radius: 8px;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }

        .button:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .button-secondary {
            color: var(--primary);
            background: white;
            border-color: var(--primary);
        }

        .button-secondary:hover {
            color: var(--text);
            background: transparent;
        }

        .button-danger {
            color: white;
            background: var(--danger);
            border-color: var(--danger);
        }

        .button-danger:hover {
            background: #b02526;
            border-color: #b02526;
        }

        .button-small {
            padding: 7px 11px;
            font-size: 14px;
        }

        .action-group,
        .form-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .action-group form,
        .form-actions form {
            margin: 0;
        }

        .form-actions {
            margin-top: 24px;
        }

        .alert {
            padding: 14px 16px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .alert-success {
            color: var(--success-text);
            background: var(--success-bg);
            border: 1px solid #b7ead8;
        }

        .alert-error {
            color: var(--error-text);
            background: var(--error-bg);
            border: 1px solid #ffc9c9;
        }

        .alert ul {
            margin: 8px 0 0;
            padding-left: 22px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .form-group-full {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: 600;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 11px 12px;
            color: var(--text);
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            font: inherit;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary);
            outline: 2px solid rgba(108, 92, 231, 0.15);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .required {
            color: var(--danger);
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 13px 12px;
            border-bottom: 1px solid var(--border);
            text-align: left;
            vertical-align: middle;
        }

        th {
            color: var(--muted);
            background: #fafafa;
            font-size: 14px;
        }

        tbody tr:hover {
            background: #F8FBF9;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .badge-active {
            color: #087f5b;
            background: #dff7ed;
        }

        .badge-inactive {
            color: #636e72;
            background: #eceff1;
        }

        .badge-available {
            color: #087f5b;
            background: #dff7ed;
        }

        .badge-unavailable {
            color: #8a5d00;
            background: #fff3bf;
        }

        .badge-open {
            color: #087f5b;
            background: #dff7ed;
        }

        .badge-closed {
            color: #636e72;
            background: #eceff1;
        }

        .badge-pending {
            color: #8a5d00;
            background: #fff3bf;
        }

        .badge-confirmed {
            color: #1c4f91;
            background: #dbeafe;
        }

        .badge-completed {
            color: #087f5b;
            background: #dff7ed;
        }

        .badge-cancelled {
            color: #c92a2a;
            background: #ffe3e3;
        }

        .badge-paid {
            color: #087f5b;
            background: #dff7ed;
        }

        .badge-failed {
            color: #c92a2a;
            background: #ffe3e3;
        }

        .badge-refunded {
            color: #765b3d;
            background: #f7efe7;
        }

        .badge-approved {
            color: #087f5b;
            background: #dff7ed;
        }

        .badge-rejected {
            color: #c92a2a;
            background: #ffe3e3;
        }

        .empty-state {
            padding: 34px 20px;
            text-align: center;
        }

        .empty-state h2 {
            margin-bottom: 8px;
        }

        .empty-state p {
            margin-bottom: 18px;
            color: var(--muted);
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .detail-item {
            padding: 16px;
            background: #fafafa;
            border: 1px solid var(--border);
            border-radius: 9px;
        }

        .detail-item-full {
            grid-column: 1 / -1;
        }

        .detail-label {
            display: block;
            margin-bottom: 5px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 600;
        }

        .detail-value {
            margin: 0;
            font-weight: 600;
        }

        .activity-list {
            display: grid;
            gap: 14px;
        }

        .activity-item {
            padding: 16px;
            background: #fafafa;
            border: 1px solid var(--border);
            border-radius: 9px;
        }

        .activity-item p {
            margin: 8px 0 0;
        }

        .activity-photo {
            display: block;
            width: min(320px, 100%);
            margin-top: 12px;
            border-radius: 9px;
        }

        .footer {
            padding: 22px;
            color: var(--muted);
            background: white;
            border-top: 1px solid var(--border);
            text-align: center;
        }

        @media (max-width: 768px) {
            .topbar-inner {
                padding: 14px 0;
                align-items: flex-start;
                flex-direction: column;
            }

            .navigation {
                width: 100%;
                flex-wrap: wrap;
            }

            .page-header {
                flex-direction: column;
            }

            .form-grid,
            .detail-grid {
                grid-template-columns: 1fr;
            }

            .form-group-full,
            .detail-item-full {
                grid-column: auto;
            }

            .panel {
                padding: 18px;
            }
        }
    </style>
</head>

<body>
    <header class="topbar">
        <div class="topbar-inner">
            <a class="brand" href="{{ route('dashboard') }}">
                LittleNest
            </a>

            <nav class="navigation">
                <a
                    href="{{ route('dashboard') }}"
                    class="{{ request()->routeIs('dashboard', 'admin.dashboard')
                        ? 'active'
                        : '' }}"
                >
                    Dashboard
                </a>

                @if (auth()->user()->role === 'parent')
                    <a
                        href="{{ route('children.index') }}"
                        class="{{ request()->routeIs('children.*')
                            ? 'active'
                            : '' }}"
                    >
                        My Children
                    </a>

                    <a
                        href="{{ route('bookings.create') }}"
                        class="{{ request()->routeIs('bookings.create') ? 'active' : '' }}"
                    >
                        Book a Service
                    </a>

                    <a
                        href="{{ route('bookings.index') }}"
                        class="{{ request()->routeIs(
                            'bookings.*',
                            'booking-requests.*'
                        )
                            ? 'active'
                            : '' }}"
                    >
                        My Bookings
                    </a>

                    <a
                        href="{{ route('activities.index') }}"
                        class="{{ request()->routeIs('activities.*')
                            ? 'active'
                            : '' }}"
                    >
                        Child Activities
                    </a>

                    <a
                        href="{{ route('payments.index') }}"
                        class="{{ request()->routeIs('payments.*')
                            ? 'active'
                            : '' }}"
                    >
                        Payments
                    </a>

                    <a
                        href="{{ route('profile.show') }}"
                        class="{{ request()->routeIs('profile.*')
                            ? 'active'
                            : '' }}"
                    >
                        My Profile
                    </a>
                @elseif (auth()->user()->role === 'caregiver')
                    <a
                        href="{{ route('caregiver.assignments.index') }}"
                        class="{{ request()->routeIs(
                            'caregiver.assignments.*'
                        )
                            ? 'active'
                            : '' }}"
                    >
                        My Assignments
                    </a>

                    <a
                        href="{{ route('caregiver.activities.index') }}"
                        class="{{ request()->routeIs(
                            'caregiver.activities.*'
                        )
                            ? 'active'
                            : '' }}"
                    >
                        Activity History
                    </a>

                    <a
                        href="{{ route('caregiver.schedule.index') }}"
                        class="{{ request()->routeIs(
                            'caregiver.schedule.*'
                        )
                            ? 'active'
                            : '' }}"
                    >
                        My Schedule
                    </a>

                    <a
                        href="{{ route('caregiver.profile.show') }}"
                        class="{{ request()->routeIs(
                            'caregiver.profile.*'
                        )
                            ? 'active'
                            : '' }}"
                    >
                        My Profile
                    </a>
                @elseif (auth()->user()->role === 'admin')
                    <a
                        href="{{ route('admin.parents.index') }}"
                        class="{{ request()->routeIs('admin.parents.*') ? 'active' : '' }}"
                    >Parents</a>

                    <a
                        href="{{ route('admin.children.index') }}"
                        class="{{ request()->routeIs('admin.children.*') ? 'active' : '' }}"
                    >Children</a>

                    <a
                        href="{{ route('admin.bookings.index') }}"
                        class="{{ request()->routeIs('admin.bookings.*')
                            ? 'active'
                            : '' }}"
                    >
                        Booking Management
                    </a>

                    <a
                        href="{{ route('admin.caregivers.index') }}"
                        class="{{ request()->routeIs('admin.caregivers.*')
                            ? 'active'
                            : '' }}"
                    >
                        Caregiver Management
                    </a>

                    <a
                        href="{{ route('admin.booking-requests.index') }}"
                        class="{{ request()->routeIs(
                            'admin.booking-requests.*'
                        )
                            ? 'active'
                            : '' }}"
                    >
                        Booking Requests
                    </a>

                    <a
                        href="{{ route('admin.payments.index') }}"
                        class="{{ request()->routeIs('admin.payments.*')
                            ? 'active'
                            : '' }}"
                    >
                        Payment Management
                    </a>

                    <a
                        href="{{ route('admin.services.index') }}"
                        class="{{ request()->routeIs('admin.services.*')
                            ? 'active'
                            : '' }}"
                    >
                        Service Management
                    </a>

                    <a
                        href="{{ route('admin.time-slots.index') }}"
                        class="{{ request()->routeIs('admin.time-slots.*')
                            ? 'active'
                            : '' }}"
                    >
                        Time Slots
                    </a>

                    <a
                        href="{{ route('admin.activities.index') }}"
                        class="{{ request()->routeIs('admin.activities.*') ? 'active' : '' }}"
                    >Activities</a>

                    <a
                        href="{{ route('admin.reports.index') }}"
                        class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
                    >Reports</a>

                    <a
                        href="{{ route('admin.inquiries.index') }}"
                        class="{{ request()->routeIs('admin.inquiries.*') ? 'active' : '' }}"
                    >Inquiries</a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit">
                        Logout
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main class="container">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <strong>
                    Please correct the following information:
                </strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="footer">
        &copy; {{ date('Y') }} LittleNest. All rights reserved.
    </footer>
</body>
</html>
