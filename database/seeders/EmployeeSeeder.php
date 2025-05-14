<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        Employee::create([
            'employee_id' => 'ADM001',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role_id' => 1, // Admin role
            'department_id' => 1, // Administration
            'shift_id' => 1, // Morning shift
            'first_name' => 'Admin',
            'last_name' => 'User',
            'phone' => '1234567890',
            'address' => '123 Admin St, City',
            'date_hired' => now()->subYears(2),
            'status' => 'active',
        ]);

        // Create HR user
        Employee::create([
            'employee_id' => 'HR001',
            'email' => 'hr@example.com',
            'password' => Hash::make('password'),
            'role_id' => 2, // HR role
            'department_id' => 2, // Human Resources
            'shift_id' => 1, // Morning shift
            'first_name' => 'HR',
            'last_name' => 'Manager',
            'phone' => '2345678901',
            'address' => '456 HR Blvd, City',
            'date_hired' => now()->subYears(1),
            'status' => 'active',
        ]);

        // Create a regular employee
        Employee::create([
            'employee_id' => 'EMP001',
            'email' => 'employee@example.com',
            'password' => Hash::make('password'),
            'role_id' => 3, // Employee role
            'department_id' => 4, // IT Department
            'shift_id' => 1, // Morning shift
            'first_name' => 'Regular',
            'last_name' => 'Employee',
            'phone' => '3456789012',
            'address' => '789 Worker Ave, City',
            'date_hired' => now()->subMonths(6),
            'status' => 'active',
        ]);
    }
}