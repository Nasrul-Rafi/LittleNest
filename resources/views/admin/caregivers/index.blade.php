@extends('layouts.parent', ['title' => 'Caregiver Management'])

@section('content')
<div class="page-header">
    <div>
        <h1>Caregiver Management</h1>
        <p>Search caregivers, review availability and manage accounts.</p>
    </div>
    <a class="button" href="{{ route('admin.caregivers.create') }}">Add Caregiver</a>
</div>

<section class="panel">
    <form method="GET" action="{{ route('admin.caregivers.index') }}">
        <div class="form-grid">
            <div class="form-group">
                <label for="search">Search</label>
                <input id="search" name="search" type="text" value="{{ $search }}" placeholder="Name, email or phone">
            </div>
            <div class="form-group">
                <label for="status">Account Status</label>
                <select id="status" name="status">
                    <option value="">All statuses</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="form-group">
                <label for="availability">Availability</label>
                <select id="availability" name="availability">
                    <option value="">All availability</option>
                    <option value="available" @selected($availability === 'available')>Available</option>
                    <option value="unavailable" @selected($availability === 'unavailable')>Unavailable</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button class="button" type="submit">Apply Filter</button>
            <a class="button button-secondary" href="{{ route('admin.caregivers.index') }}">Clear</a>
        </div>
    </form>
</section>

<section class="panel">
    @if ($caregivers->isEmpty())
        <div class="empty-state">
            <h2>No caregivers found</h2>
            <p>Try another filter or add a new caregiver account.</p>
            <a class="button" href="{{ route('admin.caregivers.create') }}">Add Caregiver</a>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Specialization</th>
                        <th>Experience</th>
                        <th>Availability</th>
                        <th>Account</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($caregivers as $caregiver)
                        <tr>
                            <td>{{ $caregiver->name }}</td>
                            <td>{{ $caregiver->email }}</td>
                            <td>{{ $caregiver->caregiverProfile->specialization ?: 'Not provided' }}</td>
                            <td>{{ $caregiver->caregiverProfile->experience_years }} years</td>
                            <td><span class="badge badge-{{ $caregiver->caregiverProfile->availability_status }}">{{ $caregiver->caregiverProfile->availability_status }}</span></td>
                            <td><span class="badge badge-{{ $caregiver->status }}">{{ $caregiver->status }}</span></td>
                            <td>
                                <div class="action-group">
                                    <a class="button button-secondary button-small" href="{{ route('admin.caregivers.show', $caregiver) }}">View</a>
                                    <a class="button button-secondary button-small" href="{{ route('admin.caregivers.edit', $caregiver) }}">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
