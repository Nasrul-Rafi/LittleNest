@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>{{ $service->name }}</h1>

            <p>View service price, duration and status.</p>
        </div>

        <div class="action-group">
            <a
                class="button button-secondary"
                href="{{ route('admin.services.index') }}"
            >
                Back to Services
            </a>

            <a
                class="button"
                href="{{ route('admin.services.edit', $service) }}"
            >
                Edit Service
            </a>
        </div>
    </div>

    <section class="panel">
        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Service Name</span>
                <p class="detail-value">{{ $service->name }}</p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Price</span>
                <p class="detail-value">
                    ৳{{ number_format((float) $service->price, 2) }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Duration</span>
                <p class="detail-value">
                    {{ $service->duration_minutes }} minutes
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Total Bookings</span>
                <p class="detail-value">{{ $service->bookings_count }}</p>
            </div>

            <div class="detail-item detail-item-full">
                <span class="detail-label">Description</span>
                <p class="detail-value">
                    {{ $service->description ?: 'No description provided.' }}
                </p>
            </div>

            <div class="detail-item detail-item-full">
                <span class="detail-label">Status</span>

                <div class="action-group">
                    <span class="badge badge-{{ $service->status }}">
                        {{ $service->status }}
                    </span>

                    <form
                        method="POST"
                        action="{{ route('admin.services.status', $service) }}"
                        onsubmit="return confirm('Change this service status?');"
                    >
                        @csrf

                        <button
                            class="button button-small {{ $service->status === 'active' ? 'button-danger' : '' }}"
                            type="submit"
                        >
                            {{ $service->status === 'active'
                                ? 'Deactivate'
                                : 'Activate' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
