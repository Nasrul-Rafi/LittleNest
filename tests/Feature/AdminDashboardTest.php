<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_cannot_access_admin_dashboard(): void
    {
        $parent = User::factory()->create([
            'role' => 'parent',
        ]);

        $this->actingAs($parent)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_dashboard_route_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_sees_dashboard_summaries(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $caregiver = User::factory()->create([
            'role' => 'caregiver',
            'status' => 'active',
        ]);

        $caregiver->caregiverProfile()->create([
            'qualification' => 'Diploma in Child Care',
            'experience_years' => 3,
            'availability_status' => 'available',
        ]);

        Service::create([
            'name' => 'Active Dashboard Service',
            'price' => 1000,
            'duration_minutes' => 120,
            'status' => 'active',
        ]);

        Service::create([
            'name' => 'Inactive Dashboard Service',
            'price' => 800,
            'duration_minutes' => 60,
            'status' => 'inactive',
        ]);

        $parent = User::factory()->create([
            'role' => 'parent',
        ]);

        $parentProfile = $parent->parentProfile()->create();

        $child = $parentProfile->children()->create([
            'full_name' => 'Dashboard Child',
            'date_of_birth' => '2021-05-10',
            'gender' => 'female',
            'status' => 'active',
        ]);

        $service = Service::where(
            'name',
            'Active Dashboard Service'
        )->first();

        $pendingBooking = $child->bookings()->create([
            'service_id' => $service->service_id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '10:00',
            'status' => 'pending',
            'total_amount' => 1000,
        ]);

        $confirmedBooking = $child->bookings()->create([
            'service_id' => $service->service_id,
            'booking_date' => now()->addDays(2)->format('Y-m-d'),
            'booking_time' => '11:00',
            'status' => 'confirmed',
            'total_amount' => 1000,
        ]);

        Payment::create([
            'booking_id' => $confirmedBooking->booking_id,
            'amount' => 1000,
            'payment_method' => 'cash',
            'payment_status' => 'pending',
        ]);

        Payment::create([
            'booking_id' => $pendingBooking->booking_id,
            'amount' => 500,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Admin Dashboard');
        $response->assertSee('Dashboard Child');
        $response->assertSee('৳500.00');
        $response->assertViewHas('pendingBookingCount', 1);
        $response->assertViewHas('confirmedBookingCount', 1);
        $response->assertViewHas('activeCaregiverCount', 1);
        $response->assertViewHas('activeServiceCount', 1);
        $response->assertViewHas('pendingPaymentCount', 1);
    }
}
