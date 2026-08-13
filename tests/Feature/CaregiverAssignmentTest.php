<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CaregiverAssignment;
use App\Models\Child;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaregiverAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }

    private function createCaregiver(
        string $name = 'Test Caregiver',
        string $accountStatus = 'active',
        string $availabilityStatus = 'available'
    ): User {
        $caregiver = User::factory()->create([
            'name' => $name,
            'role' => 'caregiver',
            'status' => $accountStatus,
        ]);

        $caregiver->caregiverProfile()->create([
            'qualification' => 'Diploma in Child Care',
            'experience_years' => 3,
            'specialization' => 'Toddler Care',
            'availability_status' => $availabilityStatus,
        ]);

        return $caregiver;
    }

    private function createParentAndChild(
        string $childName = 'Test Child'
    ): array {
        $parent = User::factory()->create([
            'role' => 'parent',
        ]);

        $parentProfile = $parent->parentProfile()->create();

        $child = $parentProfile->children()->create([
            'full_name' => $childName,
            'date_of_birth' => '2021-05-10',
            'gender' => 'female',
            'status' => 'active',
        ]);

        return [$parent, $child];
    }

    private function createBooking(
        Child $child,
        string $status = 'confirmed'
    ): Booking {
        $service = Service::create([
            'name' => 'Day Care',
            'description' => 'Child care service',
            'price' => 1000,
            'duration_minutes' => 120,
            'status' => 'active',
        ]);

        return $child->bookings()->create([
            'service_id' => $service->service_id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '10:00',
            'status' => $status,
            'total_amount' => 1000,
        ]);
    }

    public function test_admin_can_assign_available_caregiver(): void
    {
        $admin = $this->createAdmin();
        $caregiver = $this->createCaregiver('Sara Caregiver');
        [, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);

        $response = $this
            ->actingAs($admin)
            ->post(
                route('admin.bookings.assign-caregiver', $booking),
                ['caregiver_id' => $caregiver->id]
            );

        $response->assertRedirect(
            route('admin.bookings.show', $booking)
        );

        $this->assertDatabaseHas('caregiver_assignments', [
            'booking_id' => $booking->booking_id,
            'caregiver_id' => $caregiver->id,
            'assigned_by' => $admin->id,
            'status' => 'assigned',
        ]);
    }

    public function test_pending_booking_cannot_receive_caregiver(): void
    {
        $admin = $this->createAdmin();
        $caregiver = $this->createCaregiver();
        [, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child, 'pending');

        $this->actingAs($admin)
            ->post(
                route('admin.bookings.assign-caregiver', $booking),
                ['caregiver_id' => $caregiver->id]
            )
            ->assertSessionHas('error');

        $this->assertDatabaseCount('caregiver_assignments', 0);
    }

    public function test_unavailable_caregiver_cannot_be_assigned(): void
    {
        $admin = $this->createAdmin();
        $caregiver = $this->createCaregiver(
            'Unavailable Caregiver',
            'active',
            'unavailable'
        );
        [, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);

        $this->actingAs($admin)
            ->post(
                route('admin.bookings.assign-caregiver', $booking),
                ['caregiver_id' => $caregiver->id]
            )
            ->assertSessionHas('error');

        $this->assertDatabaseCount('caregiver_assignments', 0);
    }

    public function test_caregiver_sees_only_own_assignments(): void
    {
        $admin = $this->createAdmin();
        $firstCaregiver = $this->createCaregiver('First Caregiver');
        $secondCaregiver = $this->createCaregiver('Second Caregiver');

        [, $firstChild] = $this->createParentAndChild('Own Assigned Child');
        [, $secondChild] = $this->createParentAndChild('Other Assigned Child');

        $firstBooking = $this->createBooking($firstChild);
        $secondBooking = $this->createBooking($secondChild);

        CaregiverAssignment::create([
            'booking_id' => $firstBooking->booking_id,
            'caregiver_id' => $firstCaregiver->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);

        CaregiverAssignment::create([
            'booking_id' => $secondBooking->booking_id,
            'caregiver_id' => $secondCaregiver->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);

        $this->actingAs($firstCaregiver)
            ->get(route('caregiver.assignments.index'))
            ->assertOk()
            ->assertSee('Own Assigned Child')
            ->assertDontSee('Other Assigned Child');
    }

    public function test_caregiver_cannot_view_another_caregivers_assignment(): void
    {
        $admin = $this->createAdmin();
        $firstCaregiver = $this->createCaregiver('First Caregiver');
        $secondCaregiver = $this->createCaregiver('Second Caregiver');
        [, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);

        $assignment = CaregiverAssignment::create([
            'booking_id' => $booking->booking_id,
            'caregiver_id' => $secondCaregiver->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);

        $this->actingAs($firstCaregiver)
            ->get(route('caregiver.assignments.show', $assignment))
            ->assertForbidden();
    }

    public function test_parent_sees_assigned_caregiver(): void
    {
        $admin = $this->createAdmin();
        $caregiver = $this->createCaregiver('Visible Caregiver');
        [$parent, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);

        CaregiverAssignment::create([
            'booking_id' => $booking->booking_id,
            'caregiver_id' => $caregiver->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);

        $this->actingAs($parent)
            ->get(route('bookings.show', $booking))
            ->assertOk()
            ->assertSee('Visible Caregiver');
    }

    public function test_caregiver_cannot_be_changed_after_activity_exists(): void
    {
        $admin = $this->createAdmin();
        $firstCaregiver = $this->createCaregiver('First Caregiver');
        $secondCaregiver = $this->createCaregiver('Second Caregiver');
        [, $child] = $this->createParentAndChild();
        $booking = $this->createBooking($child);

        $assignment = CaregiverAssignment::create([
            'booking_id' => $booking->booking_id,
            'caregiver_id' => $firstCaregiver->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);

        $assignment->activities()->create([
            'activity_type' => 'meal',
            'details' => 'Lunch update',
            'activity_time' => now(),
        ]);

        $this->actingAs($admin)
            ->post(
                route('admin.bookings.assign-caregiver', $booking),
                ['caregiver_id' => $secondCaregiver->id]
            )
            ->assertSessionHas('error');

        $this->assertDatabaseHas('caregiver_assignments', [
            'assignment_id' => $assignment->assignment_id,
            'caregiver_id' => $firstCaregiver->id,
        ]);
    }
}
