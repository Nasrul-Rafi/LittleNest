@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Demo Payment</h1>
            <p>Complete the simulated payment for Booking {{ $booking->display_reference }}.</p>
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
        <h2>Simulated Mobile Banking</h2>
        <p class="muted">
            This payment is for course demonstration only. No real money will be charged and no external payment gateway will be contacted.
        </p>

        <form method="POST" action="{{ route('payments.store', $booking) }}">
            @csrf

            <input type="hidden" name="payment_method" value="mobile-banking">

            <div class="form-group">
                <label for="mobile_number">Demo Mobile Number</label>
                <input
                    id="mobile_number"
                    name="mobile_number"
                    type="text"
                    value="{{ old('mobile_number') }}"
                    placeholder="01XXXXXXXXX"
                    maxlength="11"
                    required
                >
                @error('mobile_number')
                    <p style="margin:0;color:var(--danger);font-size:13px;">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label style="display:flex;align-items:flex-start;gap:10px;font-weight:500;">
                    <input
                        type="checkbox"
                        name="demo_confirmation"
                        style="width:auto;margin-top:3px;"
                        value="1"
                        {{ old('demo_confirmation') ? 'checked' : '' }}
                    >
                    <span>I understand that this is a simulated payment and no real money will be charged.</span>
                </label>
                @error('demo_confirmation')
                    <p style="margin:0;color:var(--danger);font-size:13px;">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-actions">
                <button class="button" type="submit">Complete Demo Payment</button>
                <a class="button button-secondary" href="{{ route('bookings.show', $booking) }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection
