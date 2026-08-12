<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCaregiverManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }

    private function createCaregiver(): User
    {
        $caregiver = User::factory()->create([
            'name' => 'Test Caregiver',
            'role' => 'caregiver',
            'status' => 'active',
        ]);

        $caregiver->caregiverProfile()->create([
            'qualification' => 'Diploma in Child Care',
            'experience_years' => 3,
            'specialization' => 'Infant Care',
            'skills' => 'First aid and storytelling',
            'bio' => 'Experienced child caregiver.',
            'availability_status' => 'available',
        ]);

        return $caregiver;
    }

    public function test_guest_cannot_access_caregiver_management(): void
    {
        $response = $this->get(route('admin.caregivers.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_parent_cannot_access_caregiver_management(): void
    {
        $parent = User::factory()->create([
            'role' => 'parent',
        ]);

        $response = $this
            ->actingAs($parent)
            ->get(route('admin.caregivers.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_create_caregiver(): void
    {
        $admin = $this->createAdmin();

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.caregivers.store'), [
                'name' => 'Sara Ahmed',
                'email' => 'sara@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'qualification' => 'Diploma in Child Care',
                'experience_years' => 4,
                'specialization' => 'Toddler Care',
                'skills' => 'First aid and learning activities',
                'bio' => 'Four years of child care experience.',
                'availability_status' => 'available',
            ]);

        $caregiver = User::where('email', 'sara@example.com')->first();

        $this->assertNotNull($caregiver);
        $response->assertRedirect(
            route('admin.caregivers.show', $caregiver)
        );

        $this->assertDatabaseHas('users', [
            'email' => 'sara@example.com',
            'role' => 'caregiver',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('caregiver_profiles', [
            'user_id' => $caregiver->id,
            'qualification' => 'Diploma in Child Care',
            'experience_years' => 4,
            'availability_status' => 'available',
        ]);

        $this->assertTrue(
            Hash::check('password123', $caregiver->password)
        );
    }

    public function test_caregiver_information_is_validated(): void
    {
        $admin = $this->createAdmin();

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.caregivers.store'), [
                'name' => '',
                'email' => 'invalid-email',
                'password' => 'short',
                'password_confirmation' => 'different',
                'qualification' => '',
                'experience_years' => -1,
                'availability_status' => 'invalid',
            ]);

        $response->assertSessionHasErrors([
            'name',
            'email',
            'password',
            'qualification',
            'experience_years',
            'availability_status',
        ]);

        $this->assertDatabaseMissing('users', [
            'role' => 'caregiver',
        ]);
    }

    public function test_admin_can_update_caregiver(): void
    {
        $admin = $this->createAdmin();
        $caregiver = $this->createCaregiver();

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.caregivers.update', $caregiver), [
                'name' => 'Updated Caregiver',
                'email' => 'updated@example.com',
                'password' => '',
                'password_confirmation' => '',
                'qualification' => 'Bachelor in Education',
                'experience_years' => 5,
                'specialization' => 'Early Learning',
                'skills' => 'Teaching and first aid',
                'bio' => 'Updated caregiver bio.',
                'availability_status' => 'unavailable',
            ]);

        $response->assertRedirect(
            route('admin.caregivers.show', $caregiver)
        );

        $this->assertDatabaseHas('users', [
            'id' => $caregiver->id,
            'name' => 'Updated Caregiver',
            'email' => 'updated@example.com',
        ]);

        $this->assertDatabaseHas('caregiver_profiles', [
            'user_id' => $caregiver->id,
            'qualification' => 'Bachelor in Education',
            'experience_years' => 5,
            'availability_status' => 'unavailable',
        ]);
    }

    public function test_admin_can_deactivate_and_activate_caregiver(): void
    {
        $admin = $this->createAdmin();
        $caregiver = $this->createCaregiver();

        $this->actingAs($admin)
            ->post(route('admin.caregivers.status', $caregiver))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $caregiver->id,
            'status' => 'inactive',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.caregivers.status', $caregiver))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $caregiver->id,
            'status' => 'active',
        ]);
    }

    public function test_inactive_caregiver_cannot_login(): void
    {
        $caregiver = $this->createCaregiver();
        $caregiver->password = Hash::make('password123');
        $caregiver->status = 'inactive';
        $caregiver->save();

        $response = $this->post(route('login.store'), [
            'email' => $caregiver->email,
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_active_caregiver_can_view_caregiver_dashboard(): void
    {
        $caregiver = $this->createCaregiver();

        $response = $this
            ->actingAs($caregiver)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Caregiver Dashboard');
        $response->assertSee('Diploma in Child Care');
    }
}
