<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinalReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_print_ready_report(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports.print'))
            ->assertOk()
            ->assertSee('LittleNest Reports & Summary')
            ->assertSee('Caregiver Workload')
            ->assertSee('Save as PDF / Print');
    }

    public function test_parent_cannot_open_admin_print_report(): void
    {
        $parent = User::factory()->create([
            'role' => 'parent',
            'status' => 'active',
        ]);

        $this->actingAs($parent)
            ->get(route('admin.reports.print'))
            ->assertForbidden();
    }
}
