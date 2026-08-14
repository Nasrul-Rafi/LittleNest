<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LittleNestProfileFieldTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_can_save_extended_child_care_fields(): void
    {
        $parent = User::factory()->create(['role' => 'parent']);
        $parent->parentProfile()->create();

        $this->actingAs($parent)
            ->post(route('children.store'), [
                'full_name' => 'Ariana Rahman',
                'date_of_birth' => '2022-03-12',
                'gender' => 'female',
                'guardian_relation' => 'Mother',
                'allergies' => 'Peanuts',
                'medical_notes' => 'None',
                'medicine_instructions' => 'Only with guardian approval',
                'special_needs' => null,
                'emergency_notes' => 'Call guardian immediately',
                'status' => 'active',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('children', [
            'full_name' => 'Ariana Rahman',
            'guardian_relation' => 'Mother',
            'medicine_instructions' => 'Only with guardian approval',
            'emergency_notes' => 'Call guardian immediately',
        ]);
    }

    public function test_admin_can_save_service_age_range(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.services.store'), [
                'name' => 'Age Range Care',
                'description' => 'Test service.',
                'min_age' => 2,
                'max_age' => 6,
                'price' => 1800,
                'duration_minutes' => 480,
                'status' => 'active',
            ])
            ->assertSessionHas('success');

        $service = Service::where('name', 'Age Range Care')->first();
        $this->assertNotNull($service);
        $this->assertSame(2, (int) $service->min_age);
        $this->assertSame(6, (int) $service->max_age);
    }
}
