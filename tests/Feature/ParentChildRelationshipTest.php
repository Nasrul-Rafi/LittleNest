<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentChildRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_one_parent_profile(): void
    {
        $user = User::factory()->create([
            'role' => 'parent',
        ]);

        $parentProfile = $user->parentProfile()->create([
            'address' => 'Dhaka, Bangladesh',
            'emergency_contact_name' => 'Emergency Person',
            'emergency_contact_phone' => '01700000000',
        ]);

        $this->assertTrue(
            $user->fresh()->parentProfile->is($parentProfile)
        );

        $this->assertTrue(
            $parentProfile->user->is($user)
        );
    }

    public function test_parent_profile_has_many_children(): void
    {
        $user = User::factory()->create([
            'role' => 'parent',
        ]);

        $parentProfile = $user->parentProfile()->create();

        $firstChild = $parentProfile->children()->create([
            'full_name' => 'First Child',
            'date_of_birth' => '2020-01-15',
            'gender' => 'male',
        ]);

        $secondChild = $parentProfile->children()->create([
            'full_name' => 'Second Child',
            'date_of_birth' => '2022-05-20',
            'gender' => 'female',
        ]);

        $this->assertCount(
            2,
            $parentProfile->fresh()->children
        );

        $this->assertTrue(
            $firstChild->parentProfile->is($parentProfile)
        );

        $this->assertTrue(
            $secondChild->parentProfile->is($parentProfile)
        );
    }
}