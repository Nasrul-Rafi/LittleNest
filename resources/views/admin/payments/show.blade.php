@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Payment #{{ $payment->payment_id }}</h1>

            <p>Review payment and booking information.</p>
        </div>

        <a
            class="button button-secondary"
            href="{{ route('admin.payments.index') }}"
        >
            Back to Payments
        </a>
    </div>

    <section class="panel">
        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Booking</span>
                <p class="detail-value">{{ $payment->booking->display_reference }}</p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Parent</span>
                <p class="detail-value">
                    {{ $payment->booking->child->parentProfile->user->name }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Child</span>
                <p class="detail-value">
                    {{ $payment->booking->child->full_name }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Service</span>
                <p class="detail-value">
                    {{ $payment->booking->service->name }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Amount</span>
                <p class="detail-value">
                    ৳{{ number_format((float) $payment->amount, 2) }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Method</span>
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
                <span class="detail-label">Payment Status</span>
                <p class="detail-value">
                    <span class="badge badge-{{ $payment->payment_status }}">
                        {{ $payment->payment_status }}
                    </span>
                </p>
            </div>
        </div>

        @if ($payment->payment_status === 'pending')
            <div class="form-actions">
                <form
                    method="POST"
                    action="{{ route('admin.payments.mark-paid', $payment) }}"
                    onsubmit="return confirm('Mark this payment as paid?');"
                >
                    @csrf

                    <button class="button" type="submit">
                        Mark as Paid
                    </button>
                </form>

                <form
                    method="POST"
                    action="{{ route('admin.payments.mark-failed', $payment) }}"
                    onsubmit="return confirm('Mark this payment as failed?');"
                >
                    @csrf

                    <button class="button button-danger" type="submit">
                        Mark as Failed
                    </button>
                </form>
            </div>
        @endif
    </section>
@endsection
