<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentServicePortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_can_browse_active_services_and_available_slots(): void
    {
        $parent = User::factory()->create([
            'role' => 'parent',
            'status' => 'active',
        ]);

        $parent->parentProfile()->create();

        $service = Service::create([
            'name' => 'Parent Portal Care',
            'description' => 'Care service for portal testing.',
            'price' => 900,
            'duration_minutes' => 180,
            'status' => 'active',
        ]);

        $slot = TimeSlot::create([
            'service_id' => $service->service_id,
            'slot_date' => now()->addDays(3)->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '12:00',
            'capacity' => 6,
            'status' => 'open',
        ]);

        $this->actingAs($parent)
            ->get(route('parent.services.index'))
            ->assertOk()
            ->assertSee('Parent Portal Care');

        $this->actingAs($parent)
            ->get(route('parent.services.show', $service))
            ->assertOk()
            ->assertSee('Available Time Slots')
            ->assertSee($slot->slot_date->format('d M Y'))
            ->assertSee('Book This Slot');
    }

    public function test_non_parent_cannot_open_parent_service_portal(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('parent.services.index'))
            ->assertForbidden();
    }
}
