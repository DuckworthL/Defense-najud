<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the actual columns that exist in the employees table
        $columns = Schema::getColumnListing('employees');
        
        $this->command->info("Available columns in employees table: " . implode(", ", $columns));
        
        // Reset auto-increment to avoid potential ID conflicts (optional)
        // DB::statement('ALTER TABLE employees AUTO_INCREMENT = 1');
        
        // Create 97 employees
        for ($i = 1; $i <= 97; $i++) {
            $firstName = $this->getRandomFirstName();
            $lastName = $this->getRandomLastName();
            $email = strtolower($firstName . '.' . $lastName . $i . '@example.com');
            $password = Hash::make('employee123');
            
            // Check if employee already exists to avoid duplicates
            $existingEmployee = Employee::where('email', $email)
                ->orWhere('employee_id', 'EMP' . str_pad($i, 4, '0', STR_PAD_LEFT))
                ->first();
                
            if ($existingEmployee) {
                $this->command->info("Employee with email {$email} or ID EMP" . str_pad($i, 4, '0', STR_PAD_LEFT) . " already exists, skipping.");
                continue;
            }
            
            // Create user account if users table is used for authentication
            try {
                $user = User::create([
                    'name' => $firstName . ' ' . $lastName,
                    'email' => $email,
                    'password' => $password,
                    'remember_token' => Str::random(10),
                    'email_verified_at' => now(),
                ]);
                $userId = $user->id;
            } catch (\Exception $e) {
                $this->command->error("Error creating user: {$e->getMessage()}");
                $userId = null;
            }
            
            // Prepare required employee data
            $employeeData = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
            ];
            
            // Add password (required based on error message)
            if (in_array('password', $columns)) {
                $employeeData['password'] = $password;
            }
            
            // Add employee_id if the column exists
            if (in_array('employee_id', $columns)) {
                $employeeData['employee_id'] = 'EMP' . str_pad($i, 4, '0', STR_PAD_LEFT);
            }
            
            // Add user_id if the column exists
            if (in_array('user_id', $columns) && $userId) {
                $employeeData['user_id'] = $userId;
            }
            
            // Add role_id if the column exists (default to regular employee role)
            if (in_array('role_id', $columns)) {
                $employeeData['role_id'] = 3; // Assuming 3 is the Employee role ID
            }
            
            // Add phone if the column exists
            if (in_array('phone', $columns)) {
                $employeeData['phone'] = '555' . str_pad($i, 7, rand(1000000, 9999999), STR_PAD_LEFT);
            }
            
            // Add other fields conditionally based on column existence
            $conditionalFields = [
                'gender' => $i % 2 === 0 ? 'Male' : 'Female',
                'date_of_birth' => $this->getRandomBirthDate(),
                'address' => $this->getRandomAddress(),
                'department_id' => rand(1, 5),
                'position' => $this->getRandomPosition(),
                'joined_date' => $this->getRandomJoinDate(),
                'date_hired' => $this->getRandomJoinDate(),
                'shift_id' => rand(1, 3),
                'status' => 'Active',
            ];
            
            foreach ($conditionalFields as $field => $value) {
                if (in_array($field, $columns)) {
                    $employeeData[$field] = $value;
                }
            }
            
            // Create employee with only the columns that exist
            try {
                Employee::create($employeeData);
                
                if ($i % 10 == 0 || $i == 1) {
                    $this->command->info("Created {$i} employees so far");
                }
            } catch (\Exception $e) {
                $this->command->error("Error creating employee {$email}: {$e->getMessage()}");
            }
        }
        
        $this->command->info('Finished creating employees');
    }

    /**
     * Get a random first name
     */
    private function getRandomFirstName(): string
    {
        $firstNames = [
            'James', 'Mary', 'John', 'Patricia', 'Robert', 'Jennifer', 'Michael', 'Linda',
            'William', 'Elizabeth', 'David', 'Barbara', 'Richard', 'Susan', 'Joseph', 'Jessica',
            'Thomas', 'Sarah', 'Charles', 'Karen', 'Christopher', 'Nancy', 'Daniel', 'Lisa',
            'Matthew', 'Betty', 'Anthony', 'Margaret', 'Mark', 'Sandra', 'Donald', 'Ashley',
            'Steven', 'Kimberly', 'Paul', 'Emily', 'Andrew', 'Donna', 'Joshua', 'Michelle',
            'Kenneth', 'Dorothy', 'Kevin', 'Carol', 'Brian', 'Amanda', 'George', 'Melissa'
        ];
        
        return $firstNames[array_rand($firstNames)];
    }

    /**
     * Get a random last name
     */
    private function getRandomLastName(): string
    {
        $lastNames = [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Miller', 'Davis', 'Garcia',
            'Rodriguez', 'Wilson', 'Martinez', 'Anderson', 'Taylor', 'Thomas', 'Hernandez',
            'Moore', 'Martin', 'Jackson', 'Thompson', 'White', 'Lopez', 'Lee', 'Gonzalez',
            'Harris', 'Clark', 'Lewis', 'Robinson', 'Walker', 'Perez', 'Hall', 'Young',
            'Allen', 'Sanchez', 'Wright', 'King', 'Scott', 'Green', 'Baker', 'Adams',
            'Nelson', 'Hill', 'Ramirez', 'Campbell', 'Mitchell', 'Roberts', 'Carter', 'Phillips'
        ];
        
        return $lastNames[array_rand($lastNames)];
    }

    /**
     * Generate a random birth date for an employee (25-60 years old)
     */
    private function getRandomBirthDate(): string
    {
        $year = rand(1965, 2000);
        $month = rand(1, 12);
        $day = rand(1, 28);
        
        return date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
    }

    /**
     * Generate a random join date (between 1-10 years ago)
     */
    private function getRandomJoinDate(): string
    {
        $year = rand(date('Y') - 10, date('Y') - 1);
        $month = rand(1, 12);
        $day = rand(1, 28);
        
        return date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
    }

    /**
     * Get a random position title
     */
    private function getRandomPosition(): string
    {
        $positions = [
            'Software Developer', 'System Administrator', 'Project Manager', 'QA Engineer',
            'UI/UX Designer', 'Network Engineer', 'Database Administrator', 'Business Analyst',
            'DevOps Engineer', 'Technical Writer', 'Support Specialist', 'Product Manager',
            'Marketing Specialist', 'HR Manager', 'Accountant', 'Sales Representative',
            'Customer Service Rep', 'Administrative Assistant'
        ];
        
        return $positions[array_rand($positions)];
    }

    /**
     * Get a random address
     */
    private function getRandomAddress(): string
    {
        $streets = [
            'Main Street', 'Oak Avenue', 'Maple Road', 'Washington Street', 'Park Avenue',
            'Cedar Lane', 'Pine Street', 'Lake Drive', 'Hill Street', 'River Road'
        ];
        
        $cities = [
            'Springfield', 'Riverdale', 'Fairview', 'Madison', 'Georgetown', 'Franklin',
            'Arlington', 'Salem', 'Greenville', 'Kingston'
        ];
        
        $streetNumber = rand(100, 9999);
        $street = $streets[array_rand($streets)];
        $city = $cities[array_rand($cities)];
        $zipCode = rand(10000, 99999);
        
        return "$streetNumber $street, $city, $zipCode";
    }
}