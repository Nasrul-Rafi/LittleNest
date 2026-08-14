@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Time Slot Management</h1>
            <p>Manage service dates, times and booking capacity.</p>
        </div>

        <a class="button" href="{{ route('admin.time-slots.create') }}">
            Add Time Slot
        </a>
    </div>

    <section class="panel">
        @if ($timeSlots->isEmpty())
            <div class="empty-state">
                <h2>No time slots found</h2>
                <p>Create a slot before parents can make new bookings.</p>

                <a class="button" href="{{ route('admin.time-slots.create') }}">
                    Add Time Slot
                </a>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Capacity</th>
                            <th>Reserved</th>
                            <th>Available</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($timeSlots as $timeSlot)
                            <tr>
                                <td>
                                    <strong>{{ $timeSlot->service->name }}</strong>
                                </td>

                                <td>
                                    {{ $timeSlot->slot_date->format('d M Y') }}
                                </td>

                                <td>
                                    {{ date('h:i A', strtotime($timeSlot->start_time)) }}
                                    –
                                    {{ date('h:i A', strtotime($timeSlot->end_time)) }}
                                </td>

                                <td>{{ $timeSlot->capacity }}</td>
                                <td>{{ $timeSlot->active_bookings_count }}</td>
                                <td>{{ max(0, $timeSlot->capacity - $timeSlot->active_bookings_count) }}</td>

                                <td>
                                    <span class="badge badge-{{ $timeSlot->status }}">
                                        {{ $timeSlot->status }}
                                    </span>
                                </td>

                                <td>
                                    <div class="action-group">
                                        <a
                                            class="button button-small button-secondary"
                                            href="{{ route('admin.time-slots.edit', $timeSlot) }}"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('admin.time-slots.status', $timeSlot) }}"
                                        >
                                            @csrf

                                            <button
                                                class="button button-small"
                                                type="submit"
                                            >
                                                {{ $timeSlot->status === 'open'
                                                    ? 'Close'
                                                    : 'Reopen' }}
                                            </button>
                                        </form>
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
