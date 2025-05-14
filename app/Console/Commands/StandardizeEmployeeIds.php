<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Role;

class StandardizeEmployeeIds extends Command
{
    protected $signature = 'employees:standardize-ids';
    
    protected $description = 'Standardize employee IDs based on role names';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function handle()
    {
        $roles = Role::all();
        
        foreach ($roles as $role) {
            $prefix = $this->getRolePrefix($role->name);
            
            $employees = Employee::where('role_id', $role->id)->get();
            $this->info("Processing {$employees->count()} employees with role {$role->name}");
            
            $counter = 1;
            
            foreach ($employees as $employee) {
                $oldId = $employee->employee_id;
                $newId = $prefix . str_pad($counter, 3, '0', STR_PAD_LEFT);
                
                $employee->employee_id = $newId;
                $employee->save();
                
                $this->line("Updated employee ID: {$oldId} -> {$newId} for {$employee->first_name} {$employee->last_name}");
                
                $counter++;
            }
        }
        
        $this->info('Employee IDs standardized successfully.');
    }
    
    private function getRolePrefix($roleName)
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