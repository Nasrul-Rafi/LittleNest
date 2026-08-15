@extends('layouts.parent', ['title' => 'Reports'])

@section('content')
<div class="page-header">
    <div>
        <h1>Reports & Summary</h1>
        <p>Review bookings, revenue, refunds, services and care activity.</p>
    </div>

    <a
        class="button"
        href="{{ route('admin.reports.bookings-csv', [
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ]) }}"
    >
        Export Bookings CSV
    </a>
</div>

<section class="panel">
    <form method="GET" action="{{ route('admin.reports.index') }}">
        <div class="form-grid">
            <div class="form-group">
                <label for="from_date">From Date</label>
                <input
                    id="from_date"
                    name="from_date"
                    type="date"
                    value="{{ $fromDate }}"
                >
            </div>

            <div class="form-group">
                <label for="to_date">To Date</label>
                <input
                    id="to_date"
                    name="to_date"
                    type="date"
                    value="{{ $toDate }}"
                >
            </div>
        </div>

        <div class="form-actions">
            <button class="button" type="submit">Apply Date Filter</button>
            <a class="button button-secondary" href="{{ route('admin.reports.index') }}">Clear</a>
        </div>
    </form>
</section>

<div class="dashboard-grid">
    <section class="panel"><h2>Total Bookings</h2><div class="stat-number">{{ $summary['total_bookings'] }}</div></section>
    <section class="panel"><h2>Completed</h2><div class="stat-number">{{ $summary['completed_bookings'] }}</div></section>
    <section class="panel"><h2>Net Paid Revenue</h2><div class="stat-number" style="font-size:32px">৳{{ number_format((float) $summary['paid_revenue']) }}</div></section>
    <section class="panel"><h2>Refunded</h2><div class="stat-number" style="font-size:32px">৳{{ number_format((float) $summary['refunded_amount']) }}</div></section>
    <section class="panel"><h2>Activity Updates</h2><div class="stat-number">{{ $summary['activity_updates'] }}</div></section>
    <section class="panel"><h2>Active Services</h2><div class="stat-number">{{ $summary['active_services'] }}</div></section>
    <section class="panel"><h2>Active Caregivers</h2><div class="stat-number">{{ $summary['active_caregivers'] }}</div></section>
</div>

<div class="panel">
    <h2>Service Usage</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Service</th><th>Bookings</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($serviceUsage as $service)
                    <tr>
                        <td>{{ $service->name }}</td>
                        <td>{{ $service->bookings_count }}</td>
                        <td><span class="badge badge-{{ $service->status }}">{{ $service->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="3">No services found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="panel">
    <h2>Recent Payments</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Payment</th><th>Parent</th><th>Service</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($recentPayments as $payment)
                    <tr>
                        <td>PAY-{{ $payment->payment_id }}</td>
                        <td>{{ $payment->booking->child->parentProfile->user->name }}</td>
                        <td>{{ $payment->booking->service->name }}</td>
                        <td>৳{{ number_format((float) $payment->amount) }}</td>
                        <td><span class="badge badge-{{ $payment->display_status }}">{{ $payment->display_status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5">No payments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
