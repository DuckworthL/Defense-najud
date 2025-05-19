<?php

namespace Database\Seeders;

use App\Models\LeaveType;
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
                'description' => 'Leave due to illness or medical appointments',
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Personal Leave',
                'description' => 'Leave for personal matters',
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Maternity Leave',
                'description' => 'Leave for childbirth and care for newborns',
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Paternity Leave',
                'description' => 'Leave for fathers after the birth of their child',
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Bereavement Leave',
                'description' => 'Leave due to death of a family member',
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

        foreach ($leaveTypes as $leaveTypeData) {
            // Check if leave type already exists before creating it
            $existingLeaveType = LeaveType::where('name', $leaveTypeData['name'])->first();
            
            if (!$existingLeaveType) {
                LeaveType::create($leaveTypeData);
            } else {
                // Optionally update existing leave type if needed
                // $existingLeaveType->update([
                //     'description' => $leaveTypeData['description'],
                //     'is_paid' => $leaveTypeData['is_paid'],
                //     'is_active' => $leaveTypeData['is_active']
                // ]);
                $this->command->info("Leave Type '{$leaveTypeData['name']}' already exists, skipping.");
            }
        }
    }
}