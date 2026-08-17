@extends('layouts.public')
@section('content')
<section class="hero">
    <div>
        <div class="eyebrow">Trusted child care</div>
        <h1>Happy Kids <br>Happy Life</h1>
        <p class="lead">Flexible child-care services with transparent booking, assigned caregivers and timestamped activity updates that keep parents informed.</p>
        <div class="actions">
            <a class="btn btn-primary" href="{{ route('public.services') }}">Explore Services</a>
            <a class="btn btn-secondary" href="{{ route('register') }}">Create Parent Account</a>
        </div>
    </div>
    <div class="preview">
        <div class="preview-top"><strong>Today at LittleNest</strong><br><span class="muted">A calm place to learn, rest and play.</span></div>
        <div class="preview-row"><strong>08:05 AM · Check-in</strong><br><span class="muted">Arrived cheerful and settled quickly.</span></div>
        <div class="preview-row"><strong>10:15 AM · Learning</strong><br><span class="muted">Creative matching and guided play.</span></div>
        <div class="preview-row"><strong>12:20 PM · Meal</strong><br><span class="muted">Lunch and water update recorded.</span></div>
    </div>
</section>
<section class="section">
    <div class="section-head"><div><h2>Care that fits your family</h2><div class="muted">Simple services, clear prices and available slots.</div></div><a href="{{ route('public.services') }}">View all services →</a></div>
    <div class="grid">
        @forelse($services as $service)
            <article class="card"><h3>{{ $service->name }}</h3><p class="muted">{{ $service->description ?: 'Thoughtful child-care support for busy families.' }}</p><div class="price">৳{{ number_format((float) $service->price) }}</div><p><a href="{{ route('public.services.show', $service) }}">View details →</a></p></article>
        @empty
            <article class="card"><h3>Services coming soon</h3><p class="muted">Admin can add active services from the portal.</p></article>
        @endforelse
    </div>
</section>
<section class="section">
    <div class="section-head"><div><h2>How LittleNest works</h2><div class="muted">A transparent care journey from booking to activity updates.</div></div></div>
    <div class="grid">
        <div class="card"><h3>1. Create your parent account</h3><p class="muted">Keep family, emergency and child information organised.</p></div>
        <div class="card"><h3>2. Choose a service and slot</h3><p class="muted">Book only from available dates and capacity-controlled time slots.</p></div>
        <div class="card"><h3>3. Follow care updates</h3><p class="muted">See caregiver, activity, booking and payment records in one place.</p></div>
    </div>
</section>
@endsection
