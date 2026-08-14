<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingRequest;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingRequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_can_submit_cancellation_request(): void
    {
        [$parent, $booking] = $this->createConfirmedBooking();

        $this->actingAs($parent)
            ->post(route('booking-requests.store', $booking), [
                'request_type' => 'cancellation',
                'reason' => 'Our family plan has changed.',
            ])
            ->assertRedirect(route('bookings.show', $booking))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('booking_requests', [
            'booking_id' => $booking->booking_id,
            'request_type' => 'cancellation',
            'request_status' => 'pending',
        ]);

        $this->assertDatabaseHas('bookings', [
            'booking_id' => $booking->booking_id,
            'status' => 'confirmed',
        ]);
    }

    public function test_parent_can_submit_reschedule_request(): void
    {
        [$parent, $booking] = $this->createConfirmedBooking();
        $newDate = now()->addDays(5)->format('Y-m-d');

        $newSlot = TimeSlot::create([
            'service_id' => $booking->service_id,
            'slot_date' => $newDate,
            'start_time' => '14:30',
            'end_time' => '16:30',
            'capacity' => 4,
            'status' => 'open',
        ]);

        $this->actingAs($parent)
            ->post(route('booking-requests.store', $booking), [
                'request_type' => 'reschedule',
                'requested_slot_id' => $newSlot->slot_id,
                'reason' => 'A later time will work better.',
            ])
            ->assertRedirect(route('bookings.show', $booking));

        $this->assertDatabaseHas('booking_requests', [
            'booking_id' => $booking->booking_id,
            'request_type' => 'reschedule',
            'requested_slot_id' => $newSlot->slot_id,
            'requested_time' => '14:30',
            'request_status' => 'pending',
        ]);

        $bookingRequest = BookingRequest::first();

        $this->assertSame(
            $newDate,
            $bookingRequest->requested_date->format('Y-m-d')
        );
    }

    public function test_parent_cannot_request_change_for_another_parents_booking(): void
    {
        [$firstParent] = $this->createConfirmedBooking('First Child');
        [, $secondBooking] = $this->createConfirmedBooking('Second Child');

        $this->actingAs($firstParent)
            ->post(route('booking-requests.store', $secondBooking), [
                'request_type' => 'cancellation',
                'reason' => 'Unauthorized request.',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('booking_requests', 0);
    }

    public function test_pending_booking_cannot_have_change_request(): void
    {
        [$parent, $booking] = $this->createConfirmedBooking();
        $booking->update(['status' => 'pending']);

        $this->actingAs($parent)
            ->post(route('booking-requests.store', $booking), [
                'request_type' => 'cancellation',
                'reason' => 'This request should not be stored.',
            ])
            ->assertUnprocessable();

        $this->assertDatabaseCount('booking_requests', 0);
    }

    public function test_duplicate_pending_request_is_not_created(): void
    {
        [$parent, $booking] = $this->createConfirmedBooking();

        $booking->bookingRequests()->create([
            'request_type' => 'cancellation',
            'reason' => 'First request.',
            'request_status' => 'pending',
        ]);

        $this->actingAs($parent)
            ->post(route('booking-requests.store', $booking), [
                'request_type' => 'cancellation',
                'reason' => 'Second request.',
            ])
            ->assertUnprocessable();

        $this->assertDatabaseCount('booking_requests', 1);
    }

    public function test_admin_can_approve_cancellation_request(): void
    {
        [, $booking] = $this->createConfirmedBooking();
        $admin = User::factory()->create(['role' => 'admin']);

        $bookingRequest = $this->createRequest(
            $booking,
            'cancellation'
        );

        $this->actingAs($admin)
            ->post(route(
                'admin.booking-requests.approve',
                $bookingRequest
            ), [
                'admin_note' => 'Cancellation approved.',
            ])
            ->assertRedirect(route(
                'admin.booking-requests.show',
                $bookingRequest
            ));

        $this->assertDatabaseHas('bookings', [
            'booking_id' => $booking->booking_id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('booking_requests', [
            'request_id' => $bookingRequest->request_id,
            'request_status' => 'approved',
            'reviewed_by' => $admin->id,
        ]);
    }

    public function test_admin_can_approve_reschedule_request(): void
    {
        [, $booking] = $this->createConfirmedBooking();
        $admin = User::factory()->create(['role' => 'admin']);
        $newDate = now()->addDays(7)->format('Y-m-d');

        $bookingRequest = $this->createRequest(
            $booking,
            'reschedule',
            $newDate,
            '16:15'
        );

        $this->actingAs($admin)
            ->post(route(
                'admin.booking-requests.approve',
                $bookingRequest
            ))
            ->assertSessionHas('success');

        $booking->refresh();

        $this->assertSame(
            $newDate,
            $booking->booking_date->format('Y-m-d')
        );
        $this->assertSame(
            '16:15',
            substr($booking->booking_time, 0, 5)
        );
        $this->assertSame('confirmed', $booking->status);
    }

    public function test_admin_can_reject_booking_request(): void
    {
        [, $booking] = $this->createConfirmedBooking();
        $admin = User::factory()->create(['role' => 'admin']);
        $bookingRequest = $this->createRequest(
            $booking,
            'cancellation'
        );

        $this->actingAs($admin)
            ->post(route(
                'admin.booking-requests.reject',
                $bookingRequest
            ), [
                'admin_note' => 'The caregiver visit has already started.',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('booking_requests', [
            'request_id' => $bookingRequest->request_id,
            'request_status' => 'rejected',
            'reviewed_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('bookings', [
            'booking_id' => $booking->booking_id,
            'status' => 'confirmed',
        ]);
    }

    public function test_parent_cannot_access_admin_booking_requests(): void
    {
        [$parent] = $this->createConfirmedBooking();

        $this->actingAs($parent)
            ->get(route('admin.booking-requests.index'))
            ->assertForbidden();
    }

    private function createConfirmedBooking(
        string $childName = 'Request Test Child'
    ): array {
        $parent = User::factory()->create(['role' => 'parent']);
        $parentProfile = $parent->parentProfile()->create();

        $child = $parentProfile->children()->create([
            'full_name' => $childName,
            'date_of_birth' => '2021-05-10',
            'gender' => 'female',
            'status' => 'active',
        ]);

        $service = Service::create([
            'name' => 'Request Test Service ' . uniqid(),
            'price' => 1200,
            'duration_minutes' => 120,
            'status' => 'active',
        ]);

        $timeSlot = TimeSlot::create([
            'service_id' => $service->service_id,
            'slot_date' => now()->addDays(2)->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'capacity' => 4,
            'status' => 'open',
        ]);

        $booking = $child->bookings()->create([
            'service_id' => $service->service_id,
            'slot_id' => $timeSlot->slot_id,
            'booking_date' => $timeSlot->slot_date->format('Y-m-d'),
            'booking_time' => $timeSlot->start_time,
            'status' => 'confirmed',
            'total_amount' => $service->price,
        ]);

        return [$parent, $booking];
    }

    private function createRequest(
        Booking $booking,
        string $type,
        ?string $date = null,
        ?string $time = null
    ): BookingRequest {
        return $booking->bookingRequests()->create([
            'request_type' => $type,
            'requested_date' => $date,
            'requested_time' => $time,
            'reason' => 'Test request reason.',
            'request_status' => 'pending',
        ]);
    }
}
