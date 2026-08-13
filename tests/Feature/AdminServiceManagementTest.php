<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminServiceManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }

    private function createService(
        string $name = 'Test Child Care'
    ): Service {
        return Service::create([
            'name' => $name,
            'description' => 'Test service description.',
            'price' => 1000,
            'duration_minutes' => 120,
            'status' => 'active',
        ]);
    }

    public function test_guest_cannot_access_service_management(): void
    {
        $this->get(route('admin.services.index'))
            ->assertRedirect(route('login'));
    }

    public function test_parent_cannot_access_service_management(): void
    {
        $parent = User::factory()->create([
            'role' => 'parent',
        ]);

        $this->actingAs($parent)
            ->get(route('admin.services.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_service(): void
    {
        $admin = $this->createAdmin();

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.services.store'), [
                'name' => 'Weekend Child Care',
                'description' => 'Weekend care for children.',
                'price' => 1800,
                'duration_minutes' => 360,
                'status' => 'active',
            ]);

        $service = Service::where(
            'name',
            'Weekend Child Care'
        )->first();

        $this->assertNotNull($service);

        $response->assertRedirect(
            route('admin.services.show', $service)
        );

        $this->assertDatabaseHas('services', [
            'name' => 'Weekend Child Care',
            'price' => 1800,
            'duration_minutes' => 360,
            'status' => 'active',
        ]);
    }

    public function test_service_information_is_validated(): void
    {
        $admin = $this->createAdmin();
        $this->createService('Existing Service');

        $this->actingAs($admin)
            ->post(route('admin.services.store'), [
                'name' => 'Existing Service',
                'description' => '',
                'price' => -10,
                'duration_minutes' => 5,
                'status' => 'invalid',
            ])
            ->assertSessionHasErrors([
                'name',
                'price',
                'duration_minutes',
                'status',
            ]);

        $this->assertDatabaseCount('services', 1);
    }

    public function test_admin_can_update_service(): void
    {
        $admin = $this->createAdmin();
        $service = $this->createService();

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.services.update', $service), [
                'name' => 'Updated Child Care',
                'description' => 'Updated description.',
                'price' => 1250,
                'duration_minutes' => 180,
                'status' => 'inactive',
            ]);

        $response->assertRedirect(
            route('admin.services.show', $service)
        );

        $this->assertDatabaseHas('services', [
            'service_id' => $service->service_id,
            'name' => 'Updated Child Care',
            'price' => 1250,
            'duration_minutes' => 180,
            'status' => 'inactive',
        ]);
    }

    public function test_admin_can_deactivate_and_activate_service(): void
    {
        $admin = $this->createAdmin();
        $service = $this->createService();

        $this->actingAs($admin)
            ->post(route('admin.services.status', $service))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('services', [
            'service_id' => $service->service_id,
            'status' => 'inactive',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.services.status', $service))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('services', [
            'service_id' => $service->service_id,
            'status' => 'active',
        ]);
    }
}
