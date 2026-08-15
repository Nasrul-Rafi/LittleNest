<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_can_register(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test Parent',
            'email' => 'parent@example.com',
            'phone' => '01700000000',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();

        $user = User::where(
            'email',
            'parent@example.com'
        )->first();

        $this->assertNotNull($user);
        $this->assertSame('parent', $user->role);
        $this->assertSame('01700000000', $user->phone);

        $this->assertDatabaseHas('parent_profiles', [
            'user_id' => $user->id,
        ]);

        $this->assertNotNull(
            $user->fresh()->parentProfile
        );

        $this->assertTrue(
            Hash::check('password123', $user->password)
        );
    }

    public function test_registered_parent_can_login(): void
    {
        $user = User::factory()->create([
            'role' => 'parent',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create([
            'role' => 'parent',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('logout'));

        $response->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }
}
