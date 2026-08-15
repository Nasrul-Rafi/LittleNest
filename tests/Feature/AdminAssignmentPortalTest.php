<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAssignmentPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_assignment_management(): void
    {
        [$admin, $assignment] = $this->createAssignment();

        $this->actingAs($admin)
            ->get(route('admin.assignments.index'))
            ->assertOk()
            ->assertSee($assignment->booking->display_reference)
            ->assertSee($assignment->caregiver->name);

        $this->actingAs($admin)
            ->get(route('admin.assignments.show', $assignment))
            ->assertOk()
            ->assertSee('Assignment Details')
            ->assertSee($assignment->booking->child->full_name);
    }

    public function test_parent_cannot_access_admin_assignments(): void
    {
        $parent = User::factory()->create([
            'role' => 'parent',
            'status' => 'active',
        ]);

        $this->actingAs($parent)
            ->get(route('admin.assignments.index'))
            ->assertForbidden();
    }

    private function createAssignment(): array
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $caregiver = User::factory()->create([
            'role' => 'caregiver',
            'status' => 'active',
        ]);

        $caregiver->caregiverProfile()->create([
            'qualification' => 'Child Care Diploma',
            'experience_years' => 4,
            'availability_status' => 'available',
        ]);

        $parent = User::factory()->create([
            'role' => 'parent',
            'status' => 'active',
        ]);

        $profile = $parent->parentProfile()->create();

        $child = $profile->children()->create([
            'full_name' => 'Assignment Child',
            'date_of_birth' => '2021-01-01',
            'gender' => 'female',
            'status' => 'active',
        ]);

        $service = Service::create([
            'name' => 'Assignment Service',
            'description' => 'Test service',
            'price' => 1000,
            'duration_minutes' => 120,
            'status' => 'active',
        ]);

        $booking = $child->bookings()->create([
            'service_id' => $service->service_id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '10:00',
            'status' => 'confirmed',
            'total_amount' => 1000,
        ]);

        $assignment = $booking->caregiverAssignment()->create([
            'caregiver_id' => $caregiver->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);

        $assignment->load([
            'booking.child',
            'caregiver',
        ]);

        return [$admin, $assignment];
    }
}
