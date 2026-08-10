<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>{{ $title ?? 'Parent Portal' }} - LittleNest</title>

    <style>
        :root {
            --primary: #6c5ce7;
            --primary-dark: #5647c7;
            --secondary: #00b894;
            --danger: #d63031;
            --warning: #e17055;
            --background: #f6f7fb;
            --surface: #ffffff;
            --text: #2d3436;
            --muted: #636e72;
            --border: #dfe6e9;
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
            font-family: Arial, sans-serif;
            color: var(--text);
            background: var(--background);
            line-height: 1.5;
        }

        a {
            color: inherit;
        }

        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
        }

        .topbar-inner {
            width: min(1120px, 92%);
            min-height: 70px;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .brand {
            color: var(--primary);
            font-size: 24px;
            font-weight: 700;
            text-decoration: none;
        }

        .navigation {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .navigation a {
            padding: 10px 14px;
            color: var(--muted);
            font-weight: 600;
            text-decoration: none;
            border-radius: 8px;
        }

        .navigation a:hover,
        .navigation a.active {
            color: var(--primary);
            background: #eeebff;
        }

        .logout-button {
            padding: 10px 14px;
            color: var(--danger);
            background: transparent;
            border: 0;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .logout-button:hover {
            background: #fff0f0;
        }

        .container {
            width: min(1120px, 92%);
            margin: 32px auto;
        }

        .page-header {
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
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
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 6px 20px rgba(45, 52, 54, 0.06);
        }

        .button {
            display: inline-block;
            padding: 10px 16px;
            color: white;
            background: var(--primary);
            border: 1px solid var(--primary);
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .button:hover {
            background: var(--primary-dark);
        }

        .button-secondary {
            color: var(--primary);
            background: white;
        }

        .button-secondary:hover {
            color: white;
        }

        .button-danger {
            background: var(--danger);
            border-color: var(--danger);
        }

        .button-danger:hover {
            background: #b02525;
        }

        .button-small {
            padding: 7px 11px;
            font-size: 13px;
        }

        .alert {
            margin-bottom: 20px;
            padding: 14px 16px;
            border-radius: 8px;
        }

        .alert-success {
            color: var(--success-text);
            background: var(--success-bg);
        }

        .alert-error {
            color: var(--error-text);
            background: var(--error-bg);
        }

        .alert ul {
            margin: 8px 0 0;
            padding-left: 20px;
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

        label {
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
            outline: 3px solid #eeebff;
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        .required {
            color: var(--danger);
        }

        .form-actions,
        .action-group {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .form-actions {
            margin-top: 24px;
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
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        th {
            color: var(--muted);
            background: #fafafa;
            font-size: 13px;
            text-transform: uppercase;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .badge-active {
            color: #087f5b;
            background: #dff7ed;
        }

        .badge-inactive {
            color: #b25f00;
            background: #fff3bf;
        }

        .empty-state {
            padding: 45px 20px;
            text-align: center;
        }

        .empty-state h2 {
            margin-bottom: 8px;
        }

        .empty-state p {
            margin-bottom: 22px;
            color: var(--muted);
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .detail-item {
            padding: 16px;
            background: #fafafa;
            border: 1px solid var(--border);
            border-radius: 10px;
        }

        .detail-item-full {
            grid-column: 1 / -1;
        }

        .detail-label {
            display: block;
            margin-bottom: 6px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .detail-value {
            margin: 0;
            white-space: pre-wrap;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .stat-number {
            margin: 8px 0;
            color: var(--primary);
            font-size: 36px;
            font-weight: 700;
        }

        .muted {
            color: var(--muted);
        }

        .footer {
            padding: 20px;
            color: var(--muted);
            text-align: center;
            font-size: 13px;
        }

        @media (max-width: 700px) {
            .topbar-inner,
            .page-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .topbar-inner {
                padding: 16px 0;
            }

            .navigation {
                width: 100%;
                flex-wrap: wrap;
            }

            .form-grid,
            .detail-grid,
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .form-group-full,
            .detail-item-full {
                grid-column: auto;
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
                    class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"
                >
                    Dashboard
                </a>

                @if (auth()->user()->role === 'parent')
                    <a
                        href="{{ route('children.index') }}"
                        class="{{ request()->routeIs('children.*') ? 'active' : '' }}"
                    >
                        My Children
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button class="logout-button" type="submit">
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

        @yield('content')
    </main>

    <footer class="footer">
        &copy; {{ date('Y') }} LittleNest Child Care System
    </footer>
</body>
</html>