<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_parent_profile(): void
    {
        $this->get(route('profile.show'))
            ->assertRedirect(route('login'));
    }

    public function test_non_parent_cannot_access_parent_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('profile.show'))
            ->assertForbidden();
    }

    public function test_parent_can_view_profile(): void
    {
        $parent = $this->createParent();

        $this->actingAs($parent)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee($parent->name)
            ->assertSee('Dhaka, Bangladesh')
            ->assertSee('01700000000');
    }

    public function test_parent_can_update_profile(): void
    {
        $parent = $this->createParent();

        $this->actingAs($parent)
            ->post(route('profile.update'), [
                'name' => 'Updated Parent',
                'email' => 'updated.parent@example.com',
                'address' => 'Uttara, Dhaka',
                'emergency_contact_name' => 'Emergency Person',
                'emergency_contact_phone' => '01800000000',
            ])
            ->assertRedirect(route('profile.show'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $parent->id,
            'name' => 'Updated Parent',
            'email' => 'updated.parent@example.com',
        ]);

        $this->assertDatabaseHas('parent_profiles', [
            'user_id' => $parent->id,
            'address' => 'Uttara, Dhaka',
            'emergency_contact_name' => 'Emergency Person',
            'emergency_contact_phone' => '01800000000',
        ]);
    }

    public function test_parent_profile_information_is_validated(): void
    {
        $parent = $this->createParent();

        User::factory()->create([
            'email' => 'already.used@example.com',
        ]);

        $this->actingAs($parent)
            ->post(route('profile.update'), [
                'name' => '',
                'email' => 'already.used@example.com',
                'emergency_contact_phone' => str_repeat('1', 21),
            ])
            ->assertSessionHasErrors([
                'name',
                'email',
                'emergency_contact_phone',
            ]);
    }

    private function createParent(): User
    {
        $parent = User::factory()->create([
            'role' => 'parent',
        ]);

        $parent->parentProfile()->create([
            'address' => 'Dhaka, Bangladesh',
            'emergency_contact_name' => 'Test Contact',
            'emergency_contact_phone' => '01700000000',
        ]);

        return $parent;
    }
}
