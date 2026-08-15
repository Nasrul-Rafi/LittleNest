<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportEnhancementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_report_by_booking_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->createBooking('2026-08-20', 'In Range Child');
        $this->createBooking('2026-09-20', 'Outside Range Child');

        $response = $this->actingAs($admin)
            ->get(route('admin.reports.index', [
                'from_date' => '2026-08-01',
                'to_date' => '2026-08-31',
            ]));

        $response->assertOk();
        $response->assertViewHas('summary', function ($summary) {
            return $summary['total_bookings'] === 1;
        });
    }

    public function test_booking_csv_respects_date_filter(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $inside = $this->createBooking(
            '2026-08-20',
            'CSV In Range Child'
        );

        $outside = $this->createBooking(
            '2026-09-20',
            'CSV Outside Range Child'
        );

        $response = $this->actingAs($admin)
            ->get(route('admin.reports.bookings-csv', [
                'from_date' => '2026-08-01',
                'to_date' => '2026-08-31',
            ]));

        $response->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString(
            $inside->display_reference,
            $content
        );

        $this->assertStringNotContainsString(
            $outside->display_reference,
            $content
        );
    }

    private function createBooking(
        string $date,
        string $childName
    ): Booking {
        $parent = User::factory()->create(['role' => 'parent']);
        $profile = $parent->parentProfile()->create();

        $child = $profile->children()->create([
            'full_name' => $childName,
            'date_of_birth' => '2021-05-10',
            'gender' => 'female',
            'status' => 'active',
        ]);

        $service = Service::create([
            'name' => 'Report Service ' . uniqid(),
            'price' => 900,
            'duration_minutes' => 120,
            'status' => 'active',
        ]);

        return $child->bookings()->create([
            'service_id' => $service->service_id,
            'booking_date' => $date,
            'booking_time' => '10:00',
            'status' => 'confirmed',
            'total_amount' => 900,
        ]);
    }
}
