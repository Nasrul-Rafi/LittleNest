@extends('layouts.parent', ['title' => 'Caregiver Assignments'])

@section('content')
<div class="page-header">
    <div>
        <h1>Caregiver Assignments</h1>
        <p>Review current and completed caregiver assignments.</p>
    </div>
</div>

<section class="panel">
    <form method="GET" action="{{ route('admin.assignments.index') }}">
        <div class="form-grid">
            <div class="form-group">
                <label for="caregiver_id">Caregiver</label>
                <select id="caregiver_id" name="caregiver_id">
                    <option value="">All caregivers</option>
                    @foreach($caregivers as $caregiver)
                        <option value="{{ $caregiver->id }}" @selected((string) $caregiverId === (string) $caregiver->id)>
                            {{ $caregiver->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="status">Assignment Status</label>
                <select id="status" name="status">
                    <option value="">All statuses</option>
                    <option value="assigned" @selected($status === 'assigned')>Assigned</option>
                    <option value="completed" @selected($status === 'completed')>Completed</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button class="button" type="submit">Apply Filter</button>
            <a class="button button-secondary" href="{{ route('admin.assignments.index') }}">Clear</a>
        </div>
    </form>
</section>

<div class="panel table-wrap">
    <table>
        <thead>
            <tr>
                <th>Booking</th>
                <th>Child</th>
                <th>Service</th>
                <th>Caregiver</th>
                <th>Assigned At</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assignments as $assignment)
                <tr>
                    <td>{{ $assignment->booking->display_reference }}</td>
                    <td>{{ $assignment->booking->child->full_name }}</td>
                    <td>{{ $assignment->booking->service->name }}</td>
                    <td>{{ $assignment->caregiver->name }}</td>
                    <td>{{ $assignment->assigned_at?->format('d M Y, h:i A') }}</td>
                    <td><span class="badge badge-{{ $assignment->status }}">{{ $assignment->status }}</span></td>
                    <td><a class="button button-secondary button-small" href="{{ route('admin.assignments.show', $assignment) }}">View</a></td>
                </tr>
            @empty
                <tr><td colspan="7">No caregiver assignments found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
