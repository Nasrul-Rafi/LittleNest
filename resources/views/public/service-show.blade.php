@extends('layouts.public')
@section('content')
<section class="section">
    <div class="card" style="max-width:900px;margin:0 auto;">
        <div class="eyebrow">Service Details</div>
        <h2>{{ $service->name }}</h2>
        <p class="lead">{{ $service->description ?: 'A trusted LittleNest child-care service.' }}</p>
        <div class="grid" style="margin-top:20px;grid-template-columns:repeat(3,1fr);">
            <div class="preview-row"><strong>Price</strong><br>৳{{ number_format((float)$service->price) }}</div>
            <div class="preview-row"><strong>Duration</strong><br>{{ $service->duration_minutes }} minutes</div>
            <div class="preview-row"><strong>Age</strong><br>{{ $service->min_age ?? 'Flexible' }}{{ $service->max_age ? '–'.$service->max_age : '' }}</div>
        </div>
        <h3 style="margin-top:28px;">Next available slots</h3>
        @forelse($nextSlots as $slot)
            <div class="preview-row">{{ $slot->slot_date->format('d M Y') }} · {{ substr($slot->start_time,0,5) }}–{{ substr($slot->end_time,0,5) }} · {{ $slot->remainingCapacity() }} place(s) left</div>
        @empty
            <p class="muted">No open future slots are available yet.</p>
        @endforelse
        <div class="actions"><a class="btn btn-primary" href="{{ auth()->check() ? route('bookings.create') : route('login') }}">Book this service</a></div>
    </div>
</section>
@endsection
