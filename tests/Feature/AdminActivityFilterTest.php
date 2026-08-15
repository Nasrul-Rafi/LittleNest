<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CaregiverAssignment;
use App\Models\ChildActivity;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminActivityFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_activity_monitoring(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $matching = $this->createActivity(
            $admin,
            'Ariana Filter',
            'Sarah Filter',
            'learning',
            now()->subDay()
        );

        $other = $this->createActivity(
            $admin,
            'Adam Other',
            'Nusrat Other',
            'meal',
            now()->subDays(5)
        );

        $this->actingAs($admin)
            ->get(route('admin.activities.index', [
                'child' => 'Ariana',
                'caregiver' => 'Sarah',
                'booking' => $matching->assignment->booking->booking_reference,
                'activity_type' => 'learn',
                'from_date' => now()->subDays(2)->format('Y-m-d'),
                'to_date' => now()->format('Y-m-d'),
            ]))
            ->assertOk()
            ->assertSee('Ariana Filter')
            ->assertSee('Sarah Filter')
            ->assertSee($matching->assignment->booking->display_reference)
            ->assertDontSee('Adam Other')
            ->assertDontSee('Nusrat Other');
    }

    public function test_parent_cannot_access_admin_activity_monitoring(): void
    {
        $parent = User::factory()->create(['role' => 'parent']);

        $this->actingAs($parent)
            ->get(route('admin.activities.index'))
            ->assertForbidden();
    }

    private function createActivity(
        User $admin,
        string $childName,
        string $caregiverName,
        string $activityType,
        $activityTime
    ): ChildActivity {
        $parent = User::factory()->create(['role' => 'parent']);
        $profile = $parent->parentProfile()->create();
        $child = $profile->children()->create([
            'full_name' => $childName,
            'date_of_birth' => '2021-01-01',
            'status' => 'active',
        ]);

        $service = Service::create([
            'name' => 'Activity Filter Service ' . uniqid(),
            'price' => 900,
            'duration_minutes' => 90,
            'status' => 'active',
        ]);

        $booking = Booking::create([
            'child_id' => $child->child_id,
            'service_id' => $service->service_id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '10:00',
            'status' => 'confirmed',
            'total_amount' => 900,
        ]);

        $caregiver = User::factory()->create([
            'name' => $caregiverName,
            'role' => 'caregiver',
            'status' => 'active',
        ]);

        $caregiver->caregiverProfile()->create([
            'qualification' => 'Care Diploma',
            'experience_years' => 2,
            'availability_status' => 'available',
        ]);

        $assignment = CaregiverAssignment::create([
            'booking_id' => $booking->booking_id,
            'caregiver_id' => $caregiver->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);

        return ChildActivity::create([
            'assignment_id' => $assignment->assignment_id,
            'activity_type' => $activityType,
            'details' => 'Filter details for ' . $childName,
            'activity_time' => $activityTime,
        ]);
    }
}
