<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CaregiverAssignment;
use App\Models\Child;
use App\Models\ChildActivity;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaregiverActivityUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function createCaregiver(string $name): User
    {
        $caregiver = User::factory()->create([
            'name' => $name,
            'role' => 'caregiver',
            'status' => 'active',
        ]);

        $caregiver->caregiverProfile()->create([
            'qualification' => 'Diploma in Child Care',
            'experience_years' => 2,
            'availability_status' => 'available',
        ]);

        return $caregiver;
    }

    private function createParentAndChild(): array
    {
        $parent = User::factory()->create([
            'role' => 'parent',
        ]);

        $parentProfile = $parent->parentProfile()->create();

        $child = $parentProfile->children()->create([
            'full_name' => 'Activity Child',
            'date_of_birth' => '2021-04-15',
            'gender' => 'male',
            'status' => 'active',
        ]);

        return [$parent, $child];
    }

    private function createBooking(
        Child $child,
        string $status = 'confirmed'
    ): Booking {
        $service = Service::create([
            'name' => 'Learning Care',
            'price' => 900,
            'duration_minutes' => 90,
            'status' => 'active',
        ]);

        return $child->bookings()->create([
            'service_id' => $service->service_id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '11:00',
            'status' => $status,
            'total_amount' => 900,
        ]);
    }

    private function createAssignment(
        User $caregiver,
        Booking $booking
    ): CaregiverAssignment {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        return CaregiverAssignment::create([
            'booking_id' => $booking->booking_id,
            'caregiver_id' => $caregiver->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);
    }

    private function createActivity(
        CaregiverAssignment $assignment
    ): ChildActivity {
        return $assignment->activities()->create([
            'activity_type' => 'meal',
            'details' => 'The child finished lunch.',
            'activity_time' => now()->subMinutes(20),
        ]);
    }

    public function test_assigned_caregiver_can_add_activity(): void
    {
        $caregiver = $this->createCaregiver('Assigned Caregiver');
        [, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);
        $assignment = $this->createAssignment($caregiver, $booking);

        $response = $this
            ->actingAs($caregiver)
            ->post(route('caregiver.activities.store', $assignment), [
                'activity_type' => 'meal',
                'details' => 'The child finished breakfast.',
                'activity_time' => now()->subMinutes(10)->format('Y-m-d H:i:s'),
            ]);

        $response->assertRedirect(
            route('caregiver.assignments.show', $assignment)
        );

        $this->assertDatabaseHas('child_activities', [
            'assignment_id' => $assignment->assignment_id,
            'activity_type' => 'meal',
            'details' => 'The child finished breakfast.',
        ]);
    }

    public function test_activity_information_is_validated(): void
    {
        $caregiver = $this->createCaregiver('Assigned Caregiver');
        [, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);
        $assignment = $this->createAssignment($caregiver, $booking);

        $this->actingAs($caregiver)
            ->post(route('caregiver.activities.store', $assignment), [
                'activity_type' => 'invalid-type',
                'activity_time' => now()->addDay()->format('Y-m-d H:i:s'),
            ])
            ->assertSessionHasErrors([
                'activity_type',
                'activity_time',
            ]);

        $this->assertDatabaseCount('child_activities', 0);
    }

    public function test_other_caregiver_cannot_add_activity(): void
    {
        $assignedCaregiver = $this->createCaregiver('Assigned Caregiver');
        $otherCaregiver = $this->createCaregiver('Other Caregiver');
        [, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);
        $assignment = $this->createAssignment($assignedCaregiver, $booking);

        $this->actingAs($otherCaregiver)
            ->post(route('caregiver.activities.store', $assignment), [
                'activity_type' => 'play',
                'details' => 'Unauthorized update.',
                'activity_time' => now()->format('Y-m-d H:i:s'),
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('child_activities', 0);
    }

    public function test_assigned_caregiver_can_edit_activity(): void
    {
        $caregiver = $this->createCaregiver('Assigned Caregiver');
        [, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);
        $assignment = $this->createAssignment($caregiver, $booking);
        $activity = $this->createActivity($assignment);

        $response = $this
            ->actingAs($caregiver)
            ->post(route('caregiver.activities.update', $activity), [
                'activity_type' => 'learning',
                'details' => 'The child completed a drawing activity.',
                'activity_time' => now()->subMinutes(5)->format('Y-m-d H:i:s'),
            ]);

        $response->assertRedirect(
            route('caregiver.assignments.show', $assignment)
        );

        $this->assertDatabaseHas('child_activities', [
            'activity_id' => $activity->activity_id,
            'activity_type' => 'learning',
            'details' => 'The child completed a drawing activity.',
        ]);
    }

    public function test_other_caregiver_cannot_edit_activity(): void
    {
        $assignedCaregiver = $this->createCaregiver('Assigned Caregiver');
        $otherCaregiver = $this->createCaregiver('Other Caregiver');
        [, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);
        $assignment = $this->createAssignment($assignedCaregiver, $booking);
        $activity = $this->createActivity($assignment);

        $this->actingAs($otherCaregiver)
            ->post(route('caregiver.activities.update', $activity), [
                'activity_type' => 'play',
                'details' => 'Unauthorized change.',
                'activity_time' => now()->format('Y-m-d H:i:s'),
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('child_activities', [
            'activity_id' => $activity->activity_id,
            'activity_type' => 'meal',
            'details' => 'The child finished lunch.',
        ]);
    }

    public function test_parent_sees_activity_in_booking_timeline(): void
    {
        $caregiver = $this->createCaregiver('Assigned Caregiver');
        [$parent, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);
        $assignment = $this->createAssignment($caregiver, $booking);
        $this->createActivity($assignment);

        $this->actingAs($parent)
            ->get(route('bookings.show', $booking))
            ->assertOk()
            ->assertSee('Child Activity Timeline')
            ->assertSee('The child finished lunch.');
    }

    public function test_activity_cannot_be_added_to_cancelled_booking(): void
    {
        $caregiver = $this->createCaregiver('Assigned Caregiver');
        [, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child, 'cancelled');
        $assignment = $this->createAssignment($caregiver, $booking);

        $this->actingAs($caregiver)
            ->post(route('caregiver.activities.store', $assignment), [
                'activity_type' => 'meal',
                'details' => 'Should not be saved.',
                'activity_time' => now()->format('Y-m-d H:i:s'),
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('child_activities', 0);
    }
}
