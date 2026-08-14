<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBookingDecisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reject_pending_booking(): void
    {
        [$booking, $timeSlot] = $this->createPendingBooking();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.bookings.reject', $booking))
            ->assertRedirect(route('admin.bookings.show', $booking))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'booking_id' => $booking->booking_id,
            'status' => 'cancelled',
        ]);

        $this->assertSame(1, $timeSlot->remainingCapacity());
    }

    public function test_non_admin_cannot_reject_booking(): void
    {
        [$booking] = $this->createPendingBooking();
        $parent = User::factory()->create(['role' => 'parent']);

        $this->actingAs($parent)
            ->post(route('admin.bookings.reject', $booking))
            ->assertForbidden();

        $this->assertDatabaseHas('bookings', [
            'booking_id' => $booking->booking_id,
            'status' => 'pending',
        ]);
    }

    public function test_confirmed_booking_cannot_be_rejected(): void
    {
        [$booking] = $this->createPendingBooking();
        $booking->update(['status' => 'confirmed']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.bookings.reject', $booking))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('bookings', [
            'booking_id' => $booking->booking_id,
            'status' => 'confirmed',
        ]);
    }

    private function createPendingBooking(): array
    {
        $parent = User::factory()->create(['role' => 'parent']);
        $profile = $parent->parentProfile()->create();
        $child = $profile->children()->create([
            'full_name' => 'Decision Test Child',
            'date_of_birth' => '2021-01-01',
            'gender' => 'female',
            'status' => 'active',
        ]);

        $service = Service::create([
            'name' => 'Decision Test Service ' . uniqid(),
            'price' => 800,
            'duration_minutes' => 120,
            'status' => 'active',
        ]);

        $timeSlot = TimeSlot::create([
            'service_id' => $service->service_id,
            'slot_date' => now()->addDays(2)->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'capacity' => 1,
            'status' => 'open',
        ]);

        $booking = $child->bookings()->create([
            'service_id' => $service->service_id,
            'slot_id' => $timeSlot->slot_id,
            'booking_date' => $timeSlot->slot_date->format('Y-m-d'),
            'booking_time' => $timeSlot->start_time,
            'status' => 'pending',
            'total_amount' => $service->price,
        ]);

        return [$booking, $timeSlot];
    }
}
