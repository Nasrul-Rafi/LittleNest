@extends('layouts.parent', ['title' => 'Parent Management'])
@section('content')
<div class="page-header"><div><h1>Parent Management</h1><p>Review registered parent accounts and child counts.</p></div></div>
<div class="panel table-wrap"><table><thead><tr><th>Parent</th><th>Email</th><th>Children</th><th>Status</th><th>Action</th></tr></thead><tbody>
@forelse($parents as $parent)<tr><td>{{ $parent->name }}</td><td>{{ $parent->email }}</td><td>{{ $parent->parentProfile?->children?->count() ?? 0 }}</td><td><span class="badge badge-{{ $parent->status }}">{{ $parent->status }}</span></td><td><a class="button button-secondary button-small" href="{{ route('admin.parents.show', $parent) }}">View</a></td></tr>@empty<tr><td colspan="5">No parents found.</td></tr>@endforelse
</tbody></table></div>
@endsection
