<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_populates_littlenest_business_tables(): void
    {
        $this->seed();

        $this->assertDatabaseCount('users', 21);
        $this->assertDatabaseCount('parent_profiles', 12);
        $this->assertDatabaseCount('caregiver_profiles', 8);
        $this->assertDatabaseCount('children', 24);
        $this->assertDatabaseCount('services', 6);
        $this->assertDatabaseCount('time_slots', 32);
        $this->assertDatabaseCount('bookings', 40);
        $this->assertDatabaseCount('caregiver_assignments', 24);
        $this->assertDatabaseCount('child_activities', 98);
        $this->assertDatabaseCount('payments', 23);
        $this->assertDatabaseCount('booking_requests', 10);
        $this->assertDatabaseCount('contact_messages', 10);

        $parent = User::where('email', 'parent1@littlenest.test')->firstOrFail();
        $caregiver = User::where('email', 'caregiver1@littlenest.test')->firstOrFail();
        $admin = User::where('email', 'admin@littlenest.test')->firstOrFail();

        $this->assertTrue(Hash::check('12345678', $parent->password));
        $this->assertTrue(Hash::check('12345678', $caregiver->password));
        $this->assertTrue(Hash::check('12345678', $admin->password));
    }

    public function test_demo_seeder_can_run_twice_without_duplicate_demo_records(): void
    {
        $this->seed();

        $firstCounts = $this->demoCounts();

        $this->seed();

        $this->assertSame($firstCounts, $this->demoCounts());
    }

    private function demoCounts(): array
    {
        return [
            'users' => User::where('email', 'like', '%@littlenest.test')->count(),
            'children' => DB::table('children')->count(),
            'time_slots' => DB::table('time_slots')->count(),
            'bookings' => DB::table('bookings')->count(),
            'assignments' => DB::table('caregiver_assignments')->count(),
            'activities' => DB::table('child_activities')->count(),
            'payments' => DB::table('payments')->count(),
            'requests' => DB::table('booking_requests')->count(),
            'messages' => DB::table('contact_messages')->count(),
        ];
    }
}
