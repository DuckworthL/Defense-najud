<?php

namespace Database\Seeders;

use App\Models\AttendanceStatus;
use Illuminate\Database\Seeder;

class AttendanceStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Present',
                'description' => 'Employee is present',
                'color_code' => '#28a745', // Green
            ],
            [
                'name' => 'Late',
                'description' => 'Employee arrived late',
                'color_code' => '#ffc107', // Yellow
            ],
            [
                'name' => 'Absent',
                'description' => 'Employee is absent',
                'color_code' => '#dc3545', // Red
            ],
            [
                'name' => 'Half Day',
                'description' => 'Employee worked for half day',
                'color_code' => '#fd7e14', // Orange
            ],
            [
                'name' => 'On Leave',
                'description' => 'Employee is on approved leave',
                'color_code' => '#6c757d', // Grey
            ],
            [
                'name' => 'Holiday',
                'description' => 'Public holiday',
                'color_code' => '#17a2b8', // Blue
            ],
            [
                'name' => 'Weekend',
                'description' => 'Weekend off',
                'color_code' => '#6f42c1', // Purple
            ],
        ];

        foreach ($statuses as $statusData) {
            // Check if the status already exists before creating it
            $existingStatus = AttendanceStatus::where('name', $statusData['name'])->first();
            
            if (!$existingStatus) {
                AttendanceStatus::create($statusData);
            } else {
                // Optionally update existing status if needed
                // $existingStatus->update([
                //     'description' => $statusData['description'], 
                //     'color_code' => $statusData['color_code']
                // ]);
                $this->command->info("Attendance Status '{$statusData['name']}' already exists, skipping.");
            }
        }
    }
}