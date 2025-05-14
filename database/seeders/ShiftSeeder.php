<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'Morning Shift',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'grace_period_minutes' => 15,
                'description' => 'Standard morning shift',
                'is_active' => true,
            ],
            [
                'name' => 'Afternoon Shift',
                'start_time' => '12:00:00',
                'end_time' => '20:00:00',
                'grace_period_minutes' => 15,
                'description' => 'Afternoon shift',
                'is_active' => true,
            ],
            [
                'name' => 'Night Shift',
                'start_time' => '20:00:00',
                'end_time' => '04:00:00',
                'grace_period_minutes' => 15,
                'description' => 'Night shift',
                'is_active' => true,
            ],
            [
                'name' => 'Flexible Hours',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'grace_period_minutes' => 30,
                'description' => 'Flexible working hours with extended grace period',
                'is_active' => true,
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::create($shift);
        }
    }
}