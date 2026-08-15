@extends('layouts.parent', ['title' => 'Parent Details'])

@section('content')
<div class="page-header">
    <div>
        <h1>{{ $parent->name }}</h1>
        <p>Parent account, contact and child information.</p>
    </div>
    <div class="action-group">
        <a class="button" href="{{ route('admin.parents.edit', $parent) }}">Edit Parent</a>
        <a class="button button-secondary" href="{{ route('admin.parents.index') }}">Back</a>
    </div>
</div>

<div class="panel">
    <div class="detail-grid">
        <div class="detail-item"><span class="detail-label">Email</span><p class="detail-value">{{ $parent->email }}</p></div>
        <div class="detail-item"><span class="detail-label">Phone</span><p class="detail-value">{{ $parent->phone ?: 'Not provided' }}</p></div>
        <div class="detail-item"><span class="detail-label">Status</span><p class="detail-value"><span class="badge badge-{{ $parent->status }}">{{ $parent->status }}</span></p></div>
        <div class="detail-item"><span class="detail-label">Address</span><p class="detail-value">{{ $parent->parentProfile?->address ?: 'Not provided' }}</p></div>
        <div class="detail-item detail-item-full"><span class="detail-label">Emergency Contact</span><p class="detail-value">{{ $parent->parentProfile?->emergency_contact_name ?: 'Not provided' }} {{ $parent->parentProfile?->emergency_contact_phone ? '· '.$parent->parentProfile->emergency_contact_phone : '' }}</p></div>
    </div>

    <div class="form-actions">
        <form method="POST" action="{{ route('admin.parents.status', $parent) }}">
            @csrf
            <button class="button {{ $parent->status === 'active' ? 'button-danger' : '' }}" type="submit">
                {{ $parent->status === 'active' ? 'Deactivate Parent' : 'Activate Parent' }}
            </button>
        </form>
    </div>
</div>

<div class="panel">
    <h2>Children</h2>
    @forelse($parent->parentProfile?->children ?? [] as $child)
        <p>
            <a href="{{ route('admin.children.show', $child) }}">{{ $child->full_name }}</a>
            · {{ ucfirst($child->status) }}
        </p>
    @empty
        <p class="muted">No children added.</p>
    @endforelse
</div>
@endsection
