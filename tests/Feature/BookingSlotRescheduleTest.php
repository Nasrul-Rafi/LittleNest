<?php

namespace Tests\Feature;

use App\Models\BookingRequest;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingSlotRescheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_can_request_reschedule_to_same_service_slot(): void
    {
        [$parent, $booking, $service] = $this->createConfirmedBooking();
        $newSlot = $this->createTimeSlot(
            $service,
            now()->addDays(5)->format('Y-m-d'),
            '14:00',
            '16:00'
        );

        $this->actingAs($parent)
            ->post(route('booking-requests.store', $booking), [
                'request_type' => 'reschedule',
                'requested_slot_id' => $newSlot->slot_id,
                'reason' => 'A later day works better.',
            ])
            ->assertRedirect(route('bookings.show', $booking));

        $this->assertDatabaseHas('booking_requests', [
            'booking_id' => $booking->booking_id,
            'requested_slot_id' => $newSlot->slot_id,
            'request_type' => 'reschedule',
            'request_status' => 'pending',
        ]);
    }

    public function test_parent_cannot_request_slot_from_different_service(): void
    {
        [$parent, $booking] = $this->createConfirmedBooking();
        $differentService = $this->createService();
        $wrongSlot = $this->createTimeSlot(
            $differentService,
            now()->addDays(6)->format('Y-m-d'),
            '10:00',
            '12:00'
        );

        $this->actingAs($parent)
            ->post(route('booking-requests.store', $booking), [
                'request_type' => 'reschedule',
                'requested_slot_id' => $wrongSlot->slot_id,
                'reason' => 'Trying another slot.',
            ])
            ->assertSessionHasErrors('requested_slot_id');

        $this->assertDatabaseCount('booking_requests', 0);
    }

    public function test_admin_approval_moves_booking_to_requested_slot(): void
    {
        [$parent, $booking, $service] = $this->createConfirmedBooking();
        $admin = User::factory()->create(['role' => 'admin']);
        $newDate = now()->addDays(7)->format('Y-m-d');
        $newSlot = $this->createTimeSlot(
            $service,
            $newDate,
            '15:00',
            '17:00'
        );

        $this->actingAs($parent)
            ->post(route('booking-requests.store', $booking), [
                'request_type' => 'reschedule',
                'requested_slot_id' => $newSlot->slot_id,
                'reason' => 'New schedule requested.',
            ]);

        $bookingRequest = BookingRequest::first();

        $this->actingAs($admin)
            ->post(route(
                'admin.booking-requests.approve',
                $bookingRequest
            ))
            ->assertSessionHas('success');

        $booking->refresh();

        $this->assertSame($newSlot->slot_id, $booking->slot_id);
        $this->assertSame(
            $newDate,
            $booking->booking_date->format('Y-m-d')
        );
        $this->assertSame(
            '15:00',
            substr($booking->booking_time, 0, 5)
        );
        $this->assertSame('confirmed', $booking->status);
    }

    public function test_admin_cannot_approve_reschedule_if_target_slot_becomes_full(): void
    {
        [$parent, $booking, $service] = $this->createConfirmedBooking();
        $admin = User::factory()->create(['role' => 'admin']);
        $newSlot = $this->createTimeSlot(
            $service,
            now()->addDays(8)->format('Y-m-d'),
            '11:00',
            '13:00',
            1
        );

        $this->actingAs($parent)
            ->post(route('booking-requests.store', $booking), [
                'request_type' => 'reschedule',
                'requested_slot_id' => $newSlot->slot_id,
                'reason' => 'Request before slot fills.',
            ]);

        $bookingRequest = BookingRequest::first();

        [, $otherChild] = $this->createParentWithChild();
        $otherChild->bookings()->create([
            'service_id' => $service->service_id,
            'slot_id' => $newSlot->slot_id,
            'booking_date' => $newSlot->slot_date->format('Y-m-d'),
            'booking_time' => $newSlot->start_time,
            'status' => 'pending',
            'total_amount' => $service->price,
        ]);

        $oldSlotId = $booking->slot_id;

        $this->actingAs($admin)
            ->post(route(
                'admin.booking-requests.approve',
                $bookingRequest
            ))
            ->assertSessionHas('error');

        $booking->refresh();
        $bookingRequest->refresh();

        $this->assertSame($oldSlotId, $booking->slot_id);
        $this->assertSame('pending', $bookingRequest->request_status);
    }

    private function createConfirmedBooking(): array
    {
        [$parent, $child] = $this->createParentWithChild();
        $service = $this->createService();
        $slot = $this->createTimeSlot(
            $service,
            now()->addDays(2)->format('Y-m-d'),
            '08:00',
            '12:00'
        );

        $booking = $child->bookings()->create([
            'service_id' => $service->service_id,
            'slot_id' => $slot->slot_id,
            'booking_date' => $slot->slot_date->format('Y-m-d'),
            'booking_time' => $slot->start_time,
            'status' => 'confirmed',
            'total_amount' => $service->price,
        ]);

        return [$parent, $booking, $service];
    }

    private function createParentWithChild(): array
    {
        $parent = User::factory()->create(['role' => 'parent']);
        $profile = $parent->parentProfile()->create();

        $child = $profile->children()->create([
            'full_name' => 'Reschedule Child ' . uniqid(),
            'date_of_birth' => '2021-01-01',
            'gender' => 'female',
            'status' => 'active',
        ]);

        return [$parent, $child];
    }

    private function createService(): Service
    {
        return Service::create([
            'name' => 'Reschedule Service ' . uniqid(),
            'description' => 'Test service.',
            'price' => 900,
            'duration_minutes' => 120,
            'status' => 'active',
        ]);
    }

    private function createTimeSlot(
        Service $service,
        string $date,
        string $start,
        string $end,
        int $capacity = 3
    ): TimeSlot {
        return TimeSlot::create([
            'service_id' => $service->service_id,
            'slot_date' => $date,
            'start_time' => $start,
            'end_time' => $end,
            'capacity' => $capacity,
            'status' => 'open',
        ]);
    }
}
