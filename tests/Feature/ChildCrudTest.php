<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChildCrudTest extends TestCase
{
    use RefreshDatabase;

    private function createParent(): User
    {
        $parent = User::factory()->create([
            'role' => 'parent',
        ]);

        $parent->parentProfile()->create();

        return $parent;
    }

    private function createChild(
        User $parent,
        array $attributes = []
    ): Child {
        return $parent->parentProfile->children()->create(
            array_merge([
                'full_name' => 'Test Child',
                'date_of_birth' => '2020-05-10',
                'gender' => 'male',
                'allergies' => null,
                'medical_notes' => null,
                'special_needs' => null,
                'status' => 'active',
            ], $attributes)
        );
    }

    public function test_guest_cannot_access_child_pages(): void
    {
        $response = $this->get(route('children.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_parent_sees_only_own_children(): void
    {
        $firstParent = $this->createParent();
        $secondParent = $this->createParent();

        $this->createChild($firstParent, [
            'full_name' => 'Own Child',
        ]);

        $this->createChild($secondParent, [
            'full_name' => 'Another Parent Child',
        ]);

        $response = $this
            ->actingAs($firstParent)
            ->get(route('children.index'));

        $response->assertOk();
        $response->assertSee('Own Child');
        $response->assertDontSee('Another Parent Child');
    }

    public function test_parent_can_create_child(): void
    {
        $parent = $this->createParent();

        $response = $this
            ->actingAs($parent)
            ->post(route('children.store'), [
                'full_name' => 'Sara Ahmed',
                'date_of_birth' => '2021-03-15',
                'gender' => 'female',
                'allergies' => 'Peanuts',
                'medical_notes' => 'No medical condition',
                'special_needs' => null,
                'status' => 'active',
            ]);

        $child = Child::where(
            'full_name',
            'Sara Ahmed'
        )->first();

        $this->assertNotNull($child);

        $response->assertRedirect(
            route('children.show', $child)
        );

        $this->assertDatabaseHas('children', [
            'parent_profile_id' =>
                $parent->parentProfile->parent_profile_id,
            'full_name' => 'Sara Ahmed',
            'gender' => 'female',
            'status' => 'active',
        ]);
    }

    public function test_child_information_is_validated(): void
    {
        $parent = $this->createParent();

        $response = $this
            ->actingAs($parent)
            ->post(route('children.store'), [
                'full_name' => '',
                'date_of_birth' => now()
                    ->addDay()
                    ->format('Y-m-d'),
                'gender' => 'invalid-gender',
                'status' => 'invalid-status',
            ]);

        $response->assertSessionHasErrors([
            'full_name',
            'date_of_birth',
            'gender',
            'status',
        ]);

        $this->assertDatabaseCount('children', 0);
    }

    public function test_parent_can_view_own_child(): void
    {
        $parent = $this->createParent();
        $child = $this->createChild($parent);

        $response = $this
            ->actingAs($parent)
            ->get(route('children.show', $child));

        $response->assertOk();
        $response->assertSee('Test Child');
        $response->assertSee('10 May 2020');
    }

    public function test_parent_cannot_manage_another_parents_child(): void
    {
        $firstParent = $this->createParent();
        $secondParent = $this->createParent();

        $child = $this->createChild($secondParent);

        $this->actingAs($firstParent)
            ->get(route('children.show', $child))
            ->assertForbidden();

        $this->actingAs($firstParent)
            ->get(route('children.edit', $child))
            ->assertForbidden();

        $this->actingAs($firstParent)
            ->put(route('children.update', $child), [
                'full_name' => 'Unauthorized Change',
                'date_of_birth' => '2020-05-10',
                'gender' => 'male',
                'status' => 'active',
            ])
            ->assertForbidden();

        $this->actingAs($firstParent)
            ->delete(route('children.destroy', $child))
            ->assertForbidden();

        $this->assertDatabaseHas('children', [
            'child_id' => $child->child_id,
            'full_name' => 'Test Child',
        ]);
    }

    public function test_parent_can_update_own_child(): void
    {
        $parent = $this->createParent();
        $child = $this->createChild($parent);

        $response = $this
            ->actingAs($parent)
            ->put(route('children.update', $child), [
                'full_name' => 'Updated Child Name',
                'date_of_birth' => '2020-05-10',
                'gender' => 'other',
                'allergies' => 'Dust',
                'medical_notes' => null,
                'special_needs' => null,
                'status' => 'inactive',
            ]);

        $response->assertRedirect(
            route('children.show', $child)
        );

        $this->assertDatabaseHas('children', [
            'child_id' => $child->child_id,
            'full_name' => 'Updated Child Name',
            'gender' => 'other',
            'allergies' => 'Dust',
            'status' => 'inactive',
        ]);
    }

    public function test_parent_can_delete_own_child(): void
    {
        $parent = $this->createParent();
        $child = $this->createChild($parent);

        $response = $this
            ->actingAs($parent)
            ->delete(route('children.destroy', $child));

        $response->assertRedirect(route('children.index'));

        $this->assertDatabaseMissing('children', [
            'child_id' => $child->child_id,
        ]);
    }
}