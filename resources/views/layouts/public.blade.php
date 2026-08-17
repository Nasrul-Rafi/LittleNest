<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'LittleNest' }}</title>
    <style>
        :root {
            --sage: #6F8F83;
            --sage-dark: #58756B;
            --sage-light: #E8F0EC;
            --cream: #FAF8F3;
            --surface: #FFFFFF;
            --text: #27332F;
            --muted: #68736F;
            --border: #DCE4E0;
            --blue: #EEF4F6;
            --lavender: #F3F0F5;
            --peach: #F7EFE7;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Inter, Arial, sans-serif; color: var(--text); background: var(--cream); line-height: 1.6; }
        a { color: inherit; }
        .shell { width: min(1180px, 92%); margin: 0 auto; }
        .nav-wrap { padding-top: 20px; }
        .navbar { min-height: 64px; padding: 10px 14px 10px 20px; display: flex; align-items: center; justify-content: space-between; gap: 18px; background: rgba(255,255,255,.96); border: 1px solid var(--border); border-radius: 18px; box-shadow: 0 8px 28px rgba(39,51,47,.05); }
        .brand { color: var(--sage-dark); font-size: 23px; font-weight: 800; text-decoration: none; letter-spacing: -.4px; }
        .nav-links { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .nav-links a { padding: 9px 12px; border-radius: 10px; text-decoration: none; font-size: 14px; color: var(--muted); }
        .nav-links a:hover, .nav-links a.active { color: var(--text); background: var(--sage-light); }
        .nav-links .login { border: 1px solid var(--sage); color: var(--sage-dark); background: white; }
        .nav-links .register { color: white; background: var(--sage); }
        .hero { padding: 72px 0 44px; display: grid; grid-template-columns: 1.1fr .9fr; gap: 46px; align-items: center; }
        .eyebrow { color: var(--sage-dark); font-weight: 700; font-size: 14px; letter-spacing: .06em; text-transform: uppercase; }
        h1 { margin: 10px 0 18px; font-size: clamp(42px, 6vw, 70px); line-height: 1.02; letter-spacing: -2.2px; }
        h2 { margin: 0 0 10px; font-size: 30px; letter-spacing: -.8px; }
        h3 { margin: 0 0 8px; font-size: 18px; }
        .lead { max-width: 650px; color: var(--muted); font-size: 17px; }
        .actions { display: flex; gap: 12px; margin-top: 26px; flex-wrap: wrap; }
        .btn { display: inline-block; padding: 11px 17px; border-radius: 11px; text-decoration: none; font-weight: 700; border: 1px solid var(--sage); }
        .btn-primary { color: white; background: var(--sage); }
        .btn-secondary { color: var(--sage-dark); background: white; }
        .preview { padding: 22px; border: 1px solid var(--border); border-radius: 24px; background: white; box-shadow: 0 18px 60px rgba(39,51,47,.08); }
        .preview-top { padding: 18px; border-radius: 18px; background: var(--sage-light); }
        .preview-row { margin-top: 12px; padding: 14px; border-radius: 14px; background: var(--blue); }
        .preview-row:nth-child(3) { background: var(--peach); }
        .preview-row:nth-child(4) { background: var(--lavender); }
        .section { padding: 48px 0; }
        .section-head { display: flex; align-items: end; justify-content: space-between; gap: 20px; margin-bottom: 22px; }
        .muted { color: var(--muted); }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
        .card { padding: 22px; background: white; border: 1px solid var(--border); border-radius: 18px; box-shadow: 0 8px 30px rgba(39,51,47,.04); }
        .card:nth-child(3n+1) { background: #fff; }
        .card:nth-child(3n+2) { background: #fbfcfb; }
        .card:nth-child(3n+3) { background: #fdfbf8; }
        .price { margin-top: 16px; color: var(--sage-dark); font-size: 20px; font-weight: 800; }
        .form-card { max-width: 760px; margin: 50px auto; padding: 28px; background: white; border: 1px solid var(--border); border-radius: 20px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .full { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 7px; font-size: 14px; font-weight: 700; }
        input, textarea, select { width: 100%; padding: 12px 13px; border: 1px solid var(--border); border-radius: 11px; background: white; font: inherit; color: var(--text); }
        textarea { min-height: 130px; resize: vertical; }
        input:focus, textarea:focus, select:focus { outline: 3px solid rgba(111,143,131,.13); border-color: var(--sage); }
        .alert { margin-bottom: 18px; padding: 13px 15px; border-radius: 11px; }
        .success { background: #eaf5ef; color: #315f4e; }
        .error { background: #fff0f0; color: #8d3f3f; }
        footer { margin-top: 54px; padding: 34px 0; color: var(--muted); border-top: 1px solid var(--border); }
        .footer-grid { display: flex; justify-content: space-between; gap: 24px; flex-wrap: wrap; }
        @media(max-width: 820px) { .hero, .grid { grid-template-columns: 1fr; } .hero { padding-top: 48px; } .form-grid { grid-template-columns: 1fr; } .full { grid-column: auto; } .navbar { align-items: flex-start; flex-direction: column; } .nav-links { width: 100%; } }
    </style>
</head>
<body>
    <div class="nav-wrap shell">
        <nav class="navbar">
            <a class="brand" href="{{ route('home') }}">LittleNest</a>
            <div class="nav-links">
                <a class="{{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                <a class="{{ request()->routeIs('public.services*') ? 'active' : '' }}" href="{{ route('public.services') }}">Services</a>
                <a class="{{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">About</a>
                <a class="{{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Contact</a>
                @auth
                    <a class="register" href="{{ route('dashboard') }}">Dashboard</a>
                @else
                    <a class="login" href="{{ route('login') }}">Login</a>
                    <a class="register" href="{{ route('register') }}">Register as Parent</a>
                @endauth
            </div>
        </nav>
    </div>

    <main class="shell">
        @if (session('success'))
            <div class="alert success" style="margin-top:20px;">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert error" style="margin-top:20px;">
                <strong>Please correct the following information:</strong>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @yield('content')
    </main>

    <footer>
        <div class="shell footer-grid">
            <div><strong>LittleNest</strong><br><span>Safe care, clear communication and trusted support.</span></div>
            <div>Dhanmondi, Dhaka<br>littlenest@gmail.com<br>+880 1700 000000</div>
        </div>
    </footer>
</body>
</html>
