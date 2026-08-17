<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'LittleNest Portal' }}</title>
    <style>
        :root {
            --primary: #6F8F83;
            --primary-dark: #58756B;
            --primary-light: #E8F0EC;
            --cream: #FAF8F3;
            --neutral: #F6F7F5;
            --blue: #EEF4F6;
            --lavender: #F3F0F5;
            --peach: #F7EFE7;
            --surface: #FFFFFF;
            --text: #27332F;
            --muted: #68736F;
            --border: #DCE4E0;
            --danger: #C65D5D;
            --success-bg: #E7F2ED;
            --success-text: #315F4E;
            --error-bg: #FBECEC;
            --error-text: #934646;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: var(--text);
            background: var(--cream);
            font-family: Inter, Arial, Helvetica, sans-serif;
            line-height: 1.55;
        }

        a { color: inherit; }

        .portal-shell {
            width: min(1380px, 96%);
            min-height: calc(100vh - 36px);
            margin: 18px auto;
            display: grid;
            grid-template-columns: 236px minmax(0, 1fr);
            gap: 22px;
        }

        .sidebar {
            position: sticky;
            top: 18px;
            height: calc(100vh - 36px);
            padding: 22px 16px;
            color: white;
            background: var(--primary-dark);
            border-radius: 22px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .brand {
            display: block;
            margin: 2px 10px 0;
            color: white;
            font-size: 23px;
            font-weight: 800;
            text-decoration: none;
            letter-spacing: -.4px;
        }

        .role-label {
            margin: 2px 10px 22px;
            color: rgba(255,255,255,.72);
            font-size: 12px;
        }

        .navigation {
            display: grid;
            gap: 5px;
        }

        .navigation a,
        .navigation button {
            width: 100%;
            padding: 10px 12px;
            color: rgba(255,255,255,.85);
            background: transparent;
            border: 0;
            border-radius: 10px;
            font: inherit;
            font-size: 14px;
            cursor: pointer;
            text-align: left;
            text-decoration: none;
        }

        .navigation a:hover,
        .navigation button:hover,
        .navigation a.active {
            color: white;
            background: rgba(255,255,255,.13);
        }

        .navigation form {
            margin: 8px 0 0;
        }

        .portal-main {
            min-width: 0;
        }

        .topbar {
            min-height: 70px;
            padding: 14px 18px;
            margin-bottom: 22px;
            background: rgba(255,255,255,.94);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 8px 28px rgba(39,51,47,.04);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
        }

        .topbar strong {
            display: block;
            font-size: 15px;
        }

        .topbar span {
            color: var(--muted);
            font-size: 12px;
        }

        .user-chip {
            min-width: 42px;
            height: 42px;
            padding: 0 12px;
            color: var(--primary-dark);
            background: var(--primary-light);
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
        }

        .container {
            min-height: calc(100vh - 130px);
        }

        .page-header {
            margin: 0 0 22px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
        }

        .page-header h1 {
            margin: 0 0 5px;
            font-size: clamp(25px, 3vw, 32px);
            line-height: 1.2;
            letter-spacing: -.7px;
        }

        .page-header p {
            margin: 0;
            color: var(--muted);
        }

        .panel {
            padding: 22px;
            margin-bottom: 20px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 8px 28px rgba(39,51,47,.035);
        }

        .panel h2 {
            margin: 0 0 12px;
            font-size: 19px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 16px;
        }

        .dashboard-grid .panel:nth-child(4n+2) { background: #FCF8F3; }
        .dashboard-grid .panel:nth-child(4n+3) { background: #F7FAFB; }
        .dashboard-grid .panel:nth-child(4n+4) { background: #F9F7FA; }

        .stat-number {
            margin: 8px 0;
            color: var(--primary-dark);
            font-size: 38px;
            font-weight: 800;
            line-height: 1.1;
        }

        .compact-stat { font-size: 28px; }

        .stat-text {
            margin: 10px 0 2px;
            color: var(--primary-dark);
            font-size: 22px;
            font-weight: 800;
        }

        .muted { color: var(--muted); }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 9px 15px;
            color: white;
            background: var(--primary);
            border: 1px solid var(--primary);
            border-radius: 9px;
            font: inherit;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }

        .button:hover {
            color: white;
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .button-secondary {
            color: var(--primary-dark);
            background: white;
            border-color: var(--border);
        }

        .button-secondary:hover {
            color: var(--primary-dark);
            background: var(--primary-light);
            border-color: var(--primary);
        }

        .button-danger {
            color: white;
            background: var(--danger);
            border-color: var(--danger);
        }

        .button-danger:hover {
            background: #A94D4D;
            border-color: #A94D4D;
        }

        .button-small {
            min-height: 34px;
            padding: 6px 10px;
            font-size: 13px;
        }

        .action-group,
        .form-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 9px;
        }

        .action-group form,
        .form-actions form { margin: 0; }

        .form-actions { margin-top: 22px; }

        .alert {
            padding: 13px 15px;
            margin-bottom: 18px;
            border-radius: 11px;
        }

        .alert-success {
            color: var(--success-text);
            background: var(--success-bg);
            border: 1px solid #C8E2D7;
        }

        .alert-error {
            color: var(--error-text);
            background: var(--error-bg);
            border: 1px solid #F0CACA;
        }

        .alert ul { margin: 7px 0 0; padding-left: 20px; }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 17px;
        }

        .form-grid-three {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .pagination-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }

        .pagination-label {
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        .disabled-button {
            pointer-events: none;
            opacity: .5;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group-full { grid-column: 1 / -1; }

        .form-group label {
            font-size: 14px;
            font-weight: 700;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px 12px;
            color: var(--text);
            background: white;
            border: 1px solid var(--border);
            border-radius: 9px;
            font: inherit;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary);
            outline: 3px solid rgba(111,143,131,.12);
        }

        textarea {
            min-height: 118px;
            resize: vertical;
        }

        .required { color: var(--danger); }

        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px 11px;
            border-bottom: 1px solid var(--border);
            text-align: left;
            vertical-align: middle;
        }

        th {
            color: var(--muted);
            background: var(--neutral);
            font-size: 13px;
        }

        tbody tr:hover { background: #FAFCFB; }

        .badge {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .badge-active,
        .badge-available,
        .badge-open,
        .badge-completed,
        .badge-paid,
        .badge-approved,
        .badge-resolved {
            color: #315F4E;
            background: #E7F2ED;
        }

        .badge-inactive,
        .badge-closed {
            color: #616A67;
            background: #ECEFED;
        }

        .badge-unavailable,
        .badge-pending,
        .badge-new {
            color: #80602B;
            background: #F7EFE0;
        }

        .badge-confirmed,
        .badge-open {
            color: #486271;
            background: #E7F1F5;
        }

        .badge-cancelled,
        .badge-failed,
        .badge-rejected {
            color: #934646;
            background: #FBECEC;
        }

        .badge-refunded {
            color: #765B3D;
            background: var(--peach);
        }

        .badge-assigned {
            color: #486271;
            background: var(--blue);
        }

        .empty-state {
            padding: 34px 20px;
            text-align: center;
        }

        .empty-state h2 { margin-bottom: 8px; }
        .empty-state p { margin-bottom: 18px; color: var(--muted); }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
        }

        .detail-item {
            padding: 15px;
            background: var(--neutral);
            border: 1px solid var(--border);
            border-radius: 11px;
        }

        .detail-item-full { grid-column: 1 / -1; }

        .detail-label {
            display: block;
            margin-bottom: 4px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }

        .detail-value {
            margin: 0;
            font-weight: 650;
        }

        .activity-list {
            display: grid;
            gap: 12px;
        }

        .activity-item {
            padding: 15px;
            margin-bottom: 10px;
            background: var(--blue);
            border: 1px solid var(--border);
            border-radius: 11px;
        }

        .activity-item:nth-child(3n+2) { background: var(--peach); }
        .activity-item:nth-child(3n+3) { background: var(--lavender); }

        .activity-item p { margin: 7px 0 0; }

        .activity-photo {
            display: block;
            width: min(320px, 100%);
            margin-top: 12px;
            border-radius: 10px;
        }

        .footer {
            padding: 16px;
            color: var(--muted);
            font-size: 12px;
            text-align: center;
        }

        @media (max-width: 980px) {
            .portal-shell {
                width: min(94%, 900px);
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
                height: auto;
                border-radius: 18px;
            }

            .navigation {
                display: flex;
                flex-wrap: wrap;
            }

            .navigation a,
            .navigation button {
                width: auto;
            }

            .navigation form {
                margin: 0;
            }
        }

        @media (max-width: 680px) {
            .portal-shell { width: 94%; margin-top: 12px; }
            .sidebar { padding: 18px 13px; }
            .topbar { align-items: flex-start; }
            .page-header { flex-direction: column; }
            .form-grid,
            .form-grid-three,
            .detail-grid { grid-template-columns: 1fr; }
            .form-group-full,
            .detail-item-full { grid-column: auto; }
            .panel { padding: 17px; }
            th, td { padding: 10px 9px; white-space: nowrap; }
        }
    </style>
</head>
<body>
    <div class="portal-shell">
        <aside class="sidebar">
            <a class="brand" href="{{ route('dashboard') }}">LittleNest</a>
            <div class="role-label">{{ ucfirst(auth()->user()->role) }} Portal</div>

            <nav class="navigation">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard', 'admin.dashboard') ? 'active' : '' }}">Dashboard</a>

                @if (auth()->user()->role === 'parent')
                    <a href="{{ route('children.index') }}" class="{{ request()->routeIs('children.*') ? 'active' : '' }}">My Children</a>
                    <a href="{{ route('parent.services.index') }}" class="{{ request()->routeIs('parent.services.*') ? 'active' : '' }}">Services</a>
                    <a href="{{ route('bookings.create') }}" class="{{ request()->routeIs('bookings.create') ? 'active' : '' }}">Book a Service</a>
                    <a href="{{ route('bookings.index') }}" class="{{ request()->routeIs('bookings.*', 'booking-requests.*') ? 'active' : '' }}">My Bookings</a>
                    <a href="{{ route('activities.index') }}" class="{{ request()->routeIs('activities.*') ? 'active' : '' }}">Child Activities</a>
                    <a href="{{ route('payments.index') }}" class="{{ request()->routeIs('payments.*') ? 'active' : '' }}">Payments</a>
                    <a href="{{ route('profile.show') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">Profile</a>
                @elseif (auth()->user()->role === 'caregiver')
                    <a href="{{ route('caregiver.assignments.index') }}" class="{{ request()->routeIs('caregiver.assignments.*') ? 'active' : '' }}">Assigned Children</a>
                    <a href="{{ route('caregiver.activities.index') }}" class="{{ request()->routeIs('caregiver.activities.*') ? 'active' : '' }}">Activity History</a>
                    <a href="{{ route('caregiver.schedule.index') }}" class="{{ request()->routeIs('caregiver.schedule.*') ? 'active' : '' }}">Schedule</a>
                    <a href="{{ route('caregiver.profile.show') }}" class="{{ request()->routeIs('caregiver.profile.*') ? 'active' : '' }}">Profile</a>
                @elseif (auth()->user()->role === 'admin')
                    <a href="{{ route('admin.parents.index') }}" class="{{ request()->routeIs('admin.parents.*') ? 'active' : '' }}">Parents</a>
                    <a href="{{ route('admin.children.index') }}" class="{{ request()->routeIs('admin.children.*') ? 'active' : '' }}">Children</a>
                    <a href="{{ route('admin.caregivers.index') }}" class="{{ request()->routeIs('admin.caregivers.*') ? 'active' : '' }}">Caregivers</a>
                    <a href="{{ route('admin.services.index') }}" class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}">Services</a>
                    <a href="{{ route('admin.time-slots.index') }}" class="{{ request()->routeIs('admin.time-slots.*') ? 'active' : '' }}">Time Slots</a>
                    <a href="{{ route('admin.bookings.index') }}" class="{{ request()->routeIs('admin.bookings.*', 'admin.booking-requests.*') ? 'active' : '' }}">Bookings</a>
                    <a href="{{ route('admin.assignments.index') }}" class="{{ request()->routeIs('admin.assignments.*') ? 'active' : '' }}">Assignments</a>
                    <a href="{{ route('admin.activities.index') }}" class="{{ request()->routeIs('admin.activities.*') ? 'active' : '' }}">Activities</a>
                    <a href="{{ route('admin.payments.index') }}" class="{{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">Payments</a>
                    <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">Reports</a>
                    <a href="{{ route('admin.inquiries.index') }}" class="{{ request()->routeIs('admin.inquiries.*') ? 'active' : '' }}">Inquiries</a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            </nav>
        </aside>

        <div class="portal-main">
            <header class="topbar">
                <div>
                    <strong>Welcome back, {{ auth()->user()->name }}</strong>
                    <span>Keep care records clear, secure and up to date.</span>
                </div>
                <div class="user-chip">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            </header>

            <main class="container">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-error">
                        <strong>Please correct the following information:</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="footer">&copy; {{ date('Y') }} LittleNest. All rights reserved.</footer>
        </div>
    </div>
</body>
</html>
