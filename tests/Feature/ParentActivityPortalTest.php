<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CaregiverAssignment;
use App\Models\ChildActivity;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentActivityPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_sees_only_own_child_activities(): void
    {
        [$parentOne, $activityOne] = $this->createActivityForParent('First Child');
        [, $activityTwo] = $this->createActivityForParent('Second Child');

        $this->actingAs($parentOne)
            ->get(route('activities.index'))
            ->assertOk()
            ->assertSee($activityOne->details)
            ->assertDontSee($activityTwo->details);
    }

    public function test_parent_cannot_view_other_parent_activity_details(): void
    {
        [$parentOne] = $this->createActivityForParent('First Child');
        [, $otherActivity] = $this->createActivityForParent('Other Child');

        $this->actingAs($parentOne)
            ->get(route('activities.show', $otherActivity))
            ->assertForbidden();
    }

    public function test_parent_can_view_assigned_caregiver_profile(): void
    {
        [$parent, , $assignment] = $this->createActivityForParent('Caregiver Child');

        $this->actingAs($parent)
            ->get(route('caregivers.show', $assignment))
            ->assertOk()
            ->assertSee($assignment->caregiver->name);
    }

    private function createActivityForParent(string $childName): array
    {
        $parent = User::factory()->create(['role' => 'parent']);
        $profile = $parent->parentProfile()->create();
        $child = $profile->children()->create([
            'full_name' => $childName,
            'date_of_birth' => '2021-01-01',
            'status' => 'active',
        ]);

        $service = Service::create([
            'name' => 'Activity Service ' . uniqid(),
            'price' => 1000,
            'duration_minutes' => 120,
            'status' => 'active',
        ]);

        $booking = Booking::create([
            'child_id' => $child->child_id,
            'service_id' => $service->service_id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '10:00',
            'status' => 'confirmed',
            'total_amount' => 1000,
        ]);

        $caregiver = User::factory()->create([
            'role' => 'caregiver',
            'status' => 'active',
        ]);
        $caregiver->caregiverProfile()->create([
            'qualification' => 'Child Care Diploma',
            'experience_years' => 3,
            'availability_status' => 'available',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $assignment = CaregiverAssignment::create([
            'booking_id' => $booking->booking_id,
            'caregiver_id' => $caregiver->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);

        $activity = ChildActivity::create([
            'assignment_id' => $assignment->assignment_id,
            'activity_type' => 'learning',
            'details' => 'Private update for ' . $childName,
            'activity_time' => now(),
        ]);

        return [$parent, $activity, $assignment];
    }
}
