@extends('layouts.public')
@section('content')
<section class="section">
    <div class="section-head"><div><div class="eyebrow">LittleNest Services</div><h2>Choose the care format that fits your family.</h2></div></div>
    <div class="grid">
        @forelse($services as $service)
            <article class="card">
                <h3>{{ $service->name }}</h3>
                <p class="muted">{{ $service->description ?: 'Reliable child-care support with clear records.' }}</p>
                @if($service->min_age || $service->max_age)<p class="muted">Ages {{ $service->min_age ?? '?' }}–{{ $service->max_age ?? '?' }}</p>@endif
                <div class="price">৳{{ number_format((float) $service->price) }}</div>
                <p><a class="btn btn-secondary" href="{{ route('public.services.show', $service) }}">View Details</a></p>
            </article>
        @empty
            <div class="card"><h3>No active services yet</h3><p class="muted">Please check again later.</p></div>
        @endforelse
    </div>
</section>
@endsection
