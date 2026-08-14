@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Submit Payment</h1>

            <p>Submit payment information for Booking {{ $booking->display_reference }}.</p>
        </div>

        <a
            class="button button-secondary"
            href="{{ route('bookings.show', $booking) }}"
        >
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
                <span class="detail-label">Payment Amount</span>
                <p class="detail-value">
                    ৳{{ number_format((float) $booking->total_amount, 2) }}
                </p>
            </div>
        </div>
    </section>

    <section class="panel">
        <form method="POST" action="{{ route('payments.store', $booking) }}">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label for="payment_method">
                        Payment Method <span class="required">*</span>
                    </label>

                    <select id="payment_method" name="payment_method" required>
                        <option value="">Choose payment method</option>
                        <option value="cash" @selected(old('payment_method') === 'cash')>
                            Cash
                        </option>
                        <option value="card" @selected(old('payment_method') === 'card')>
                            Card
                        </option>
                        <option
                            value="mobile-banking"
                            @selected(old('payment_method') === 'mobile-banking')
                        >
                            Mobile Banking
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="transaction_id">Transaction ID</label>

                    <input
                        type="text"
                        id="transaction_id"
                        name="transaction_id"
                        value="{{ old('transaction_id') }}"
                        placeholder="Required for card or mobile banking"
                    >

                    <small class="muted">
                        Cash payment does not require a transaction ID.
                    </small>
                </div>
            </div>

            <div class="form-actions">
                <button class="button" type="submit">
                    Submit Payment
                </button>

                <a
                    class="button button-secondary"
                    href="{{ route('bookings.show', $booking) }}"
                >
                    Cancel
                </a>
            </div>
        </form>
    </section>
@endsection
