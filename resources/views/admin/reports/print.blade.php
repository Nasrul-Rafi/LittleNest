<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LittleNest Report</title>
    <style>
        body { margin: 0; padding: 32px; color: #27332F; font-family: Arial, sans-serif; background: #ffffff; }
        .toolbar { display: flex; justify-content: flex-end; margin-bottom: 24px; }
        .button { padding: 10px 16px; color: white; background: #6F8F83; border: 0; border-radius: 8px; cursor: pointer; }
        .header { padding-bottom: 18px; border-bottom: 2px solid #6F8F83; }
        .header h1 { margin: 0 0 6px; }
        .muted { color: #68736F; }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin: 24px 0; }
        .card { padding: 16px; border: 1px solid #DCE4E0; border-radius: 10px; }
        .value { margin-top: 8px; font-size: 24px; font-weight: 700; }
        h2 { margin-top: 28px; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 9px; border-bottom: 1px solid #DCE4E0; text-align: left; font-size: 13px; }
        th { background: #F6F7F5; }
        @media print {
            body { padding: 0; }
            .toolbar { display: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="button" type="button" onclick="window.print()">Save as PDF / Print</button>
    </div>

    <div class="header">
        <h1>LittleNest Reports &amp; Summary</h1>
        <div class="muted">
            Date range:
            {{ $fromDate ?: 'All dates' }}
            to
            {{ $toDate ?: 'All dates' }}
        </div>
    </div>

    <div class="stats">
        <div class="card"><div>Total Bookings</div><div class="value">{{ $summary['total_bookings'] }}</div></div>
        <div class="card"><div>Completed</div><div class="value">{{ $summary['completed_bookings'] }}</div></div>
        <div class="card"><div>Net Revenue</div><div class="value">৳{{ number_format((float) $summary['paid_revenue']) }}</div></div>
        <div class="card"><div>Refunded</div><div class="value">৳{{ number_format((float) $summary['refunded_amount']) }}</div></div>
        <div class="card"><div>Activity Updates</div><div class="value">{{ $summary['activity_updates'] }}</div></div>
        <div class="card"><div>Active Services</div><div class="value">{{ $summary['active_services'] }}</div></div>
        <div class="card"><div>Active Caregivers</div><div class="value">{{ $summary['active_caregivers'] }}</div></div>
        <div class="card"><div>Top Service</div><div class="value">{{ $topService?->name ?? 'No data' }}</div></div>
    </div>

    <h2>Service Usage</h2>
    <table>
        <thead><tr><th>Service</th><th>Bookings</th><th>Status</th></tr></thead>
        <tbody>
            @forelse($serviceUsage as $service)
                <tr><td>{{ $service->name }}</td><td>{{ $service->bookings_count }}</td><td>{{ ucfirst($service->status) }}</td></tr>
            @empty
                <tr><td colspan="3">No service data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Caregiver Workload</h2>
    <table>
        <thead><tr><th>Caregiver</th><th>Bookings</th></tr></thead>
        <tbody>
            @forelse($caregiverWorkload as $caregiver)
                <tr><td>{{ $caregiver->name }}</td><td>{{ $caregiver->workload_count }}</td></tr>
            @empty
                <tr><td colspan="2">No caregiver data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Recent Payments</h2>
    <table>
        <thead><tr><th>Payment</th><th>Parent</th><th>Service</th><th>Amount</th><th>Status</th></tr></thead>
        <tbody>
            @forelse($recentPayments as $payment)
                <tr>
                    <td>PAY-{{ $payment->payment_id }}</td>
                    <td>{{ $payment->booking->child->parentProfile->user->name }}</td>
                    <td>{{ $payment->booking->service->name }}</td>
                    <td>৳{{ number_format((float) $payment->amount) }}</td>
                    <td>{{ ucfirst($payment->display_status) }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No payment data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
