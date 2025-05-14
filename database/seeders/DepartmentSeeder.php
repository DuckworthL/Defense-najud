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
                'name' => 'Human Resources',
                'description' => 'HR department',
                'is_active' => true,
            ],
            [
                'name' => 'Finance',
                'description' => 'Finance and accounting department',
                'is_active' => true,
            ],
            [
                'name' => 'Information Technology',
                'description' => 'IT department',
                'is_active' => true,
            ],
            [
                'name' => 'Facilities',
                'description' => 'Facilities management',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}