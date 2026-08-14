@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>My Bookings</h1>
            <p>View and manage your child care bookings.</p>
        </div>

        <a class="button" href="{{ route('bookings.create') }}">
            New Booking
        </a>
    </div>

    <section class="panel">
        @if ($bookings->isEmpty())
            <div class="empty-state">
                <h2>No bookings found</h2>

                <p>
                    Create your first child care booking.
                </p>

                <a class="button" href="{{ route('bookings.create') }}">
                    Create Booking
                </a>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Child</th>
                            <th>Service</th>
                            <th>Date and Time</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($bookings as $booking)
                            <tr>
                                <td>
                                    {{ $booking->display_reference }}
                                </td>

                                <td>
                                    <strong>
                                        {{ $booking->child->full_name }}
                                    </strong>
                                </td>

                                <td>
                                    {{ $booking->service->name }}
                                </td>

                                <td>
                                    {{ $booking->booking_date->format(
                                        'd M Y'
                                    ) }}

                                    <br>

                                    <span class="muted">
                                        {{ date(
                                            'h:i A',
                                            strtotime(
                                                $booking->booking_time
                                            )
                                        ) }}
                                    </span>
                                </td>

                                <td>
                                    ৳{{ number_format(
                                        (float) $booking->total_amount,
                                        2
                                    ) }}
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
                                        <a
                                            class="button button-small button-secondary"
                                            href="{{ route(
                                                'bookings.show',
                                                $booking
                                            ) }}"
                                        >
                                            View
                                        </a>

                                        @if (in_array(
                                            $booking->status,
                                            ['pending', 'confirmed']
                                        ))
                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'bookings.cancel',
                                                    $booking
                                                ) }}"
                                                onsubmit="return confirm(
                                                    'Are you sure you want to cancel this booking?'
                                                );"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    class="button button-small button-danger"
                                                    type="submit"
                                                >
                                                    Cancel
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