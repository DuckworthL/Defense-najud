<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Role;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Services\EmployeeIdService;

class EmployeeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roleId = $this->faker->randomElement([1, 2, 3]); // 1=Admin, 2=HR, 3=Employee
        $roleNames = [1 => 'Admin', 2 => 'HR', 3 => 'Employee'];
        
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password123'), // Default password for all seeded accounts
            'role_id' => $roleId,
            'department_id' => Department::inRandomOrder()->first()->id ?? 1,
            'shift_id' => Shift::inRandomOrder()->first()->id ?? 1,
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'profile_picture' => null,
            'date_hired' => $this->faker->dateTimeBetween('-3 years', 'now'),
            'status' => $this->faker->randomElement(['active', 'active', 'active', 'inactive']), // 75% active, 25% inactive
            'created_by' => 1, // Assuming ID 1 is the system admin
            'updated_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the model's role is Admin.
     *
     * @return static
     */
    public function admin()
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => 1,
        ]);
    }

    /**
     * Indicate that the model's role is HR.
     *
     * @return static
     */
    public function hr()
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => 2,
        ]);
    }

    /**
     * Indicate that the model's role is Employee.
     *
     * @return static
     */
    public function employee()
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => 3,
        ]);
    }
}