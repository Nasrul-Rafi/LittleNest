<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOperationalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_new_operational_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.parents.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.children.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.activities.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.inquiries.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.reports.index'))->assertOk();
    }

    public function test_parent_cannot_access_new_admin_pages(): void
    {
        $parent = User::factory()->create(['role' => 'parent']);

        $this->actingAs($parent)->get(route('admin.parents.index'))->assertForbidden();
        $this->actingAs($parent)->get(route('admin.children.index'))->assertForbidden();
        $this->actingAs($parent)->get(route('admin.activities.index'))->assertForbidden();
        $this->actingAs($parent)->get(route('admin.inquiries.index'))->assertForbidden();
        $this->actingAs($parent)->get(route('admin.reports.index'))->assertForbidden();
    }
}
