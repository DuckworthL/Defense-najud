<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Role;
use App\Models\Shift;
use App\Models\Leave;
use App\Models\LeaveCredit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the employees.
     */
    /**
     * Display a listing of employees with search functionality.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = \App\Models\Employee::with(['department', 'role'])
            ->orderBy('created_at', 'desc');
        
        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('first_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('employee_id', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('phone', 'LIKE', "%{$searchTerm}%");
            });
        }
        
        // Add department filter if provided
        if ($request->has('department') && $request->department != '') {
            $query->where('department_id', $request->department);
        }
        
        // Add role/position filter if provided
        if ($request->has('role') && $request->role != '') {
            $query->where('role_id', $request->role);
        }
        
        $employees = $query->paginate(15);
        
        // Get all departments and roles for the filter dropdowns
        $departments = \App\Models\Department::orderBy('name')->get();
        $roles = \App\Models\Role::orderBy('name')->get();
        
        return view('employees.index', compact('employees', 'departments', 'roles'));
    }
    
    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        $departments = Department::all();
        $roles = Role::all();
        $shifts = Shift::all();
        
        return view('employees.create', compact('departments', 'roles', 'shifts'));
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:employees',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'required|exists:departments,id',
            'shift_id' => 'required|exists:shifts,id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'date_hired' => 'required|date',
            'status' => 'required|in:active,inactive',
        ]);
        
        try {
            // Handle profile picture upload
            $profilePicturePath = null;
            if ($request->hasFile('profile_picture')) {
                $profilePicturePath = $request->file('profile_picture')
                    ->store('profile_pictures', 'public');
            }
            
            // Generate employee ID based on role
            $employeeId = $this->generateEmployeeId($request->role_id);
            
            $employee = Employee::create([
                'employee_id' => $employeeId,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id,
                'department_id' => $request->department_id,
                'shift_id' => $request->shift_id,
                'phone' => $request->phone,
                'address' => $request->address,
                'profile_picture' => $profilePicturePath,
                'date_hired' => $request->date_hired,
                'status' => $request->status,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
            
            return redirect()->route('employees.index')
                ->with('success', 'Employee created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating employee: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified employee.
     */
    public function show($id)
    {
        $employee = Employee::with(['department', 'role', 'shift'])
            ->findOrFail($id);
            
        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        $departments = Department::all();
        $roles = Role::all();
        $shifts = Shift::all();
        
        return view('employees.edit', compact('employee', 'departments', 'roles', 'shifts'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('employees')->ignore($employee->id),
            ],
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'required|exists:departments,id',
            'shift_id' => 'required|exists:shifts,id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'date_hired' => 'required|date',
            'status' => 'required|in:active,inactive',
        ]);
        
        try {
            $data = $request->except(['_token', '_method', 'password', 'password_confirmation', 'profile_picture']);
            
            // Update password if provided
            if ($request->filled('password')) {
                $request->validate([
                    'password' => 'required|string|min:8|confirmed',
                ]);
                
                $data['password'] = Hash::make($request->password);
            }
            
            // Handle profile picture update
            if ($request->hasFile('profile_picture')) {
                // Delete old profile picture if exists
                if ($employee->profile_picture) {
                    Storage::disk('public')->delete($employee->profile_picture);
                }
                
                $data['profile_picture'] = $request->file('profile_picture')
                    ->store('profile_pictures', 'public');
            }
            
            $data['updated_by'] = auth()->id();
            
            $employee->update($data);
            
            return redirect()->route('employees.index')
                ->with('success', 'Employee updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating employee: ' . $e->getMessage());
        }
    }

    /**
     * Archive the specified employee (soft delete).
     */
    public function destroy($id)
    {
        try {
            $employee = Employee::findOrFail($id);
            $employee->delete(); // This will soft delete the record
            
            return redirect()->route('employees.index')
                ->with('success', 'Employee archived successfully');
        } catch (\Exception $e) {
            return redirect()->route('employees.index')
                ->with('error', 'Error archiving employee: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of archived employees with search functionality.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function archive(Request $request)
    {
        try {
            $query = Employee::onlyTrashed()
                ->with(['department', 'role', 'shift'])
                ->orderBy('deleted_at', 'desc');
            
            // Search functionality
            if ($request->has('search') && $request->search != '') {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('first_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('employee_id', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('phone', 'LIKE', "%{$searchTerm}%");
                });
            }
            
            // Add department filter if provided
            if ($request->has('department') && $request->department != '') {
                $query->where('department_id', $request->department);
            }
            
            // Add role/position filter if provided
            if ($request->has('role') && $request->role != '') {
                $query->where('role_id', $request->role);
            }
            
            $employees = $query->paginate(15);
            
            // Get all departments and roles for the filter dropdowns
            $departments = Department::orderBy('name')->get();
            $roles = Role::orderBy('name')->get();
            
            return view('employees.archive', compact('employees', 'departments', 'roles'));
            
        } catch (\Exception $e) {
            return redirect()->route('employees.index')
                ->with('error', 'Error loading archived employees: ' . $e->getMessage());
        }
    }

   /**
    * Restore the specified resource from archive.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function restore($id)
    {
        try {
            // Find the soft-deleted employee
            $employee = \App\Models\Employee::withTrashed()->findOrFail($id);
            
            // Check if the employee was found and is actually trashed
            if (!$employee || !$employee->trashed()) {
                \Log::warning('Employee not found or not trashed: ' . $id);
                return redirect()->route('employees.archive')
                    ->with('error', 'Employee could not be restored. Not found or not archived.');
            }
            
            // Attempt to restore the employee
            $result = $employee->restore();
            
            // Log the result for debugging
            \Log::info('Employee restore attempt for ID ' . $id . ': ' . ($result ? 'Success' : 'Failed'));
            
            if ($result) {
                return redirect()->route('employees.archive')
                    ->with('success', 'Employee restored successfully.');
            } else {
                return redirect()->route('employees.archive')
                    ->with('error', 'Failed to restore employee.');
            }
        } catch (\Exception $e) {
            // Log the exception
            \Log::error('Error restoring employee: ' . $e->getMessage());
            return redirect()->route('employees.archive')
                ->with('error', 'Error restoring employee: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete an employee.
     */
    public function forceDelete($id)
    {
        try {
            $employee = Employee::withTrashed()->findOrFail($id);
            
            // Delete profile picture if exists
            if ($employee->profile_picture) {
                Storage::disk('public')->delete($employee->profile_picture);
            }
            
            $employee->forceDelete(); // This will permanently delete the record
            
            return redirect()->route('employees.archive')
                ->with('success', 'Employee permanently deleted successfully');
        } catch (\Exception $e) {
            return redirect()->route('employees.archive')
                ->with('error', 'Error permanently deleting employee: ' . $e->getMessage());
        }
    }
    
    /**
     * Bulk restore employees from archive.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkRestore(Request $request)
    {
        // Validate the request
        $request->validate([
            'selected_ids' => 'required|array',
            'selected_ids.*' => 'exists:employees,id'
        ]);
        
        if (empty($request->selected_ids)) {
            return redirect()->route('employees.archive')
                ->with('error', 'No employees selected for restoration.');
        }
        
        try {
            $totalSelected = count($request->selected_ids);
            $successCount = 0;
            $failedIds = [];
            
            // Process each employee restoration
            foreach ($request->selected_ids as $id) {
                $employee = Employee::withTrashed()->find($id);
                
                if ($employee && $employee->trashed()) {
                    try {
                        $result = $employee->restore();
                        
                        if ($result) {
                            $successCount++;
                            
                            // Log successful restore
                            \Log::info('Employee restored successfully', [
                                'employee_id' => $id,
                                'name' => $employee->first_name . ' ' . $employee->last_name,
                                'restored_by' => auth()->user()->id
                            ]);
                        } else {
                            $failedIds[] = $id;
                            
                            // Log failed restore
                            \Log::warning('Failed to restore employee', [
                                'employee_id' => $id,
                                'name' => $employee->first_name . ' ' . $employee->last_name
                            ]);
                        }
                    } catch (\Exception $e) {
                        $failedIds[] = $id;
                        
                        // Log exception
                        \Log::error('Exception while restoring employee', [
                            'employee_id' => $id,
                            'exception' => $e->getMessage()
                        ]);
                    }
                } else {
                    $failedIds[] = $id;
                    
                    // Log not found or not trashed
                    \Log::warning('Employee not found or not trashed during bulk restore', [
                        'employee_id' => $id
                    ]);
                }
            }
            
            // Prepare response message based on results
            if ($successCount === $totalSelected) {
                return redirect()->route('employees.archive')
                    ->with('success', "All $successCount employees have been successfully restored.");
            } elseif ($successCount > 0) {
                return redirect()->route('employees.archive')
                    ->with('warning', "$successCount out of $totalSelected employees were restored. Some employees could not be restored.");
            } else {
                return redirect()->route('employees.archive')
                    ->with('error', 'Failed to restore the selected employees. Please try again or contact support.');
            }
        } catch (\Exception $e) {
            \Log::error('Error in bulk restore operation', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('employees.archive')
                ->with('error', 'An error occurred during the restore operation: ' . $e->getMessage());
        }
    }

    /**
     * Display employee leave credits dashboard.
     */
    public function leaveCredits()
    {
        $employeeId = auth()->id();
        $currentYear = Carbon::now()->year;
        
        // Get all leave credits for the current year
        $leaveCredits = LeaveCredit::where('employee_id', $employeeId)
            ->where('fiscal_year', $currentYear)
            ->with('leaveType')
            ->get();
            
        // Get leave statistics
        $leaves = Leave::where('employee_id', $employeeId)
            ->whereYear('start_date', $currentYear)
            ->with('leaveType')
            ->get();
        
        $totalLeaveDays = 0;
        $withoutPayLeaveDays = 0;
        
        foreach ($leaves as $leave) {
            if ($leave->status === 'approved') {
                $days = $leave->days_count;
                $totalLeaveDays += $days;
                
                if ($leave->is_without_pay) {
                    $withoutPayLeaveDays += $days;
                }
            }
        }
        
        // Get pending leave count
        $pendingLeaves = Leave::where('employee_id', $employeeId)
            ->where('status', 'pending')
            ->count();
            
        // Get recent leave history
        $recentLeaves = Leave::where('employee_id', $employeeId)
            ->with('leaveType')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        return view('employees.leave_credits', compact(
            'leaveCredits',
            'totalLeaveDays',
            'withoutPayLeaveDays',
            'pendingLeaves',
            'recentLeaves'
        ));
    }

    /**
     * Change employee password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request, Employee $employee)
    {
        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $employee->password = Hash::make($request->new_password);
            $employee->save();
            
            return redirect()->route('employees.show', $employee->id)
                ->with('success', 'Password has been changed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error changing password: ' . $e->getMessage());
        }
    }

    /**
     * Show import form.
     */
    public function importForm()
    {
        return view('employees.import-form');
    }

    /**
     * Import employees from CSV/Excel.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:2048',
        ]);

        try {
            $file = $request->file('file');
            
            // Process the import
            $import = new \App\Imports\EmployeesImport;
            \Maatwebsite\Excel\Facades\Excel::import($import, $file);
            
            $importedCount = $import->getImportedCount();
            
            return redirect()->route('employees.index')
                ->with('success', "$importedCount employees imported successfully.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error importing employees: ' . $e->getMessage());
        }
    }

    /**
     * Bulk permanently delete employees.
     */
    public function bulkForceDelete(Request $request)
    {
        $request->validate([
            'selected_ids' => 'required|array',
            'selected_ids.*' => 'exists:employees,id'
        ]);
        
        try {
            // Get employees to delete their profile pictures
            $employees = Employee::withTrashed()
                ->whereIn('id', $request->selected_ids)
                ->get();
                
            // Delete profile pictures
            foreach($employees as $employee) {
                if ($employee->profile_picture) {
                    Storage::disk('public')->delete($employee->profile_picture);
                }
            }
            
            // Force delete records
            Employee::withTrashed()
                ->whereIn('id', $request->selected_ids)
                ->forceDelete();
            
            return redirect()->route('employees.archive')
                ->with('success', count($request->selected_ids) . ' employees permanently deleted successfully');
        } catch (\Exception $e) {
            return redirect()->route('employees.archive')
                ->with('error', 'Error permanently deleting employees: ' . $e->getMessage());
        }
    }

    /**
     * Generate a unique employee ID based on role.
     */
    private function generateEmployeeId($roleId)
    {
        $prefix = '';
        
        // Determine prefix based on role
        switch ($roleId) {
            case 1: // Admin
                $prefix = 'ADM';
                $lastId = Employee::where('role_id', 1)
                    ->where('employee_id', 'LIKE', 'ADM%')
                    ->max(\DB::raw('CAST(SUBSTRING(employee_id, 4) AS UNSIGNED)'));
                break;
            case 2: // HR
                $prefix = 'HR';
                $lastId = Employee::where('role_id', 2)
                    ->where('employee_id', 'LIKE', 'HR%')
                    ->max(\DB::raw('CAST(SUBSTRING(employee_id, 3) AS UNSIGNED)'));
                break;
            default: // Regular Employee
                $prefix = 'EMP';
                $lastId = Employee::where('role_id', 3)
                    ->where('employee_id', 'LIKE', 'EMP%')
                    ->max(\DB::raw('CAST(SUBSTRING(employee_id, 4) AS UNSIGNED)'));
                break;
        }
        
        $nextId = ($lastId ?? 0) + 1;
        
        // Format ID with leading zeros
        return $prefix . str_pad($nextId, 3, '0', STR_PAD_LEFT);
    }
}