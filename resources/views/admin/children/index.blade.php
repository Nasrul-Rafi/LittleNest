@extends('layouts.parent', ['title' => 'Child Management'])

@section('content')
<div class="page-header">
    <div>
        <h1>Child Management</h1>
        <p>Search child profiles and review important care information.</p>
    </div>
</div>

<section class="panel">
    <form method="GET" action="{{ route('admin.children.index') }}">
        <div class="form-grid">
            <div class="form-group">
                <label for="search">Search</label>
                <input id="search" name="search" type="text" value="{{ $search }}" placeholder="Child or parent name">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All statuses</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button class="button" type="submit">Apply Filter</button>
            <a class="button button-secondary" href="{{ route('admin.children.index') }}">Clear</a>
        </div>
    </form>
</section>

<div class="panel table-wrap">
    <table>
        <thead><tr><th>Child</th><th>Parent</th><th>Date of Birth</th><th>Health Note</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
            @forelse($children as $child)
                <tr>
                    <td>{{ $child->full_name }}</td>
                    <td>{{ $child->parentProfile->user->name }}</td>
                    <td>{{ $child->date_of_birth->format('d M Y') }}</td>
                    <td>{{ $child->allergies ?: ($child->medical_notes ?: 'None') }}</td>
                    <td><span class="badge badge-{{ $child->status }}">{{ $child->status }}</span></td>
                    <td><a class="button button-secondary button-small" href="{{ route('admin.children.show', $child) }}">View</a></td>
                </tr>
            @empty
                <tr><td colspan="6">No children found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
