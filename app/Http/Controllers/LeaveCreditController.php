<?php

namespace App\Http\Controllers;

use App\Models\LeaveCredit;
use App\Models\LeaveType;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaveCreditController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = LeaveCredit::with(['employee', 'leaveType']);
        
        // Filter by employee if provided
        if ($request->has('employee_id') && $request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }
        
        // Filter by leave type if provided
        if ($request->has('leave_type_id') && $request->leave_type_id) {
            $query->where('leave_type_id', $request->leave_type_id);
        }
        
        // Filter by fiscal year if provided, otherwise use current year
        $fiscalYear = $request->fiscal_year ?? Carbon::now()->year;
        $query->where('fiscal_year', $fiscalYear);
        
        // Calculate remaining days directly in the query (MySQL specific solution)
        $query->selectRaw('leave_credits.*, (leave_credits.allocated_days - leave_credits.used_days) as remaining_days');
        
        $leaveCredits = $query->orderBy('employee_id')->paginate(15);
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();
        
        return view('leave_credits.index', compact('leaveCredits', 'employees', 'leaveTypes', 'fiscalYear'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();
        $defaultYear = Carbon::now()->year;
        
        return view('leave_credits.create', compact('employees', 'leaveTypes', 'defaultYear'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'fiscal_year' => 'required|integer|min:2000|max:2100',
            'allocated_days' => 'required|numeric|min:0.01|max:365',
            'expiry_date' => 'nullable|date|after:today',
        ]);
        
        // Check if a record already exists for this employee, leave type and fiscal year
        $existing = LeaveCredit::where('employee_id', $validated['employee_id'])
            ->where('leave_type_id', $validated['leave_type_id'])
            ->where('fiscal_year', $validated['fiscal_year'])
            ->first();
            
        if ($existing) {
            return back()->withInput()
                ->with('error', 'A leave credit record already exists for this employee, leave type and fiscal year.');
        }
        
        $validated['created_by'] = auth()->id();
        $validated['used_days'] = 0; // Initialize used days to zero
        
        LeaveCredit::create($validated);
        
        return redirect()->route('leave-credits.index')
            ->with('success', 'Leave credit allocated successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveCredit $leaveCredit)
    {
        // Calculate remaining days
        $leaveCredit->remaining_days = $leaveCredit->allocated_days - $leaveCredit->used_days;
        
        return view('leave_credits.show', compact('leaveCredit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveCredit $leaveCredit)
    {
        // Calculate remaining days
        $leaveCredit->remaining_days = $leaveCredit->allocated_days - $leaveCredit->used_days;
        
        return view('leave_credits.edit', compact('leaveCredit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveCredit $leaveCredit)
    {
        $validated = $request->validate([
            'allocated_days' => 'required|numeric|min:' . $leaveCredit->used_days . '|max:365',
            'used_days' => 'required|numeric|min:0|max:' . $request->allocated_days,
            'expiry_date' => 'nullable|date',
        ]);
        
        $validated['updated_by'] = auth()->id();
        
        $leaveCredit->update($validated);
        
        return redirect()->route('leave-credits.index')
            ->with('success', 'Leave credit updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveCredit $leaveCredit)
    {
        // Check if this leave credit has been used
        if ($leaveCredit->used_days > 0) {
            return back()->with('error', 'Cannot delete this leave credit as it has been used.');
        }
        
        $leaveCredit->delete();
        
        return redirect()->route('leave-credits.index')
            ->with('success', 'Leave credit deleted successfully.');
    }
    
    /**
     * Show the form for bulk allocating leave credits.
     */
    public function bulkAllocate()
    {
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $defaultYear = Carbon::now()->year;
        
        return view('leave_credits.bulk_allocate', compact(
            'employees', 
            'leaveTypes', 
            'departments', 
            'defaultYear'
        ));
    }
    
    /**
     * Process the bulk allocation of leave credits.
     */
    public function processBulkAllocate(Request $request)
    {
        // Validation rules
        $rules = [
            'allocation_type' => 'required|in:all,department,selected',
            'leave_type_id' => 'required|exists:leave_types,id',
            'fiscal_year' => 'required|integer|min:2000|max:2100',
            'allocated_days' => 'required|numeric|min:0.01|max:365',
            'expiry_date' => 'nullable|date|after:today',
        ];

        // Add conditional validation rules based on allocation_type
        if ($request->allocation_type === 'department') {
            $rules['department_id'] = 'required|exists:departments,id';
        } elseif ($request->allocation_type === 'selected') {
            $rules['employee_ids'] = 'required|array';
            $rules['employee_ids.*'] = 'exists:employees,id';
        }

        // Validate the request
        $validated = $request->validate($rules);
        
        $employeeQuery = Employee::where('status', 'active');
        
        // Determine which employees to allocate credits to
        switch ($validated['allocation_type']) {
            case 'all':
                // All active employees - no additional filter needed
                break;
                
            case 'department':
                // Employees in a specific department
                $employeeQuery->where('department_id', $validated['department_id']);
                break;
                
            case 'selected':
                // Selected employees only
                $employeeQuery->whereIn('id', $validated['employee_ids']);
                break;
        }
        
        $employees = $employeeQuery->get();
        $counter = 0;
        
        foreach ($employees as $employee) {
            // Check if a record already exists
            $existing = LeaveCredit::where('employee_id', $employee->id)
                ->where('leave_type_id', $validated['leave_type_id'])
                ->where('fiscal_year', $validated['fiscal_year'])
                ->first();
                
            if (!$existing) {
                LeaveCredit::create([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $validated['leave_type_id'],
                    'fiscal_year' => $validated['fiscal_year'],
                    'allocated_days' => $validated['allocated_days'],
                    'used_days' => 0,
                    'expiry_date' => $validated['expiry_date'] ?? null,
                    'created_by' => auth()->id(),
                ]);
                
                $counter++;
            }
        }
        
        return redirect()->route('leave-credits.index')
            ->with('success', "Successfully allocated leave credits to {$counter} employees.");
    }
}