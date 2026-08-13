<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CaregiverAssignment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaregiverScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_caregiver_schedule(): void
    {
        $this->get(route('caregiver.schedule.index'))
            ->assertRedirect(route('login'));
    }

    public function test_parent_cannot_access_caregiver_schedule(): void
    {
        $parent = User::factory()->create(['role' => 'parent']);

        $this->actingAs($parent)
            ->get(route('caregiver.schedule.index'))
            ->assertForbidden();
    }

    public function test_caregiver_sees_only_own_upcoming_confirmed_schedule(): void
    {
        $caregiver = $this->createCaregiver('Schedule Caregiver');
        $otherCaregiver = $this->createCaregiver('Other Caregiver');

        $visibleBooking = $this->createBooking(
            'Visible Schedule Child',
            'confirmed',
            now()->addDays(2)->format('Y-m-d'),
            '10:00'
        );

        $cancelledBooking = $this->createBooking(
            'Cancelled Schedule Child',
            'cancelled',
            now()->addDays(3)->format('Y-m-d'),
            '11:00'
        );

        $pastBooking = $this->createBooking(
            'Past Schedule Child',
            'confirmed',
            now()->subDay()->format('Y-m-d'),
            '12:00'
        );

        $otherBooking = $this->createBooking(
            'Other Caregiver Child',
            'confirmed',
            now()->addDays(4)->format('Y-m-d'),
            '13:00'
        );

        $this->createAssignment($caregiver, $visibleBooking);
        $this->createAssignment($caregiver, $cancelledBooking);
        $this->createAssignment($caregiver, $pastBooking);
        $this->createAssignment($otherCaregiver, $otherBooking);

        $this->actingAs($caregiver)
            ->get(route('caregiver.schedule.index'))
            ->assertOk()
            ->assertSee('My Schedule')
            ->assertSee('Visible Schedule Child')
            ->assertDontSee('Cancelled Schedule Child')
            ->assertDontSee('Past Schedule Child')
            ->assertDontSee('Other Caregiver Child');
    }

    public function test_inactive_caregiver_cannot_access_schedule(): void
    {
        $caregiver = $this->createCaregiver('Inactive Caregiver');
        $caregiver->update(['status' => 'inactive']);

        $this->actingAs($caregiver)
            ->get(route('caregiver.schedule.index'))
            ->assertForbidden();
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

    private function createBooking(
        string $childName,
        string $status,
        string $date,
        string $time
    ): Booking {
        $parent = User::factory()->create(['role' => 'parent']);
        $parentProfile = $parent->parentProfile()->create();

        $child = $parentProfile->children()->create([
            'full_name' => $childName,
            'date_of_birth' => '2021-05-10',
            'gender' => 'female',
            'status' => 'active',
        ]);

        $service = Service::create([
            'name' => 'Schedule Service ' . uniqid(),
            'price' => 1000,
            'duration_minutes' => 120,
            'status' => 'active',
        ]);

        return $child->bookings()->create([
            'service_id' => $service->service_id,
            'booking_date' => $date,
            'booking_time' => $time,
            'status' => $status,
            'total_amount' => $service->price,
        ]);
    }

    private function createAssignment(
        User $caregiver,
        Booking $booking
    ): CaregiverAssignment {
        $admin = User::factory()->create(['role' => 'admin']);

        return CaregiverAssignment::create([
            'booking_id' => $booking->booking_id,
            'caregiver_id' => $caregiver->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);
    }
}
