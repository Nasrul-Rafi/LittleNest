<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingListFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_can_search_and_filter_own_bookings(): void
    {
        [$parent, $child] = $this->createParentWithChild();
        $service = $this->createService('Full-Day Care');

        $confirmed = $child->bookings()->create([
            'service_id' => $service->service_id,
            'booking_date' => '2026-08-20',
            'booking_time' => '08:00',
            'status' => 'confirmed',
            'total_amount' => 1800,
        ]);

        $cancelled = $child->bookings()->create([
            'service_id' => $service->service_id,
            'booking_date' => '2026-08-21',
            'booking_time' => '08:00',
            'status' => 'cancelled',
            'total_amount' => 1800,
        ]);

        $this->actingAs($parent)
            ->get(route('bookings.index', [
                'search' => $confirmed->display_reference,
                'status' => 'confirmed',
            ]))
            ->assertOk()
            ->assertSee($confirmed->display_reference)
            ->assertDontSee($cancelled->display_reference);
    }

    public function test_parent_can_filter_bookings_by_month(): void
    {
        [$parent, $child] = $this->createParentWithChild();
        $service = $this->createService('Monthly Care');

        $august = $child->bookings()->create([
            'service_id' => $service->service_id,
            'booking_date' => '2026-08-15',
            'booking_time' => '09:00',
            'status' => 'pending',
            'total_amount' => 900,
        ]);

        $september = $child->bookings()->create([
            'service_id' => $service->service_id,
            'booking_date' => '2026-09-15',
            'booking_time' => '09:00',
            'status' => 'pending',
            'total_amount' => 900,
        ]);

        $this->actingAs($parent)
            ->get(route('bookings.index', ['month' => '2026-08']))
            ->assertOk()
            ->assertSee($august->display_reference)
            ->assertDontSee($september->display_reference);
    }

    private function createParentWithChild(): array
    {
        $parent = User::factory()->create([
            'role' => 'parent',
            'status' => 'active',
        ]);

        $profile = $parent->parentProfile()->create();

        $child = $profile->children()->create([
            'full_name' => 'Booking Filter Child',
            'date_of_birth' => '2021-01-01',
            'gender' => 'female',
            'status' => 'active',
        ]);

        return [$parent, $child];
    }

    private function createService(string $name): Service
    {
        return Service::create([
            'name' => $name,
            'description' => 'Booking list filter service',
            'price' => 1800,
            'duration_minutes' => 480,
            'status' => 'active',
        ]);
    }
}
