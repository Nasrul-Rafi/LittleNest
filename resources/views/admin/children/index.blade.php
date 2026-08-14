@extends('layouts.parent', ['title' => 'Child Management'])
@section('content')
<div class="page-header"><div><h1>Child Management</h1><p>Review child profiles and important health information.</p></div></div>
<div class="panel table-wrap"><table><thead><tr><th>Child</th><th>Parent</th><th>Date of Birth</th><th>Health Note</th><th>Status</th><th>Action</th></tr></thead><tbody>
@forelse($children as $child)<tr><td>{{ $child->full_name }}</td><td>{{ $child->parentProfile->user->name }}</td><td>{{ $child->date_of_birth->format('d M Y') }}</td><td>{{ $child->allergies ?: ($child->medical_notes ?: 'None') }}</td><td><span class="badge badge-{{ $child->status }}">{{ $child->status }}</span></td><td><a class="button button-secondary button-small" href="{{ route('admin.children.show', $child) }}">View</a></td></tr>@empty<tr><td colspan="6">No children found.</td></tr>@endforelse
</tbody></table></div>
@endsection
