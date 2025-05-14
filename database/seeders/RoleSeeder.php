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
                'name' => 'HR',
                'description' => 'HR personnel with employee and attendance management privileges',
                'is_active' => true,
            ],
            [
                'name' => 'Employee',
                'description' => 'Regular employee with limited access',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}