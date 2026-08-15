<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CaregiverAssignment;
use App\Models\ChildActivity;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentExportWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_can_export_only_own_payment_history(): void
    {
        [$parent, $ownBooking] = $this->createParentBooking('Own Payment Child');
        [, $otherBooking] = $this->createParentBooking('Other Payment Child');

        Payment::create([
            'booking_id' => $ownBooking->booking_id,
            'amount' => 1200,
            'payment_method' => 'mobile-banking',
            'transaction_id' => 'OWN-EXPORT-001',
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        Payment::create([
            'booking_id' => $otherBooking->booking_id,
            'amount' => 1200,
            'payment_method' => 'mobile-banking',
            'transaction_id' => 'OTHER-EXPORT-002',
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($parent)
            ->get(route('payments.export'));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('OWN-EXPORT-001', $content);
        $this->assertStringNotContainsString('OTHER-EXPORT-002', $content);
    }

    public function test_non_parent_cannot_export_payment_history(): void
    {
        $caregiver = User::factory()->create([
            'role' => 'caregiver',
            'status' => 'active',
        ]);

        $this->actingAs($caregiver)
            ->get(route('payments.export'))
            ->assertForbidden();
    }

    public function test_parent_can_download_only_own_activity_summary(): void
    {
        [$parent, $ownActivity] = $this->createParentActivity('Own Activity Child');
        [, $otherActivity] = $this->createParentActivity('Other Activity Child');

        $response = $this->actingAs($parent)
            ->get(route('activities.summary'));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString($ownActivity->details, $content);
        $this->assertStringNotContainsString($otherActivity->details, $content);
    }

    public function test_parent_cannot_download_summary_for_another_parents_child(): void
    {
        [$parent] = $this->createParentActivity('First Activity Child');
        [, $otherActivity] = $this->createParentActivity('Second Activity Child');

        $otherChildId = $otherActivity->assignment->booking->child_id;

        $this->actingAs($parent)
            ->get(route('activities.summary', ['child_id' => $otherChildId]))
            ->assertForbidden();
    }

    private function createParentBooking(string $childName): array
    {
        $parent = User::factory()->create(['role' => 'parent']);
        $profile = $parent->parentProfile()->create();
        $child = $profile->children()->create([
            'full_name' => $childName,
            'date_of_birth' => '2021-01-01',
            'status' => 'active',
        ]);

        $service = Service::create([
            'name' => 'Export Service ' . uniqid(),
            'price' => 1200,
            'duration_minutes' => 120,
            'status' => 'active',
        ]);

        $booking = Booking::create([
            'child_id' => $child->child_id,
            'service_id' => $service->service_id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '09:00',
            'status' => 'confirmed',
            'total_amount' => 1200,
        ]);

        return [$parent, $booking];
    }

    private function createParentActivity(string $childName): array
    {
        [$parent, $booking] = $this->createParentBooking($childName);

        $caregiver = User::factory()->create([
            'role' => 'caregiver',
            'status' => 'active',
        ]);

        $caregiver->caregiverProfile()->create([
            'qualification' => 'Child Care Diploma',
            'experience_years' => 2,
            'availability_status' => 'available',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $assignment = CaregiverAssignment::create([
            'booking_id' => $booking->booking_id,
            'caregiver_id' => $caregiver->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);

        $activity = ChildActivity::create([
            'assignment_id' => $assignment->assignment_id,
            'activity_type' => 'learning',
            'details' => 'Summary details for ' . $childName,
            'activity_time' => now(),
        ]);

        return [$parent, $activity];
    }
}
