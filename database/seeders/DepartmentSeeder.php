<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Administration',
                'description' => 'Administrative staff',
                'is_active' => true,
            ],
            [
                'name' => 'Information Technology',
                'description' => 'IT support and development',
                'is_active' => true,
            ],
            [
                'name' => 'Human Resources',
                'description' => 'HR management and recruitment',
                'is_active' => true,
            ],
            [
                'name' => 'Marketing',
                'description' => 'Marketing and promotional activities',
                'is_active' => true,
            ],
            [
                'name' => 'Finance',
                'description' => 'Accounts and financial management',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $deptData) {
            // Check if department already exists before creating it
            $existingDept = Department::where('name', $deptData['name'])->first();
            
            if (!$existingDept) {
                Department::create($deptData);
            } else {
                // Optionally update existing department if needed
                // $existingDept->update(['description' => $deptData['description'], 'is_active' => $deptData['is_active']]);
                $this->command->info("Department '{$deptData['name']}' already exists, skipping.");
            }
        }
    }
}