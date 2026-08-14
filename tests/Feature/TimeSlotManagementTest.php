<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeSlotManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_time_slot(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $service = $this->createService();
        $slotDate = now()->addDays(3)->format('Y-m-d');

        $this->actingAs($admin)
            ->post(route('admin.time-slots.store'), [
                'service_id' => $service->service_id,
                'slot_date' => $slotDate,
                'start_time' => '08:00',
                'end_time' => '12:00',
                'capacity' => 10,
                'status' => 'open',
            ])
            ->assertRedirect(route('admin.time-slots.index'))
            ->assertSessionHas('success');

        $timeSlot = TimeSlot::first();

        $this->assertNotNull($timeSlot);
        $this->assertSame($service->service_id, $timeSlot->service_id);
        $this->assertSame($slotDate, $timeSlot->slot_date->format('Y-m-d'));
        $this->assertSame('08:00', substr($timeSlot->start_time, 0, 5));
        $this->assertSame('12:00', substr($timeSlot->end_time, 0, 5));
        $this->assertSame(10, $timeSlot->capacity);
        $this->assertSame('open', $timeSlot->status);
    }

    public function test_parent_cannot_access_admin_time_slots(): void
    {
        $parent = User::factory()->create(['role' => 'parent']);

        $this->actingAs($parent)
            ->get(route('admin.time-slots.index'))
            ->assertForbidden();
    }

    public function test_end_time_must_be_after_start_time(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $service = $this->createService();

        $this->actingAs($admin)
            ->post(route('admin.time-slots.store'), [
                'service_id' => $service->service_id,
                'slot_date' => now()->addDays(2)->format('Y-m-d'),
                'start_time' => '12:00',
                'end_time' => '10:00',
                'capacity' => 5,
                'status' => 'open',
            ])
            ->assertSessionHasErrors('end_time');

        $this->assertDatabaseCount('time_slots', 0);
    }

    public function test_capacity_cannot_be_lower_than_active_bookings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $service = $this->createService();
        $timeSlot = $this->createTimeSlot($service, 3);

        Booking::create([
            'service_id' => $service->service_id,
            'slot_id' => $timeSlot->slot_id,
            'booking_date' => $timeSlot->slot_date->format('Y-m-d'),
            'booking_time' => $timeSlot->start_time,
            'status' => 'pending',
            'total_amount' => $service->price,
            'child_id' => $this->createChild()->child_id,
        ]);

        Booking::create([
            'service_id' => $service->service_id,
            'slot_id' => $timeSlot->slot_id,
            'booking_date' => $timeSlot->slot_date->format('Y-m-d'),
            'booking_time' => $timeSlot->start_time,
            'status' => 'confirmed',
            'total_amount' => $service->price,
            'child_id' => $this->createChild()->child_id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.time-slots.update', $timeSlot), [
                'service_id' => $service->service_id,
                'slot_date' => $timeSlot->slot_date->format('Y-m-d'),
                'start_time' => substr($timeSlot->start_time, 0, 5),
                'end_time' => substr($timeSlot->end_time, 0, 5),
                'capacity' => 1,
                'status' => 'open',
            ])
            ->assertSessionHasErrors('capacity');

        $this->assertDatabaseHas('time_slots', [
            'slot_id' => $timeSlot->slot_id,
            'capacity' => 3,
        ]);
    }

    public function test_admin_can_close_and_reopen_time_slot(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $timeSlot = $this->createTimeSlot($this->createService());

        $this->actingAs($admin)
            ->post(route('admin.time-slots.status', $timeSlot))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('time_slots', [
            'slot_id' => $timeSlot->slot_id,
            'status' => 'closed',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.time-slots.status', $timeSlot))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('time_slots', [
            'slot_id' => $timeSlot->slot_id,
            'status' => 'open',
        ]);
    }

    private function createService(): Service
    {
        return Service::create([
            'name' => 'Time Slot Test Service ' . uniqid(),
            'description' => 'Test service.',
            'price' => 1000,
            'duration_minutes' => 240,
            'status' => 'active',
        ]);
    }

    private function createTimeSlot(
        Service $service,
        int $capacity = 5
    ): TimeSlot {
        return TimeSlot::create([
            'service_id' => $service->service_id,
            'slot_date' => now()->addDays(3)->format('Y-m-d'),
            'start_time' => '08:00',
            'end_time' => '12:00',
            'capacity' => $capacity,
            'status' => 'open',
        ]);
    }

    private function createChild()
    {
        $parent = User::factory()->create(['role' => 'parent']);
        $profile = $parent->parentProfile()->create();

        return $profile->children()->create([
            'full_name' => 'Capacity Test Child ' . uniqid(),
            'date_of_birth' => '2021-01-01',
            'gender' => 'female',
            'status' => 'active',
        ]);
    }
}
