@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Payment Management</h1>

            <p>Review parent payments and update their status.</p>
        </div>

        <span class="badge badge-confirmed">
            {{ $payments->count() }} total
        </span>
    </div>

    <section class="panel">
        @if ($payments->isEmpty())
            <div class="empty-state">
                <h2>No payments found</h2>
                <p>Parent payment submissions will appear here.</p>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Payment</th>
                            <th>Booking</th>
                            <th>Parent</th>
                            <th>Child</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($payments as $payment)
                            <tr>
                                <td>#{{ $payment->payment_id }}</td>
                                <td>#{{ $payment->booking_id }}</td>
                                <td>
                                    {{ $payment->booking->child->parentProfile->user->name }}
                                </td>
                                <td>{{ $payment->booking->child->full_name }}</td>
                                <td>৳{{ number_format((float) $payment->amount, 2) }}</td>
                                <td>
                                    {{ ucwords(str_replace('-', ' ', $payment->payment_method)) }}
                                </td>
                                <td>
                                    <span class="badge badge-{{ $payment->payment_status }}">
                                        {{ $payment->payment_status }}
                                    </span>
                                </td>
                                <td>
                                    <a
                                        class="button button-small button-secondary"
                                        href="{{ route('admin.payments.show', $payment) }}"
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
