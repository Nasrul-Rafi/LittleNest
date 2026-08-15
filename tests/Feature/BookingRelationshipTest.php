<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Child;
use App\Models\ParentProfile;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingRelationshipTest extends TestCase
{
    use RefreshDatabase;

    private function createParentWithChild(): array
    {
        $user = User::factory()->create([
            'role' => 'parent',
        ]);

        $parentProfile = $user->parentProfile()->create();

        $child = $parentProfile->children()->create([
            'full_name' => 'Test Child',
            'date_of_birth' => '2020-05-10',
            'gender' => 'male',
            'status' => 'active',
        ]);

        return [
            $parentProfile,
            $child,
        ];
    }

    private function createService(): Service
    {
        return Service::create([
            'name' => 'Half-Day Child Care',
            'description' => 'Child care service for half a day.',
            'price' => 1200,
            'duration_minutes' => 240,
            'status' => 'active',
        ]);
    }

    private function createBooking(
        Child $child,
        Service $service
    ): Booking {
        return $child->bookings()->create([
            'service_id' => $service->service_id,
            'booking_date' => now()
                ->addDays(2)
                ->format('Y-m-d'),
            'booking_time' => '10:00',
            'special_instructions' => 'Avoid peanuts.',
            'total_amount' => $service->price,
        ]);
    }

    public function test_booking_belongs_to_child_and_service(): void
    {
        [, $child] = $this->createParentWithChild();

        $service = $this->createService();
        $booking = $this->createBooking($child, $service);

        $this->assertTrue(
            $booking->child->is($child)
        );

        $this->assertTrue(
            $booking->service->is($service)
        );

        $this->assertTrue(
            $child->bookings->contains($booking)
        );

        $this->assertTrue(
            $service->bookings->contains($booking)
        );
    }

    public function test_parent_profile_sees_only_own_bookings(): void
    {
        [$firstParent, $firstChild] =
            $this->createParentWithChild();

        [$secondParent, $secondChild] =
            $this->createParentWithChild();

        $service = $this->createService();

        $firstBooking = $this->createBooking(
            $firstChild,
            $service
        );

        $secondBooking = $this->createBooking(
            $secondChild,
            $service
        );

        $firstParentBookings = $firstParent
            ->bookings()
            ->get();

        $this->assertCount(1, $firstParentBookings);

        $this->assertTrue(
            $firstParentBookings->contains($firstBooking)
        );

        $this->assertFalse(
            $firstParentBookings->contains($secondBooking)
        );

        $this->assertCount(
            1,
            $secondParent->bookings()->get()
        );
    }

    public function test_new_booking_has_pending_status(): void
    {
        [, $child] = $this->createParentWithChild();

        $service = $this->createService();
        $booking = $this->createBooking($child, $service);

        $booking->refresh();

        $this->assertSame(
            'pending',
            $booking->status
        );

        $this->assertSame(
            '1200.00',
            $booking->total_amount
        );
    }

    public function test_deleting_child_deletes_its_bookings(): void
    {
        [, $child] = $this->createParentWithChild();

        $service = $this->createService();
        $booking = $this->createBooking($child, $service);

        $child->delete();

        $this->assertDatabaseMissing('bookings', [
            'booking_id' => $booking->booking_id,
        ]);

        $this->assertDatabaseHas('services', [
            'service_id' => $service->service_id,
        ]);
    }
}
