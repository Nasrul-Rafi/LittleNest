@extends('layouts.parent', ['title' => 'Parent Management'])

@section('content')
<div class="page-header">
    <div>
        <h1>Parent Management</h1>
        <p>Search, review and manage parent accounts.</p>
    </div>
    <a class="button" href="{{ route('admin.parents.create') }}">Add Parent</a>
</div>

<section class="panel">
    <form method="GET" action="{{ route('admin.parents.index') }}">
        <div class="form-grid">
            <div class="form-group">
                <label for="search">Search</label>
                <input id="search" name="search" type="text" value="{{ $search }}" placeholder="Name, email or phone">
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
            <a class="button button-secondary" href="{{ route('admin.parents.index') }}">Clear</a>
        </div>
    </form>
</section>

<div class="panel table-wrap">
    <table>
        <thead>
            <tr>
                <th>Parent</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Children</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($parents as $parent)
                <tr>
                    <td>{{ $parent->name }}</td>
                    <td>{{ $parent->email }}</td>
                    <td>{{ $parent->phone ?: '—' }}</td>
                    <td>{{ $parent->parentProfile?->children?->count() ?? 0 }}</td>
                    <td><span class="badge badge-{{ $parent->status }}">{{ $parent->status }}</span></td>
                    <td>
                        <div class="action-group">
                            <a class="button button-secondary button-small" href="{{ route('admin.parents.show', $parent) }}">View</a>
                            <a class="button button-secondary button-small" href="{{ route('admin.parents.edit', $parent) }}">Edit</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No parents found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
