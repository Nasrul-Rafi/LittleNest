@extends('layouts.parent', ['title' => 'My Bookings'])

@section('content')
<div class="page-header">
    <div>
        <h1>My Bookings</h1>
        <p>Search, review and manage your child care bookings.</p>
    </div>
    <a class="button" href="{{ route('bookings.create') }}">Book a Service</a>
</div>

<section class="panel">
    <form method="GET" action="{{ route('bookings.index') }}">
        <div class="form-grid form-grid-three">
            <div class="form-group">
                <label for="search">Search</label>
                <input id="search" name="search" type="text" value="{{ $search }}" placeholder="Reference, child or service">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All statuses</option>
                    <option value="pending" @selected($status === 'pending')>Pending</option>
                    <option value="confirmed" @selected($status === 'confirmed')>Confirmed</option>
                    <option value="completed" @selected($status === 'completed')>Completed</option>
                    <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
                </select>
            </div>
            <div class="form-group">
                <label for="month">Month</label>
                <input id="month" name="month" type="month" value="{{ $month }}">
            </div>
        </div>
        <div class="form-actions">
            <button class="button" type="submit">Apply Filters</button>
            <a class="button button-secondary" href="{{ route('bookings.index') }}">Clear</a>
        </div>
    </form>
</section>

<section class="panel">
    @if ($bookings->isEmpty())
        <div class="empty-state">
            <h2>No bookings found</h2>
            <p>Try another filter or create a new child care booking.</p>
            <a class="button" href="{{ route('bookings.create') }}">Create Booking</a>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Child</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bookings as $booking)
                        <tr>
                            <td>{{ $booking->display_reference }}</td>
                            <td><strong>{{ $booking->child->full_name }}</strong></td>
                            <td>{{ $booking->service->name }}</td>
                            <td>
                                {{ $booking->booking_date->format('d M Y') }}
                                <br>
                                <span class="muted">{{ date('h:i A', strtotime($booking->booking_time)) }}</span>
                            </td>
                            <td>৳{{ number_format((float) $booking->total_amount, 2) }}</td>
                            <td><span class="badge badge-{{ $booking->status }}">{{ $booking->status }}</span></td>
                            <td>
                                <a class="button button-small button-secondary" href="{{ route('bookings.show', $booking) }}">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($bookings->hasPages())
            <div class="pagination-bar">
                @if ($bookings->onFirstPage())
                    <span class="button button-secondary button-small disabled-button">Previous</span>
                @else
                    <a class="button button-secondary button-small" href="{{ $bookings->previousPageUrl() }}">Previous</a>
                @endif

                <span class="pagination-label">Page {{ $bookings->currentPage() }} of {{ $bookings->lastPage() }}</span>

                @if ($bookings->hasMorePages())
                    <a class="button button-secondary button-small" href="{{ $bookings->nextPageUrl() }}">Next</a>
                @else
                    <span class="button button-secondary button-small disabled-button">Next</span>
                @endif
            </div>
        @endif
    @endif
</section>
@endsection
