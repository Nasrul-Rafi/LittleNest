<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_login_does_not_follow_an_old_admin_intended_url(): void
    {
        $parent = User::factory()->create([
            'email' => 'parent-role-test@example.com',
            'password' => Hash::make('12345678'),
            'role' => 'parent',
            'status' => 'active',
        ]);

        $parent->parentProfile()->create();

        $this->withSession([
            'url.intended' => route('admin.bookings.index'),
        ])->post(route('login.store'), [
            'email' => $parent->email,
            'password' => '12345678',
        ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionMissing('url.intended');
    }

    public function test_parent_cannot_open_admin_booking_history(): void
    {
        $parent = User::factory()->create([
            'role' => 'parent',
            'status' => 'active',
        ]);

        $parent->parentProfile()->create();

        $this->actingAs($parent)
            ->get(route('admin.bookings.index'))
            ->assertForbidden();
    }

    public function test_admin_cannot_open_parent_booking_pages(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('bookings.index'))
            ->assertForbidden();
    }
}
