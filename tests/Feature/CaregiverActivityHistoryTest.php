<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CaregiverAssignment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaregiverActivityHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_activity_history(): void
    {
        $this->get(route('caregiver.activities.index'))
            ->assertRedirect(route('login'));
    }

    public function test_parent_cannot_access_activity_history(): void
    {
        $parent = User::factory()->create(['role' => 'parent']);

        $this->actingAs($parent)
            ->get(route('caregiver.activities.index'))
            ->assertForbidden();
    }

    public function test_caregiver_sees_only_own_activity_history(): void
    {
        $caregiver = $this->createCaregiver('History Caregiver');
        $otherCaregiver = $this->createCaregiver('Other Caregiver');

        $ownAssignment = $this->createAssignment(
            $caregiver,
            'Own Activity Child'
        );

        $otherAssignment = $this->createAssignment(
            $otherCaregiver,
            'Other Activity Child'
        );

        $this->createActivity(
            $ownAssignment,
            'meal',
            'Own activity details.'
        );

        $this->createActivity(
            $otherAssignment,
            'play',
            'Other caregiver activity.'
        );

        $this->actingAs($caregiver)
            ->get(route('caregiver.activities.index'))
            ->assertOk()
            ->assertSee('Activity History')
            ->assertSee('Own Activity Child')
            ->assertSee('Own activity details.')
            ->assertDontSee('Other Activity Child')
            ->assertDontSee('Other caregiver activity.');
    }

    public function test_activity_history_is_ordered_latest_first(): void
    {
        $caregiver = $this->createCaregiver('History Caregiver');
        $assignment = $this->createAssignment(
            $caregiver,
            'Ordered Activity Child'
        );

        $this->createActivity(
            $assignment,
            'meal',
            'Older activity.',
            now()->subHours(2)
        );

        $this->createActivity(
            $assignment,
            'learning',
            'Newer activity.',
            now()->subHour()
        );

        $this->actingAs($caregiver)
            ->get(route('caregiver.activities.index'))
            ->assertOk()
            ->assertSeeInOrder([
                'Newer activity.',
                'Older activity.',
            ]);
    }

    public function test_activity_history_can_be_filtered(): void
    {
        $caregiver = $this->createCaregiver('History Caregiver');
        $assignment = $this->createAssignment(
            $caregiver,
            'Filter Activity Child'
        );

        $filterDate = now()->subDay();

        $this->createActivity(
            $assignment,
            'meal',
            'Matching meal activity.',
            $filterDate
        );

        $this->createActivity(
            $assignment,
            'play',
            'Wrong activity type.',
            $filterDate
        );

        $this->createActivity(
            $assignment,
            'meal',
            'Wrong activity date.',
            now()->subDays(2)
        );

        $this->actingAs($caregiver)
            ->get(route('caregiver.activities.index', [
                'activity_type' => 'meal',
                'activity_date' => $filterDate->format('Y-m-d'),
            ]))
            ->assertOk()
            ->assertSee('Matching meal activity.')
            ->assertDontSee('Wrong activity type.')
            ->assertDontSee('Wrong activity date.');
    }

    private function createCaregiver(string $name): User
    {
        $caregiver = User::factory()->create([
            'name' => $name,
            'role' => 'caregiver',
            'status' => 'active',
        ]);

        $caregiver->caregiverProfile()->create([
            'qualification' => 'Diploma in Child Care',
            'experience_years' => 3,
            'availability_status' => 'available',
        ]);

        return $caregiver;
    }

    private function createAssignment(
        User $caregiver,
        string $childName
    ): CaregiverAssignment {
        $parent = User::factory()->create(['role' => 'parent']);
        $parentProfile = $parent->parentProfile()->create();

        $child = $parentProfile->children()->create([
            'full_name' => $childName,
            'date_of_birth' => '2021-05-10',
            'gender' => 'female',
            'status' => 'active',
        ]);

        $service = Service::create([
            'name' => 'History Service ' . uniqid(),
            'price' => 1000,
            'duration_minutes' => 120,
            'status' => 'active',
        ]);

        $booking = $child->bookings()->create([
            'service_id' => $service->service_id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '10:00',
            'status' => 'confirmed',
            'total_amount' => $service->price,
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        return CaregiverAssignment::create([
            'booking_id' => $booking->booking_id,
            'caregiver_id' => $caregiver->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);
    }

    private function createActivity(
        CaregiverAssignment $assignment,
        string $type,
        string $details,
        $activityTime = null
    ): void {
        $assignment->activities()->create([
            'activity_type' => $type,
            'details' => $details,
            'activity_time' => $activityTime ?? now(),
        ]);
    }
}
