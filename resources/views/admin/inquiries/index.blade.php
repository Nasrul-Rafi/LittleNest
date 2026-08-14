@extends('layouts.parent', ['title' => 'Contact Inquiries'])
@section('content')
<div class="page-header"><div><h1>Contact Inquiries</h1><p>Review messages submitted from the public Contact page.</p></div></div>
<div class="panel"><form method="GET" class="action-group"><select name="status" style="max-width:220px"><option value="">All statuses</option>@foreach(['new','open','resolved'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select><button class="button">Filter</button><a class="button button-secondary" href="{{ route('admin.inquiries.index') }}">Clear</a></form></div>
<div class="panel table-wrap"><table><thead><tr><th>Name</th><th>Subject</th><th>Email</th><th>Status</th><th>Received</th><th>Action</th></tr></thead><tbody>
@forelse($messages as $message)<tr><td>{{ $message->full_name }}</td><td>{{ $message->subject }}</td><td>{{ $message->email }}</td><td><span class="badge badge-{{ $message->status === 'resolved' ? 'completed' : 'pending' }}">{{ $message->status }}</span></td><td>{{ $message->created_at->format('d M Y') }}</td><td><a class="button button-secondary button-small" href="{{ route('admin.inquiries.show', $message) }}">View</a></td></tr>@empty<tr><td colspan="6">No inquiries found.</td></tr>@endforelse
</tbody></table></div>
@endsection
