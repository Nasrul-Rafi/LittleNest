@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>All Bookings</h1>

            <p>Review parent booking requests and confirm pending bookings.</p>
        </div>

        <span class="badge badge-confirmed">
            {{ $bookings->count() }} total
        </span>
    </div>

    <section class="panel">
        @if ($bookings->isEmpty())
            <div class="empty-state">
                <h2>No bookings found</h2>

                <p>Parent booking requests will appear here.</p>
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
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($bookings as $booking)
                            <tr>
                                <td>{{ $booking->display_reference }}</td>
                                <td>{{ $booking->child->parentProfile->user->name }}</td>
                                <td><strong>{{ $booking->child->full_name }}</strong></td>
                                <td>{{ $booking->service->name }}</td>

                                <td>
                                    {{ $booking->caregiverAssignment?->caregiver?->name
                                        ?? 'Not assigned' }}
                                </td>

                                <td>
                                    {{ $booking->booking_date->format('d M Y') }}
                                    <br>
                                    <span class="muted">
                                        {{ date('h:i A', strtotime($booking->booking_time)) }}
                                    </span>
                                </td>

                                <td>
                                    <span
                                        class="badge badge-{{ $booking->status }}"
                                    >
                                        {{ $booking->status }}
                                    </span>
                                </td>

                                <td>
                                    <div class="action-group">
                                        <a class="button button-small button-secondary"
                                            href="{{ route('admin.bookings.show', $booking) }}">
                                            View
                                        </a>

                                        @if ($booking->status === 'pending')
                                            <form method="POST"
                                                action="{{ route('admin.bookings.confirm', $booking) }}"
                                                onsubmit="return confirm('Confirm this booking?');">
                                                @csrf

                                                <button class="button button-small" type="submit">
                                                    Confirm
                                                </button>
                                            </form>

                                            <form method="POST"
                                                action="{{ route('admin.bookings.reject', $booking) }}"
                                                onsubmit="return confirm('Reject this booking?');">
                                                @csrf

                                                <button
                                                    class="button button-small button-danger"
                                                    type="submit"
                                                >
                                                    Reject
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
