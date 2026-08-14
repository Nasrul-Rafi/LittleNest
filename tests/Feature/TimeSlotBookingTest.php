<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeSlotBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_booking_uses_selected_time_slot_data(): void
    {
        [$parent, $child] = $this->createParentWithChild();
        $service = $this->createService();
        $timeSlot = $this->createTimeSlot($service);

        $this->actingAs($parent)
            ->post(route('bookings.store'), [
                'child_id' => $child->child_id,
                'slot_id' => $timeSlot->slot_id,
                'special_instructions' => 'Call before pickup.',
            ])
            ->assertSessionHasNoErrors();

        $booking = Booking::first();

        $this->assertNotNull($booking);
        $this->assertSame($timeSlot->slot_id, $booking->slot_id);
        $this->assertSame($service->service_id, $booking->service_id);
        $this->assertSame(
            $timeSlot->slot_date->format('Y-m-d'),
            $booking->booking_date->format('Y-m-d')
        );
        $this->assertSame(
            substr($timeSlot->start_time, 0, 5),
            substr($booking->booking_time, 0, 5)
        );
        $this->assertSame('pending', $booking->status);
        $this->assertSame('1250.00', $booking->total_amount);
    }

    public function test_new_booking_gets_unique_littlenest_reference(): void
    {
        [$parent, $child] = $this->createParentWithChild();
        $service = $this->createService();
        $timeSlot = $this->createTimeSlot($service);

        $this->actingAs($parent)
            ->post(route('bookings.store'), [
                'child_id' => $child->child_id,
                'slot_id' => $timeSlot->slot_id,
            ]);

        $booking = Booking::first();
        $year = $timeSlot->slot_date->format('Y');

        $this->assertSame(
            'LN-' . $year . '-0001',
            $booking->booking_reference
        );
    }

    public function test_parent_cannot_book_a_full_time_slot(): void
    {
        [$parent, $child] = $this->createParentWithChild();
        [, $otherChild] = $this->createParentWithChild();
        $service = $this->createService();
        $timeSlot = $this->createTimeSlot($service, 1);

        $otherChild->bookings()->create([
            'service_id' => $service->service_id,
            'slot_id' => $timeSlot->slot_id,
            'booking_date' => $timeSlot->slot_date->format('Y-m-d'),
            'booking_time' => $timeSlot->start_time,
            'status' => 'pending',
            'total_amount' => $service->price,
        ]);

        $this->actingAs($parent)
            ->post(route('bookings.store'), [
                'child_id' => $child->child_id,
                'slot_id' => $timeSlot->slot_id,
            ])
            ->assertSessionHasErrors('slot_id');

        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_parent_cannot_book_a_closed_time_slot(): void
    {
        [$parent, $child] = $this->createParentWithChild();
        $service = $this->createService();
        $timeSlot = $this->createTimeSlot($service);
        $timeSlot->update(['status' => 'closed']);

        $this->actingAs($parent)
            ->post(route('bookings.store'), [
                'child_id' => $child->child_id,
                'slot_id' => $timeSlot->slot_id,
            ])
            ->assertSessionHasErrors('slot_id');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_cancelled_booking_frees_slot_capacity(): void
    {
        [$parent, $child] = $this->createParentWithChild();
        $service = $this->createService();
        $timeSlot = $this->createTimeSlot($service, 1);

        $this->actingAs($parent)
            ->post(route('bookings.store'), [
                'child_id' => $child->child_id,
                'slot_id' => $timeSlot->slot_id,
            ]);

        $booking = Booking::first();
        $this->assertSame(0, $timeSlot->remainingCapacity());

        $this->actingAs($parent)
            ->patch(route('bookings.cancel', $booking));

        $this->assertSame(1, $timeSlot->remainingCapacity());
    }

    private function createParentWithChild(): array
    {
        $parent = User::factory()->create(['role' => 'parent']);
        $profile = $parent->parentProfile()->create();

        $child = $profile->children()->create([
            'full_name' => 'Slot Test Child ' . uniqid(),
            'date_of_birth' => '2021-01-01',
            'gender' => 'female',
            'status' => 'active',
        ]);

        return [$parent, $child];
    }

    private function createService(): Service
    {
        return Service::create([
            'name' => 'Slot Booking Service ' . uniqid(),
            'description' => 'Test service.',
            'price' => 1250,
            'duration_minutes' => 240,
            'status' => 'active',
        ]);
    }

    private function createTimeSlot(
        Service $service,
        int $capacity = 3
    ): TimeSlot {
        return TimeSlot::create([
            'service_id' => $service->service_id,
            'slot_date' => now()->addDays(3)->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '13:00',
            'capacity' => $capacity,
            'status' => 'open',
        ]);
    }
}
