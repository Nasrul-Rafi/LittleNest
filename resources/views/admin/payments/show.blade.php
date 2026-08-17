@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Payment PAY-{{ $payment->payment_id }}</h1>
            <p>Review payment, booking and refund information.</p>
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
                <span class="detail-label">Booking Status</span>
                <p class="detail-value">
                    <span class="badge badge-{{ $payment->booking->status }}">
                        {{ $payment->booking->status }}
                    </span>
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Parent</span>
                <p class="detail-value">{{ $payment->booking->child->parentProfile->user->name }}</p>
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
                <span class="detail-label">Method</span>
                <p class="detail-value">{{ $payment->display_method }}</p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Transaction ID</span>
                <p class="detail-value">{{ $payment->transaction_id ?: 'Not required' }}</p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Payment Status</span>
                <p class="detail-value">
                    <span class="badge badge-{{ $payment->display_status }}">
                        {{ $payment->display_status }}
                    </span>
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Paid At</span>
                <p class="detail-value">{{ $payment->paid_at?->format('d M Y, h:i A') ?? 'Not paid yet' }}</p>
            </div>

            @if ($payment->isRefunded())
                <div class="detail-item">
                    <span class="detail-label">Refund Amount</span>
                    <p class="detail-value">৳{{ number_format((float) $payment->refund_amount, 2) }}</p>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Refunded At</span>
                    <p class="detail-value">{{ $payment->refunded_at->format('d M Y, h:i A') }}</p>
                </div>

                <div class="detail-item detail-item-full">
                    <span class="detail-label">Refund Note</span>
                    <p class="detail-value">{{ $payment->refund_note ?: 'No note provided' }}</p>
                </div>
            @endif
        </div>

        @if ($payment->payment_status === 'pending')
            <div class="form-actions">
                <form
                    method="POST"
                    action="{{ route('admin.payments.mark-paid', $payment) }}"
                    onsubmit="return confirm('Mark this payment as paid?');"
                >
                    @csrf
                    <button class="button" type="submit">Mark as Paid</button>
                </form>

                <form
                    method="POST"
                    action="{{ route('admin.payments.mark-failed', $payment) }}"
                    onsubmit="return confirm('Mark this payment as failed?');"
                >
                    @csrf
                    <button class="button button-danger" type="submit">Mark as Failed</button>
                </form>
            </div>
        @endif

        @if (
            $payment->payment_status === 'paid'
            && !$payment->isRefunded()
            && $payment->booking->status === 'cancelled'
        )
            <div style="margin-top:24px; padding-top:20px; border-top:1px solid var(--border);">
                <h2>Record Refund</h2>
                <p class="muted">
                    This booking is cancelled. Record the full payment refund below.
                </p>

                <form method="POST" action="{{ route('admin.payments.refund', $payment) }}">
                    @csrf

                    <div class="form-group">
                        <label for="refund_note">Refund Note <span class="required">*</span></label>
                        <textarea
                            id="refund_note"
                            name="refund_note"
                            required
                            placeholder="Example: Cancellation approved and full refund recorded."
                        >{{ old('refund_note') }}</textarea>
                    </div>

                    <div class="form-actions">
                        <button
                            class="button button-danger"
                            type="submit"
                            onclick="return confirm('Record a full refund for this payment?');"
                        >
                            Record Full Refund
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </section>
@endsection
