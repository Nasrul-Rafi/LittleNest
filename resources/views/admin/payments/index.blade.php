@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Payment Management</h1>
            <p>Search payments, review status and manage refunds.</p>
        </div>

        <span class="badge badge-confirmed">
            {{ $payments->count() }} results
        </span>
    </div>

    <section class="panel">
        <form method="GET" action="{{ route('admin.payments.index') }}">
            <div class="form-grid">
                <div class="form-group">
                    <label for="search">Search</label>
                    <input
                        id="search"
                        name="search"
                        type="text"
                        value="{{ $search }}"
                        placeholder="Booking ref, parent, child or transaction ID"
                    >
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All statuses</option>
                        <option value="pending" @selected($status === 'pending')>Pending</option>
                        <option value="paid" @selected($status === 'paid')>Paid</option>
                        <option value="failed" @selected($status === 'failed')>Failed</option>
                        <option value="refunded" @selected($status === 'refunded')>Refunded</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button class="button" type="submit">Apply Filters</button>
                <a class="button button-secondary" href="{{ route('admin.payments.index') }}">Clear</a>
            </div>
        </form>
    </section>

    <section class="panel">
        @if ($payments->isEmpty())
            <div class="empty-state">
                <h2>No payments found</h2>
                <p>Try changing the search or status filter.</p>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Payment</th>
                            <th>Booking</th>
                            <th>Parent</th>
                            <th>Child</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($payments as $payment)
                            <tr>
                                <td>PAY-{{ $payment->payment_id }}</td>
                                <td>{{ $payment->booking->display_reference }}</td>
                                <td>{{ $payment->booking->child->parentProfile->user->name }}</td>
                                <td>{{ $payment->booking->child->full_name }}</td>
                                <td>৳{{ number_format((float) $payment->amount, 2) }}</td>
                                <td>{{ ucwords(str_replace('-', ' ', $payment->payment_method)) }}</td>
                                <td>
                                    <span class="badge badge-{{ $payment->display_status }}">
                                        {{ $payment->display_status }}
                                    </span>
                                </td>
                                <td>
                                    <a
                                        class="button button-small button-secondary"
                                        href="{{ route('admin.payments.show', $payment) }}"
                                    >
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
