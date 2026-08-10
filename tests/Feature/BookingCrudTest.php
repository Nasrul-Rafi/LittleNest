<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Child;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_booking_pages(): void
    {
        $this->get(route('bookings.index'))
            ->assertRedirect(route('login'));

        $this->get(route('bookings.create'))
            ->assertRedirect(route('login'));
    }

    public function test_non_parent_cannot_access_booking_pages(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get(route('bookings.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('bookings.create'))
            ->assertForbidden();
    }

    public function test_parent_sees_only_own_bookings(): void
    {
        [$firstParent, $firstChild] =
            $this->createParentWithChild('Ariana Rahman');

        [$secondParent, $secondChild] =
            $this->createParentWithChild('Nafisa Ahmed');

        $service = $this->createService();

        $this->createBooking($firstChild, $service);
        $this->createBooking($secondChild, $service);

        $this->actingAs($firstParent)
            ->get(route('bookings.index'))
            ->assertOk()
            ->assertSee('Ariana Rahman')
            ->assertDontSee('Nafisa Ahmed');
    }

    public function test_parent_can_create_booking_for_own_child(): void
    {
        [$parent, $child] =
            $this->createParentWithChild();

        $service = $this->createService();

        $response = $this->actingAs($parent)
            ->post(route('bookings.store'), [
                'child_id' => $child->child_id,
                'service_id' => $service->service_id,
                'booking_date' => now()
                    ->addDays(2)
                    ->format('Y-m-d'),
                'booking_time' => '10:30',
                'special_instructions' =>
                    'The child has a mild peanut allergy.',
            ]);

        $booking = Booking::first();

        $this->assertNotNull($booking);

        $response
            ->assertRedirect(
                route('bookings.show', $booking)
            )
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'booking_id' => $booking->booking_id,
            'child_id' => $child->child_id,
            'service_id' => $service->service_id,
            'status' => 'pending',
            'total_amount' => $service->price,
            'special_instructions' =>
                'The child has a mild peanut allergy.',
        ]);
    }

    public function test_booking_information_is_validated(): void
    {
        [$parent] = $this->createParentWithChild();

        $this->actingAs($parent)
            ->post(route('bookings.store'), [])
            ->assertSessionHasErrors([
                'child_id',
                'service_id',
                'booking_date',
                'booking_time',
            ]);

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_parent_cannot_book_another_parents_child(): void
    {
        [$firstParent] =
            $this->createParentWithChild('First Child');

        [, $secondChild] =
            $this->createParentWithChild('Second Child');

        $service = $this->createService();

        $this->actingAs($firstParent)
            ->post(route('bookings.store'), [
                'child_id' => $secondChild->child_id,
                'service_id' => $service->service_id,
                'booking_date' => now()
                    ->addDays(2)
                    ->format('Y-m-d'),
                'booking_time' => '11:00',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_inactive_service_cannot_be_booked(): void
    {
        [$parent, $child] =
            $this->createParentWithChild();

        $service = $this->createService([
            'status' => 'inactive',
        ]);

        $this->actingAs($parent)
            ->post(route('bookings.store'), [
                'child_id' => $child->child_id,
                'service_id' => $service->service_id,
                'booking_date' => now()
                    ->addDays(2)
                    ->format('Y-m-d'),
                'booking_time' => '12:00',
            ])
            ->assertSessionHasErrors('service_id');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_price_and_status_are_taken_from_database(): void
    {
        [$parent, $child] =
            $this->createParentWithChild();

        $service = $this->createService([
            'price' => 1200,
        ]);

        $this->actingAs($parent)
            ->post(route('bookings.store'), [
                'child_id' => $child->child_id,
                'service_id' => $service->service_id,
                'booking_date' => now()
                    ->addDays(3)
                    ->format('Y-m-d'),
                'booking_time' => '09:30',

                // A dishonest user may add these values.
                'total_amount' => 1,
                'status' => 'completed',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('bookings', [
            'child_id' => $child->child_id,
            'service_id' => $service->service_id,
            'total_amount' => 1200,
            'status' => 'pending',
        ]);

        $this->assertDatabaseMissing('bookings', [
            'child_id' => $child->child_id,
            'total_amount' => 1,
            'status' => 'completed',
        ]);
    }

    public function test_parent_can_view_own_booking(): void
    {
        [$parent, $child] =
            $this->createParentWithChild('Samiha Islam');

        $service = $this->createService();

        $booking = $this->createBooking(
            $child,
            $service
        );

        $this->actingAs($parent)
            ->get(route('bookings.show', $booking))
            ->assertOk()
            ->assertSee('Samiha Islam')
            ->assertSee($service->name);
    }

    public function test_parent_cannot_view_another_parents_booking(): void
    {
        [$firstParent] =
            $this->createParentWithChild('First Child');

        [, $secondChild] =
            $this->createParentWithChild('Second Child');

        $service = $this->createService();

        $booking = $this->createBooking(
            $secondChild,
            $service
        );

        $this->actingAs($firstParent)
            ->get(route('bookings.show', $booking))
            ->assertForbidden();
    }

    public function test_parent_can_cancel_own_pending_booking(): void
    {
        [$parent, $child] =
            $this->createParentWithChild();

        $service = $this->createService();

        $booking = $this->createBooking(
            $child,
            $service
        );

        $this->actingAs($parent)
            ->patch(route('bookings.cancel', $booking))
            ->assertRedirect(
                route('bookings.show', $booking)
            )
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'booking_id' => $booking->booking_id,
            'status' => 'cancelled',
        ]);
    }

    public function test_parent_cannot_cancel_another_parents_booking(): void
    {
        [$firstParent] =
            $this->createParentWithChild('First Child');

        [, $secondChild] =
            $this->createParentWithChild('Second Child');

        $service = $this->createService();

        $booking = $this->createBooking(
            $secondChild,
            $service
        );

        $this->actingAs($firstParent)
            ->patch(route('bookings.cancel', $booking))
            ->assertForbidden();

        $this->assertDatabaseHas('bookings', [
            'booking_id' => $booking->booking_id,
            'status' => 'pending',
        ]);
    }

    public function test_completed_booking_cannot_be_cancelled(): void
    {
        [$parent, $child] =
            $this->createParentWithChild();

        $service = $this->createService();

        $booking = $this->createBooking(
            $child,
            $service,
            'completed'
        );

        $this->actingAs($parent)
            ->patch(route('bookings.cancel', $booking))
            ->assertRedirect(
                route('bookings.show', $booking)
            )
            ->assertSessionHas('error');

        $this->assertDatabaseHas('bookings', [
            'booking_id' => $booking->booking_id,
            'status' => 'completed',
        ]);
    }

    private function createParentWithChild(
        string $childName = 'Test Child'
    ): array {
        $parent = User::factory()->create([
            'role' => 'parent',
        ]);

        $parentProfile = $parent
            ->parentProfile()
            ->create();

        $child = $parentProfile
            ->children()
            ->create([
                'full_name' => $childName,
                'date_of_birth' => now()
                    ->subYears(5)
                    ->format('Y-m-d'),
                'gender' => 'female',
                'status' => 'active',
            ]);

        return [$parent, $child];
    }

    private function createService(
        array $values = []
    ): Service {
        return Service::create(array_merge([
            'name' => 'Hourly Child Care',
            'description' =>
                'Flexible child care service.',
            'price' => 400,
            'duration_minutes' => 60,
            'status' => 'active',
        ], $values));
    }

    private function createBooking(
        Child $child,
        Service $service,
        string $status = 'pending'
    ): Booking {
        return $child->bookings()->create([
            'service_id' => $service->service_id,
            'booking_date' => now()
                ->addDays(2)
                ->format('Y-m-d'),
            'booking_time' => '10:00',
            'special_instructions' => null,
            'status' => $status,
            'total_amount' => $service->price,
        ]);
    }
}