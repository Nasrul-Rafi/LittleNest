@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Booking Requests</h1>
            <p>Review Parent cancellation and reschedule requests.</p>
        </div>
    </div>

    <section class="panel">
        @if ($bookingRequests->isEmpty())
            <div class="empty-state">
                <h2>No Booking Request Found</h2>
                <p>Parent requests will appear here.</p>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Request</th>
                            <th>Booking</th>
                            <th>Parent</th>
                            <th>Child</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($bookingRequests as $bookingRequest)
                            <tr>
                                <td>#{{ $bookingRequest->request_id }}</td>
                                <td>#{{ $bookingRequest->booking_id }}</td>
                                <td>
                                    {{ $bookingRequest->booking
                                        ->child->parentProfile->user->name }}
                                </td>
                                <td>
                                    {{ $bookingRequest->booking
                                        ->child->full_name }}
                                </td>
                                <td>
                                    {{ ucfirst($bookingRequest->request_type) }}
                                </td>
                                <td>
                                    <span
                                        class="badge badge-{{ $bookingRequest->request_status }}"
                                    >
                                        {{ $bookingRequest->request_status }}
                                    </span>
                                </td>
                                <td>
                                    <a
                                        class="button button-small button-secondary"
                                        href="{{ route(
                                            'admin.booking-requests.show',
                                            $bookingRequest
                                        ) }}"
                                    >
                                        View
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
