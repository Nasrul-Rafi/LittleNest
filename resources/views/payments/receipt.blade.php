<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt PAY-{{ $payment->payment_id }} - LittleNest</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 32px; font-family: Arial, sans-serif; color: #27332F; background: #FAF8F3; }
        .receipt { width: min(760px, 100%); margin: 0 auto; padding: 34px; background: white; border: 1px solid #DCE4E0; border-radius: 18px; }
        .header { display: flex; justify-content: space-between; gap: 20px; border-bottom: 1px solid #DCE4E0; padding-bottom: 20px; }
        .brand { color: #58756B; font-size: 28px; font-weight: 800; }
        .muted { color: #68736F; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 24px; }
        .item { padding: 14px; border: 1px solid #DCE4E0; border-radius: 10px; background: #FAFBFA; }
        .label { display: block; margin-bottom: 5px; color: #68736F; font-size: 13px; font-weight: 700; }
        .value { font-weight: 700; }
        .refund { margin-top: 22px; padding: 16px; border-radius: 12px; background: #F7EFE7; }
        .actions { width: min(760px, 100%); margin: 18px auto 0; display: flex; gap: 10px; justify-content: flex-end; }
        .button { border: 0; border-radius: 10px; padding: 11px 16px; cursor: pointer; font-weight: 700; color: white; background: #6F8F83; text-decoration: none; }
        .button.secondary { color: #58756B; background: white; border: 1px solid #6F8F83; }
        @media (max-width: 620px) { .grid { grid-template-columns: 1fr; } .header { flex-direction: column; } }
        @media print { body { padding: 0; background: white; } .receipt { border: 0; border-radius: 0; width: 100%; } .actions { display: none; } }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div>
                <div class="brand">LittleNest</div>
                <div class="muted">Child Care Service & Activity Monitoring</div>
            </div>
            <div>
                <strong>Payment Receipt</strong><br>
                <span class="muted">PAY-{{ $payment->payment_id }}</span>
            </div>
        </div>

        <div class="grid">
            <div class="item"><span class="label">Booking</span><span class="value">{{ $payment->booking->display_reference }}</span></div>
            <div class="item"><span class="label">Parent</span><span class="value">{{ $payment->booking->child->parentProfile->user->name }}</span></div>
            <div class="item"><span class="label">Child</span><span class="value">{{ $payment->booking->child->full_name }}</span></div>
            <div class="item"><span class="label">Service</span><span class="value">{{ $payment->booking->service->name }}</span></div>
            <div class="item"><span class="label">Amount</span><span class="value">৳{{ number_format((float) $payment->amount, 2) }}</span></div>
            <div class="item"><span class="label">Method</span><span class="value">{{ $payment->display_method }}</span></div>
            <div class="item"><span class="label">Transaction ID</span><span class="value">{{ $payment->transaction_id ?: 'Not required' }}</span></div>
            @if ($payment->gateway_name === 'sslcommerz')
                <div class="item"><span class="label">Bank Transaction ID</span><span class="value">{{ $payment->bank_transaction_id ?: 'Not available' }}</span></div>
                <div class="item"><span class="label">Payment Channel</span><span class="value">{{ $payment->card_type ?: 'SSLCOMMERZ' }}</span></div>
            @endif
            <div class="item"><span class="label">Paid At</span><span class="value">{{ $payment->paid_at?->format('d M Y, h:i A') ?? 'Not available' }}</span></div>
        </div>

        @if ($payment->isRefunded())
            <div class="refund">
                <strong>Refund Recorded</strong><br>
                Refund amount: ৳{{ number_format((float) $payment->refund_amount, 2) }}<br>
                Refunded at: {{ $payment->refunded_at->format('d M Y, h:i A') }}<br>
                Note: {{ $payment->refund_note ?: 'No note provided' }}
            </div>
        @endif
    </div>

    <div class="actions">
        <a class="button secondary" href="{{ route('payments.show', $payment) }}">Back</a>
        <button class="button" type="button" onclick="window.print()">Print / Save as PDF</button>
    </div>
</body>
</html>
