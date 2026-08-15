@extends('layouts.parent', ['title' => 'Booking Management'])

@section('content')
<div class="page-header">
    <div>
        <h1>Booking Management</h1>
        <p>Search, review, confirm and update all bookings.</p>
    </div>
    <span class="badge badge-confirmed">{{ $bookings->count() }} results</span>
</div>

<section class="panel">
    <form method="GET" action="{{ route('admin.bookings.index') }}">
        <div class="form-grid">
            <div class="form-group">
                <label for="search">Search</label>
                <input id="search" name="search" type="text" value="{{ $search }}" placeholder="Reference, parent, child or service">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All statuses</option>
                    @foreach(['pending', 'confirmed', 'completed', 'cancelled'] as $option)
                        <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="booking_date">Booking Date</label>
                <input id="booking_date" name="booking_date" type="date" value="{{ $bookingDate }}">
            </div>
        </div>
        <div class="form-actions">
            <button class="button" type="submit">Apply Filter</button>
            <a class="button button-secondary" href="{{ route('admin.bookings.index') }}">Clear</a>
        </div>
    </form>
</section>

<section class="panel">
    @if ($bookings->isEmpty())
        <div class="empty-state">
            <h2>No bookings found</h2>
            <p>Try changing the filter or wait for a new parent booking.</p>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Booking</th>
                        <th>Parent</th>
                        <th>Child</th>
                        <th>Service</th>
                        <th>Caregiver</th>
                        <th>Date and Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bookings as $booking)
                        <tr>
                            <td>{{ $booking->display_reference }}</td>
                            <td>{{ $booking->child->parentProfile->user->name }}</td>
                            <td>{{ $booking->child->full_name }}</td>
                            <td>{{ $booking->service->name }}</td>
                            <td>{{ $booking->caregiverAssignment?->caregiver?->name ?? 'Not assigned' }}</td>
                            <td>{{ $booking->booking_date->format('d M Y') }}<br><span class="muted">{{ date('h:i A', strtotime($booking->booking_time)) }}</span></td>
                            <td><span class="badge badge-{{ $booking->status }}">{{ $booking->status }}</span></td>
                            <td><a class="button button-secondary button-small" href="{{ route('admin.bookings.show', $booking) }}">View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
