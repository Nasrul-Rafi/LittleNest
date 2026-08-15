<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminParentFinalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_parent_account(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.parents.store'), [
                'name' => 'New Parent',
                'email' => 'newparent@example.com',
                'phone' => '01711111111',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'address' => 'Dhaka',
                'emergency_contact_name' => 'Emergency Person',
                'emergency_contact_phone' => '01811111111',
            ])
            ->assertRedirect();

        $parent = User::where('email', 'newparent@example.com')->first();

        $this->assertNotNull($parent);
        $this->assertSame('parent', $parent->role);
        $this->assertSame('Dhaka', $parent->parentProfile->address);
    }

    public function test_admin_can_update_and_deactivate_parent(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $parent = User::factory()->create([
            'role' => 'parent',
            'status' => 'active',
            'phone' => '01700000000',
        ]);

        $parent->parentProfile()->create();

        $this->actingAs($admin)
            ->post(route('admin.parents.update', $parent), [
                'name' => 'Updated Parent',
                'email' => $parent->email,
                'phone' => '01900000000',
                'address' => 'Updated Address',
            ])
            ->assertRedirect(route('admin.parents.show', $parent));

        $this->assertDatabaseHas('users', [
            'id' => $parent->id,
            'name' => 'Updated Parent',
            'phone' => '01900000000',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.parents.status', $parent))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $parent->id,
            'status' => 'inactive',
        ]);
    }

    public function test_parent_cannot_access_admin_parent_management(): void
    {
        $parent = User::factory()->create([
            'role' => 'parent',
            'status' => 'active',
        ]);

        $this->actingAs($parent)
            ->get(route('admin.parents.create'))
            ->assertForbidden();
    }
}
