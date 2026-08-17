@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>{{ $service->name }}</h1>
            <p>{{ $service->description }}</p>
        </div>

        <a class="button button-secondary" href="{{ route('parent.services.index') }}">Back to Services</a>
    </div>

    <section class="panel">
        <div class="detail-grid">
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
                <span class="detail-label">Available Time Slots</span>
                <p class="detail-value">{{ $timeSlots->count() }}</p>
            </div>
        </div>
    </section>

    <section class="panel">
        <h2>Available Time Slots</h2>

        @if ($timeSlots->isEmpty())
            <div class="empty-state">
                <p>No available time slots are currently open for this service.</p>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Available Places</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($timeSlots as $timeSlot)
                            <tr>
                                <td>{{ $timeSlot->slot_date->format('d M Y') }}</td>
                                <td>{{ $timeSlot->start_time }} – {{ $timeSlot->end_time }}</td>
                                <td>{{ $timeSlot->remainingCapacity() }}</td>
                                <td>
                                    <a
                                        class="button button-small"
                                        href="{{ route('bookings.create', ['service_id' => $service->service_id, 'slot_id' => $timeSlot->slot_id]) }}"
                                    >
                                        Book This Slot
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
