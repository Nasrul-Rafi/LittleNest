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
                'name' => 'Hourly Child Care',
                'description' => 'Flexible child care service charged per hour.',
                'price' => 400,
                'duration_minutes' => 60,
                'status' => 'active',
            ],
            [
                'name' => 'Half-Day Child Care',
                'description' => 'Child care service for up to four hours.',
                'price' => 1200,
                'duration_minutes' => 240,
                'status' => 'active',
            ],
            [
                'name' => 'Full-Day Child Care',
                'description' => 'Complete child care service for up to eight hours.',
                'price' => 2000,
                'duration_minutes' => 480,
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
