@extends('layouts.parent', ['title' => 'Payment History'])

@section('content')
<div class="page-header">
    <div>
        <h1>Payment History</h1>
        <p>Review payment attempts, confirmed payments and refunds.</p>
    </div>
    <span class="badge badge-confirmed">{{ $payments->count() }} records</span>
</div>

<div class="panel table-wrap">
    <table>
        <thead>
            <tr>
                <th>Payment</th>
                <th>Booking</th>
                <th>Child</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>PAY-{{ $payment->payment_id }}</td>
                    <td>{{ $payment->booking->display_reference }}</td>
                    <td>{{ $payment->booking->child->full_name }}</td>
                    <td>৳{{ number_format((float) $payment->amount, 2) }}</td>
                    <td>{{ ucfirst(str_replace('-', ' ', $payment->payment_method)) }}</td>
                    <td>
                        <span class="badge badge-{{ $payment->display_status }}">
                            {{ $payment->display_status }}
                        </span>
                    </td>
                    <td>
                        {{ $payment->refunded_at?->format('d M Y')
                            ?? $payment->paid_at?->format('d M Y')
                            ?? $payment->created_at->format('d M Y') }}
                    </td>
                    <td>
                        <a
                            class="button button-secondary button-small"
                            href="{{ route('payments.show', $payment) }}"
                        >
                            View
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No payment records yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
