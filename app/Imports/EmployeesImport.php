<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Role;
use App\Models\Shift;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EmployeesImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Find or create department
        $department = Department::firstOrCreate(
            ['name' => $row['department']],
            ['description' => 'Imported from CSV', 'is_active' => true]
        );
        
        // Find role (default to Employee if not found)
        $role = Role::where('name', $row['role'] ?? 'Employee')->first();
        if (!$role) {
            $role = Role::where('name', 'Employee')->first();
        }
        
        // Find shift (default to first shift if not found)
        $shift = Shift::where('name', $row['shift'] ?? '')->first();
        if (!$shift) {
            $shift = Shift::first();
        }
        
        // Generate employee ID if not provided
        $employeeId = $row['employee_id'] ?? $this->generateEmployeeId($department->id);
        
        return new Employee([
            'employee_id' => $employeeId,
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'email' => $row['email'],
            'password' => Hash::make($row['password'] ?? 'Password123!'), // Default password if not provided
            'role_id' => $role->id,
            'department_id' => $department->id,
            'shift_id' => $shift->id,
            'date_hired' => $row['date_hired'] ?? now(),
            'phone' => $row['phone'] ?? null,
            'address' => $row['address'] ?? null,
            'status' => $row['status'] ?? 'active',
        ]);
    }
    
    /**
     * Validation rules.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:employees,email',
            'department' => 'required|string',
        ];
    }
    
    /**
     * Generate unique employee ID.
     */
    private function generateEmployeeId($departmentId)
    {
        // Get department prefix
        $department = Department::find($departmentId);
        $prefix = strtoupper(substr($department->name, 0, 2)); // First 2 letters of department name
        
        // Count employees in this department and add 1
        $employeeCount = Employee::where('department_id', $departmentId)->count() + 1;
        
        // Format: DEPT-YYYY-XXXXX (DEPT=department prefix, YYYY=current year, XXXXX=sequence)
        $employeeId = $prefix . '-' . date('Y') . '-' . str_pad($employeeCount, 5, '0', STR_PAD_LEFT);
        
        // Check if this ID already exists (just in case)
        while (Employee::where('employee_id', $employeeId)->exists()) {
            $employeeCount++;
            $employeeId = $prefix . '-' . date('Y') . '-' . str_pad($employeeCount, 5, '0', STR_PAD_LEFT);
        }
        
        return $employeeId;
    }
}