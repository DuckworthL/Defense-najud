<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'Vacation Leave',
                'description' => 'Annual leave for rest and personal activities',
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Sick Leave',
                'description' => 'Leave for medical reasons, illness or injury',
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Emergency Leave',
                'description' => 'Leave for urgent personal or family matters',
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Bereavement Leave',
                'description' => 'Leave due to death in the immediate family',
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Maternity Leave',
                'description' => 'Leave for pregnant employees before and after childbirth',
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Paternity Leave',
                'description' => 'Leave for male employees after the birth of their child',
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Unpaid Leave',
                'description' => 'Leave without pay for extended absences',
                'is_paid' => false,
                'is_active' => true,
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::create($leaveType);
        }
    }
}