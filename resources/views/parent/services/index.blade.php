@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Services</h1>
            <p>Choose the type of care that works best for your child.</p>
        </div>
    </div>

    <div class="dashboard-grid">
        @forelse ($services as $service)
            <section class="panel">
                <h2>{{ $service->name }}</h2>
                <p class="muted">{{ $service->description }}</p>

                <div class="detail-grid" style="margin-top:16px;">
                    <div class="detail-item">
                        <span class="detail-label">Price</span>
                        <p class="detail-value">৳{{ number_format((float) $service->price, 2) }}</p>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Duration</span>
                        <p class="detail-value">{{ $service->duration_minutes }} minutes</p>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Age Range</span>
                        <p class="detail-value">
                            @if ($service->min_age !== null && $service->max_age !== null)
                                {{ $service->min_age }}–{{ $service->max_age }} years
                            @else
                                Contact us for details
                            @endif
                        </p>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Upcoming Slots</span>
                        <p class="detail-value">{{ $service->available_slots_count }}</p>
                    </div>
                </div>

                <div class="form-actions">
                    <a class="button" href="{{ route('parent.services.show', $service) }}">View Service</a>
                </div>
            </section>
        @empty
            <section class="panel empty-state">
                <h2>No services are available right now</h2>
                <p>Please check again later.</p>
            </section>
        @endforelse
    </div>
@endsection
