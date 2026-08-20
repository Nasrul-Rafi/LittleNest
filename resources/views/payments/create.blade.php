@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Pay with SSLCOMMERZ</h1>
            <p>Complete the sandbox payment for Booking {{ $booking->display_reference }}.</p>
        </div>

        <a class="button button-secondary" href="{{ route('bookings.show', $booking) }}">
            Back to Booking
        </a>
    </div>

    <section class="panel">
        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Child</span>
                <p class="detail-value">{{ $booking->child->full_name }}</p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Service</span>
                <p class="detail-value">{{ $booking->service->name }}</p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Booking</span>
                <p class="detail-value">{{ $booking->display_reference }}</p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Amount</span>
                <p class="detail-value">৳{{ number_format((float) $booking->total_amount, 2) }}</p>
            </div>
        </div>
    </section>

    <section class="panel">
        <h2>SSLCOMMERZ Sandbox</h2>
        <p class="muted">
            You will be redirected to the SSLCOMMERZ sandbox checkout page. Use sandbox payment details only. No real money will be charged.
        </p>

        <form method="POST" action="{{ route('payments.store', $booking) }}">
            @csrf

            <div class="form-group">
                <label for="customer_phone">Customer Mobile Number</label>
                <input
                    id="customer_phone"
                    name="customer_phone"
                    type="text"
                    value="{{ old('customer_phone', auth()->user()->phone) }}"
                    placeholder="01XXXXXXXXX"
                    maxlength="11"
                    required
                >
                @error('customer_phone')
                    <p style="margin:0;color:var(--danger);font-size:13px;">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-actions">
                <button class="button" type="submit">Continue to SSLCOMMERZ</button>
                <a class="button button-secondary" href="{{ route('bookings.show', $booking) }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection
