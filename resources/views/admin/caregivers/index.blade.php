@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>Caregiver Management</h1>

            <p>Create caregiver accounts and manage their status.</p>
        </div>

        <a class="button" href="{{ route('admin.caregivers.create') }}">
            Add Caregiver
        </a>
    </div>

    <section class="panel">
        @if ($caregivers->isEmpty())
            <div class="empty-state">
                <h2>No caregivers found</h2>

                <p>Add the first caregiver account to get started.</p>

                <a class="button" href="{{ route('admin.caregivers.create') }}">
                    Add Caregiver
                </a>
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
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($caregivers as $caregiver)
                            <tr>
                                <td><strong>{{ $caregiver->name }}</strong></td>
                                <td>{{ $caregiver->email }}</td>

                                <td>
                                    {{ $caregiver->caregiverProfile->specialization ?: 'Not provided' }}
                                </td>

                                <td>
                                    {{ $caregiver->caregiverProfile->experience_years }} years
                                </td>

                                <td>
                                    <span class="badge badge-{{ $caregiver->caregiverProfile->availability_status }}">
                                        {{ $caregiver->caregiverProfile->availability_status }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge badge-{{ $caregiver->status }}">
                                        {{ $caregiver->status }}
                                    </span>
                                </td>

                                <td>
                                    <div class="action-group">
                                        <a
                                            class="button button-small button-secondary"
                                            href="{{ route('admin.caregivers.show', $caregiver) }}"
                                        >
                                            View
                                        </a>

                                        <a
                                            class="button button-small"
                                            href="{{ route('admin.caregivers.edit', $caregiver) }}"
                                        >
                                            Edit
                                        </a>
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
