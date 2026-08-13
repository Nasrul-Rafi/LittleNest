@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Payment #{{ $payment->payment_id }}</h1>

            <p>View submitted payment information and current status.</p>
        </div>

        <a
            class="button button-secondary"
            href="{{ route('bookings.show', $payment->booking) }}"
        >
            Back to Booking
        </a>
    </div>

    <section class="panel">
        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Booking</span>
                <p class="detail-value">#{{ $payment->booking_id }}</p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Child</span>
                <p class="detail-value">
                    {{ $payment->booking->child->full_name }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Amount</span>
                <p class="detail-value">
                    ৳{{ number_format((float) $payment->amount, 2) }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Payment Method</span>
                <p class="detail-value">
                    {{ ucwords(str_replace('-', ' ', $payment->payment_method)) }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Transaction ID</span>
                <p class="detail-value">
                    {{ $payment->transaction_id ?: 'Not required' }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Status</span>
                <p class="detail-value">
                    <span class="badge badge-{{ $payment->payment_status }}">
                        {{ $payment->payment_status }}
                    </span>
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Submitted At</span>
                <p class="detail-value">
                    {{ $payment->created_at->format('d M Y, h:i A') }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Paid At</span>
                <p class="detail-value">
                    {{ $payment->paid_at
                        ? $payment->paid_at->format('d M Y, h:i A')
                        : 'Not paid yet' }}
                </p>
            </div>
        </div>
    </section>
@endsection
