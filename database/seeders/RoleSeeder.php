<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Admin',
                'description' => 'Administrator with full system access',
                'is_active' => true,
            ],
            [
                'name' => 'HR Manager',
                'description' => 'Human Resources Manager with employee management access',
                'is_active' => true,
            ],
            [
                'name' => 'Employee',
                'description' => 'Regular employee with limited access',
                'is_active' => true,
            ],
            [
                'name' => 'Department Head',
                'description' => 'Manager of a specific department',
                'is_active' => true,
            ],
            [
                'name' => 'Supervisor',
                'description' => 'Team supervisor with attendance approval privileges',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            // Check if role already exists before creating it
            $existingRole = Role::where('name', $roleData['name'])->first();
            
            if (!$existingRole) {
                Role::create($roleData);
            } else {
                // Optionally update existing role if needed
                // $existingRole->update(['description' => $roleData['description'], 'is_active' => $roleData['is_active']]);
                $this->command->info("Role '{$roleData['name']}' already exists, skipping.");
            }
        }
    }
}