<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaregiverProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_caregiver_profile(): void
    {
        $this->get(route('caregiver.profile.show'))
            ->assertRedirect(route('login'));
    }

    public function test_parent_cannot_access_caregiver_profile(): void
    {
        $parent = User::factory()->create(['role' => 'parent']);

        $this->actingAs($parent)
            ->get(route('caregiver.profile.show'))
            ->assertForbidden();
    }

    public function test_active_caregiver_can_view_profile(): void
    {
        $caregiver = $this->createCaregiver();

        $this->actingAs($caregiver)
            ->get(route('caregiver.profile.show'))
            ->assertOk()
            ->assertSee('My Caregiver Profile')
            ->assertSee($caregiver->name)
            ->assertSee('Diploma in Child Care')
            ->assertSee('First aid, storytelling');
    }

    public function test_caregiver_can_update_own_profile(): void
    {
        $caregiver = $this->createCaregiver();

        $this->actingAs($caregiver)
            ->post(route('caregiver.profile.update'), [
                'name' => 'Updated Caregiver',
                'email' => 'updated.caregiver@example.com',
                'qualification' => 'Advanced Child Care Certificate',
                'experience_years' => 5,
                'specialization' => 'Infant Care',
                'skills' => 'First aid, meal preparation',
                'bio' => 'Experienced and caring professional.',
                'availability_status' => 'unavailable',
            ])
            ->assertRedirect(route('caregiver.profile.show'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $caregiver->id,
            'name' => 'Updated Caregiver',
            'email' => 'updated.caregiver@example.com',
        ]);

        $this->assertDatabaseHas('caregiver_profiles', [
            'user_id' => $caregiver->id,
            'qualification' => 'Advanced Child Care Certificate',
            'experience_years' => 5,
            'specialization' => 'Infant Care',
            'availability_status' => 'unavailable',
        ]);
    }

    public function test_caregiver_profile_information_is_validated(): void
    {
        $caregiver = $this->createCaregiver();

        User::factory()->create([
            'email' => 'already.used@example.com',
        ]);

        $this->actingAs($caregiver)
            ->post(route('caregiver.profile.update'), [
                'name' => '',
                'email' => 'already.used@example.com',
                'qualification' => '',
                'experience_years' => 100,
                'availability_status' => 'invalid-status',
            ])
            ->assertSessionHasErrors([
                'name',
                'email',
                'qualification',
                'experience_years',
                'availability_status',
            ]);
    }

    public function test_inactive_caregiver_cannot_access_profile(): void
    {
        $caregiver = $this->createCaregiver();
        $caregiver->update(['status' => 'inactive']);

        $this->actingAs($caregiver)
            ->get(route('caregiver.profile.show'))
            ->assertForbidden();
    }

    private function createCaregiver(): User
    {
        $caregiver = User::factory()->create([
            'name' => 'Profile Caregiver',
            'role' => 'caregiver',
            'status' => 'active',
        ]);

        $caregiver->caregiverProfile()->create([
            'qualification' => 'Diploma in Child Care',
            'experience_years' => 3,
            'specialization' => 'Toddler Care',
            'skills' => 'First aid, storytelling',
            'bio' => 'A friendly caregiver.',
            'availability_status' => 'available',
        ]);

        return $caregiver;
    }
}
