@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Payment PAY-{{ $payment->payment_id }}</h1>
            <p>View payment, refund and receipt information.</p>
        </div>

        <div class="action-group">
            @if ($payment->payment_status === 'paid' || $payment->isRefunded())
                <a
                    class="button"
                    href="{{ route('payments.receipt', $payment) }}"
                >
                    View Receipt
                </a>
            @endif

            @if ($payment->gateway_name === 'sslcommerz' && $payment->payment_status === 'pending')
                <form method="POST" action="{{ route('payments.check-status', $payment) }}">
                    @csrf
                    <button class="button" type="submit">Check Payment Status</button>
                </form>
            @endif

            <a
                class="button button-secondary"
                href="{{ route('bookings.show', $payment->booking) }}"
            >
                Back to Booking
            </a>
        </div>
    </div>

    <section class="panel">
        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Booking</span>
                <p class="detail-value">{{ $payment->booking->display_reference }}</p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Child</span>
                <p class="detail-value">{{ $payment->booking->child->full_name }}</p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Service</span>
                <p class="detail-value">{{ $payment->booking->service->name }}</p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Amount</span>
                <p class="detail-value">৳{{ number_format((float) $payment->amount, 2) }}</p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Payment Method</span>
                <p class="detail-value">
                    {{ $payment->display_method }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Transaction ID</span>
                <p class="detail-value">{{ $payment->transaction_id ?: 'Not required' }}</p>
            </div>

            @if ($payment->gateway_name === 'sslcommerz')
                <div class="detail-item">
                    <span class="detail-label">Gateway Status</span>
                    <p class="detail-value">{{ $payment->gateway_status ?: 'Pending' }}</p>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Bank Transaction ID</span>
                    <p class="detail-value">{{ $payment->bank_transaction_id ?: 'Not available yet' }}</p>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Payment Channel</span>
                    <p class="detail-value">{{ $payment->card_type ?: 'Selected at SSLCOMMERZ' }}</p>
                </div>
            @endif

            <div class="detail-item">
                <span class="detail-label">Status</span>
                <p class="detail-value">
                    <span class="badge badge-{{ $payment->display_status }}">
                        {{ $payment->display_status }}
                    </span>
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Submitted At</span>
                <p class="detail-value">{{ $payment->created_at->format('d M Y, h:i A') }}</p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Paid At</span>
                <p class="detail-value">
                    {{ $payment->paid_at
                        ? $payment->paid_at->format('d M Y, h:i A')
                        : 'Not paid yet' }}
                </p>
            </div>

            @if ($payment->refund_reference)
                <div class="detail-item">
                    <span class="detail-label">Refund Reference</span>
                    <p class="detail-value">{{ $payment->refund_reference }}</p>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Refund Status</span>
                    <p class="detail-value">{{ $payment->refund_gateway_status ?: 'processing' }}</p>
                </div>
            @endif

            @if ($payment->isRefunded())
                <div class="detail-item">
                    <span class="detail-label">Refund Amount</span>
                    <p class="detail-value">
                        ৳{{ number_format((float) $payment->refund_amount, 2) }}
                    </p>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Refunded At</span>
                    <p class="detail-value">
                        {{ $payment->refunded_at->format('d M Y, h:i A') }}
                    </p>
                </div>

                <div class="detail-item detail-item-full">
                    <span class="detail-label">Refund Note</span>
                    <p class="detail-value">{{ $payment->refund_note ?: 'No note provided' }}</p>
                </div>
            @endif
        </div>
    </section>
@endsection
