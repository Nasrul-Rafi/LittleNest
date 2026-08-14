@extends('layouts.parent', ['title' => 'Reports'])
@section('content')
<div class="page-header"><div><h1>Reports & Summary</h1><p>Simple operational summaries for bookings, revenue, services and care activity.</p></div><a class="button" href="{{ route('admin.reports.bookings-csv') }}">Export Bookings CSV</a></div>
<div class="dashboard-grid">
    <section class="panel"><h2>Total Bookings</h2><div class="stat-number">{{ $summary['total_bookings'] }}</div></section>
    <section class="panel"><h2>Completed</h2><div class="stat-number">{{ $summary['completed_bookings'] }}</div></section>
    <section class="panel"><h2>Paid Revenue</h2><div class="stat-number" style="font-size:32px">৳{{ number_format((float)$summary['paid_revenue']) }}</div></section>
    <section class="panel"><h2>Activity Updates</h2><div class="stat-number">{{ $summary['activity_updates'] }}</div></section>
    <section class="panel"><h2>Active Services</h2><div class="stat-number">{{ $summary['active_services'] }}</div></section>
    <section class="panel"><h2>Active Caregivers</h2><div class="stat-number">{{ $summary['active_caregivers'] }}</div></section>
</div>
<div class="panel"><h2>Service Usage</h2><div class="table-wrap"><table><thead><tr><th>Service</th><th>Bookings</th><th>Status</th></tr></thead><tbody>@forelse($serviceUsage as $service)<tr><td>{{ $service->name }}</td><td>{{ $service->bookings_count }}</td><td><span class="badge badge-{{ $service->status }}">{{ $service->status }}</span></td></tr>@empty<tr><td colspan="3">No services found.</td></tr>@endforelse</tbody></table></div></div>
<div class="panel"><h2>Recent Payments</h2><div class="table-wrap"><table><thead><tr><th>Payment</th><th>Parent</th><th>Service</th><th>Amount</th><th>Status</th></tr></thead><tbody>@forelse($recentPayments as $payment)<tr><td>PAY-{{ $payment->payment_id }}</td><td>{{ $payment->booking->child->parentProfile->user->name }}</td><td>{{ $payment->booking->service->name }}</td><td>৳{{ number_format((float)$payment->amount) }}</td><td><span class="badge badge-{{ $payment->payment_status }}">{{ $payment->payment_status }}</span></td></tr>@empty<tr><td colspan="5">No payments found.</td></tr>@endforelse</tbody></table></div></div>
@endsection
