<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'Full-Day Care',
                'description' => 'A structured full-day care service with guided play, rest and regular parent updates.',
                'min_age' => 2,
                'max_age' => 6,
                'price' => 1800,
                'duration_minutes' => 540,
                'status' => 'active',
            ],
            [
                'name' => 'Half-Day Care',
                'description' => 'Morning or afternoon child care for families who need a shorter care period.',
                'min_age' => 2,
                'max_age' => 8,
                'price' => 1050,
                'duration_minutes' => 270,
                'status' => 'active',
            ],
            [
                'name' => 'Hourly Care',
                'description' => 'Flexible child care for appointments, errands and short family schedules.',
                'min_age' => 2,
                'max_age' => 10,
                'price' => 350,
                'duration_minutes' => 120,
                'status' => 'active',
            ],
            [
                'name' => 'Weekend Care',
                'description' => 'Saturday child care subject to available time slots and caregiver capacity.',
                'min_age' => 2,
                'max_age' => 10,
                'price' => 2000,
                'duration_minutes' => 480,
                'status' => 'active',
            ],
            [
                'name' => 'Learning & Play',
                'description' => 'Creative guided activities that support learning, play and child development.',
                'min_age' => 3,
                'max_age' => 8,
                'price' => 600,
                'duration_minutes' => 120,
                'status' => 'active',
            ],
            [
                'name' => 'Emergency Care',
                'description' => 'Short-notice child care that is available only when capacity and caregivers permit.',
                'min_age' => 2,
                'max_age' => 10,
                'price' => 500,
                'duration_minutes' => 120,
                'status' => 'active',
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['name' => $service['name']],
                $service
            );
        }
    }
}
