<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Role;

class EmployeeIdService
{
    /**
     * Generate a new employee ID based on role
     */
    public static function generateEmployeeId($roleId)
    {
        $role = Role::find($roleId);
        
        if (!$role) {
            throw new \Exception('Invalid role ID');
        }
        
        $prefix = self::getRolePrefix($role->name);
        $lastEmployee = Employee::where('employee_id', 'like', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(employee_id, ' . (strlen($prefix) + 1) . ') as UNSIGNED) DESC')
            ->first();
        
        if ($lastEmployee) {
            // Extract the numeric part and increment
            $numericPart = (int) substr($lastEmployee->employee_id, strlen($prefix));
            $nextNumber = $numericPart + 1;
        } else {
            // Start with 001
            $nextNumber = 1;
        }
        
        // Format with leading zeros
        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get prefix based on role name
     */
    private static function getRolePrefix($roleName)
    {
        switch (strtolower($roleName)) {
            case 'admin':
                return 'ADM';
            case 'hr':
                return 'HR';
            case 'employee':
                return 'EMP';
            default:
                // For other roles, use first 3 letters
                return strtoupper(substr($roleName, 0, 3));
        }
    }
}