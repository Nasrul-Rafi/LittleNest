<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CaregiverAssignment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingCompletionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_caregiver_can_complete_own_confirmed_assignment(): void
    {
        [$parent, $caregiver, $booking, $assignment] =
            $this->createAssignment();

        $this->createActivity($assignment);

        $this->actingAs($caregiver)
            ->post(route('caregiver.assignments.complete', $assignment))
            ->assertRedirect(
                route('caregiver.assignments.show', $assignment)
            )
            ->assertSessionHas('success');

        $this->assertDatabaseHas('caregiver_assignments', [
            'assignment_id' => $assignment->assignment_id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('bookings', [
            'booking_id' => $booking->booking_id,
            'status' => 'completed',
        ]);
    }

    public function test_other_caregiver_cannot_complete_assignment(): void
    {
        [, , $booking, $assignment] = $this->createAssignment();
        $otherCaregiver = $this->createCaregiver('Other Caregiver');

        $this->createActivity($assignment);

        $this->actingAs($otherCaregiver)
            ->post(route('caregiver.assignments.complete', $assignment))
            ->assertForbidden();

        $this->assertDatabaseHas('bookings', [
            'booking_id' => $booking->booking_id,
            'status' => 'confirmed',
        ]);
    }

    public function test_assignment_without_activity_cannot_be_completed(): void
    {
        [, $caregiver, $booking, $assignment] =
            $this->createAssignment();

        $this->actingAs($caregiver)
            ->post(route('caregiver.assignments.complete', $assignment))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('bookings', [
            'booking_id' => $booking->booking_id,
            'status' => 'confirmed',
        ]);
    }

    public function test_cancelled_booking_cannot_be_completed(): void
    {
        [, $caregiver, $booking, $assignment] =
            $this->createAssignment('cancelled');

        $this->createActivity($assignment);

        $this->actingAs($caregiver)
            ->post(route('caregiver.assignments.complete', $assignment))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('bookings', [
            'booking_id' => $booking->booking_id,
            'status' => 'cancelled',
        ]);
    }

    public function test_activity_cannot_be_added_after_completion(): void
    {
        [, $caregiver, , $assignment] = $this->createAssignment();
        $this->createActivity($assignment);

        $this->actingAs($caregiver)
            ->post(route('caregiver.assignments.complete', $assignment));

        $this->actingAs($caregiver)
            ->post(route('caregiver.activities.store', $assignment), [
                'activity_type' => 'play',
                'details' => 'This activity should not be saved.',
                'activity_time' => now()->format('Y-m-d H:i:s'),
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('child_activities', 1);
    }

    public function test_parent_sees_completed_booking_status(): void
    {
        [$parent, $caregiver, $booking, $assignment] =
            $this->createAssignment();

        $this->createActivity($assignment);

        $this->actingAs($caregiver)
            ->post(route('caregiver.assignments.complete', $assignment));

        $this->actingAs($parent)
            ->get(route('bookings.show', $booking))
            ->assertOk()
            ->assertSee('completed');
    }

    private function createAssignment(
        string $bookingStatus = 'confirmed'
    ): array {
        $parent = User::factory()->create(['role' => 'parent']);
        $parentProfile = $parent->parentProfile()->create();

        $child = $parentProfile->children()->create([
            'full_name' => 'Completion Test Child',
            'date_of_birth' => '2021-05-10',
            'gender' => 'female',
            'status' => 'active',
        ]);

        $service = Service::create([
            'name' => 'Completion Test Service ' . uniqid(),
            'price' => 1200,
            'duration_minutes' => 120,
            'status' => 'active',
        ]);

        $booking = $child->bookings()->create([
            'service_id' => $service->service_id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '10:00',
            'status' => $bookingStatus,
            'total_amount' => $service->price,
        ]);

        $caregiver = $this->createCaregiver('Assigned Caregiver');
        $admin = User::factory()->create(['role' => 'admin']);

        $assignment = CaregiverAssignment::create([
            'booking_id' => $booking->booking_id,
            'caregiver_id' => $caregiver->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);

        return [$parent, $caregiver, $booking, $assignment];
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

    private function createActivity(
        CaregiverAssignment $assignment
    ): void {
        $assignment->activities()->create([
            'activity_type' => 'check-in',
            'details' => 'Care started successfully.',
            'activity_time' => now()->subHour(),
        ]);
    }
}
