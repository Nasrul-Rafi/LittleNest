@extends('layouts.parent')

@section('content')
    <div class="page-header">
        <div>
            <h1>{{ $child->full_name }}</h1>
            <p>Child profile details.</p>
        </div>

        <div class="action-group">
            <a
                class="button button-secondary"
                href="{{ route('children.index') }}"
            >
                Back to List
            </a>

            <a
                class="button"
                href="{{ route('children.edit', $child) }}"
            >
                Edit Child
            </a>
        </div>
    </div>

    <section class="panel">
        <div class="detail-grid">
            <div class="detail-item">
                <span class="detail-label">Full Name</span>

                <p class="detail-value">
                    {{ $child->full_name }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Status</span>

                <span class="badge badge-{{ $child->status }}">
                    {{ $child->status }}
                </span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Date of Birth</span>

                <p class="detail-value">
                    {{ $child->date_of_birth->format('d F Y') }}
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Age</span>

                <p class="detail-value">
                    {{ $child->date_of_birth->age }} years
                </p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Guardian Relation</span>
                <p class="detail-value">{{ $child->guardian_relation ?: 'Not provided' }}</p>
            </div>

            <div class="detail-item">
                <span class="detail-label">Gender</span>

                <p class="detail-value">
                    {{ $child->gender
                        ? ucfirst($child->gender)
                        : 'Not provided' }}
                </p>
            </div>

            <div class="detail-item detail-item-full">
                <span class="detail-label">Allergies</span>

                <p class="detail-value">
                    {{ $child->allergies ?: 'No allergies provided.' }}
                </p>
            </div>

            <div class="detail-item detail-item-full">
                <span class="detail-label">Medical Notes</span>

                <p class="detail-value">
                    {{ $child->medical_notes
                        ?: 'No medical notes provided.' }}
                </p>
            </div>

            <div class="detail-item detail-item-full">
                <span class="detail-label">Medicine Instructions</span>
                <p class="detail-value">{{ $child->medicine_instructions ?: 'None' }}</p>
            </div>

            <div class="detail-item detail-item-full">
                <span class="detail-label">Emergency Notes</span>
                <p class="detail-value">{{ $child->emergency_notes ?: 'None' }}</p>
            </div>

            <div class="detail-item detail-item-full">
                <span class="detail-label">Special Needs</span>

                <p class="detail-value">
                    {{ $child->special_needs
                        ?: 'No special needs provided.' }}
                </p>
            </div>
        </div>

        <div class="form-actions">
            <form
                method="POST"
                action="{{ route('children.destroy', $child) }}"
                onsubmit="return confirm(
                    'Are you sure you want to delete this child profile?'
                );"
            >
                @csrf
                @method('DELETE')

                <button class="button button-danger" type="submit">
                    Delete Child
                </button>
            </form>
        </div>
    </section>
@endsection