<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\LeaveCredit;
use App\Models\Employee;
use App\Models\Department;
use App\Services\LeaveCreditService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveController extends Controller
{
    protected $leaveCreditService;
    
    public function __construct(LeaveCreditService $leaveCreditService)
    {
        $this->leaveCreditService = $leaveCreditService;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Leave::with(['employee', 'leaveType', 'approver']);
        
        // Filter by employee if provided
        if ($request->has('employee_id') && $request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }
        
        // Filter by leave type if provided
        if ($request->has('leave_type_id') && $request->leave_type_id) {
            $query->where('leave_type_id', $request->leave_type_id);
        }
        
        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range if provided
        if ($request->has('start_date') && $request->start_date) {
            $query->where('start_date', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->where('end_date', '<=', $request->end_date);
        }
        
        // For regular employees, only show their own leave requests
        if (!auth()->user()->isAdmin() && !auth()->user()->isHR()) {
            $query->where('employee_id', auth()->id());
        }
        
        $leaves = $query->orderBy('created_at', 'desc')->paginate(15);
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();
        
        return view('leaves.index', compact('leaves', 'employees', 'leaveTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = [];
        if (auth()->user()->isAdmin() || auth()->user()->isHR()) {
            $employees = Employee::where('status', 'active')->orderBy('first_name')->get();
        }
        
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();
        
        // Get leave credits for the current user
        $leaveCredits = LeaveCredit::where('employee_id', auth()->id())
            ->where('fiscal_year', Carbon::now()->year)
            ->with('leaveType')
            ->get();
            
        // Set default year for view
        $defaultYear = Carbon::now()->year;
        
        return view('leaves.create', compact('employees', 'leaveTypes', 'leaveCredits', 'defaultYear'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'with_pay_days' => 'nullable|numeric|min:0',
            'without_pay_days' => 'nullable|numeric|min:0',
        ]);
        
        // Calculate credit details
        $creditDetails = $this->leaveCreditService->calculateLeaveCredits(
            $validated['employee_id'],
            $validated['leave_type_id'],
            $validated['start_date'],
            $validated['end_date']
        );
        
        // Set default values for split payment
        $withPayDays = $request->input('with_pay_days') !== null 
            ? $request->input('with_pay_days') 
            : $creditDetails['with_pay_days'];
            
        $withoutPayDays = $request->input('without_pay_days') !== null 
            ? $request->input('without_pay_days') 
            : $creditDetails['without_pay_days'];
            
        $isWithoutPay = $withoutPayDays > 0;
        
        // Create the leave request
        $leave = Leave::create([
            'employee_id' => $validated['employee_id'],
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'status' => 'pending',
            'is_without_pay' => $isWithoutPay,
            'with_pay_days' => round($withPayDays, 2),
            'without_pay_days' => round($withoutPayDays, 2),
            'user_agent' => $request->userAgent(),
        ]);
        
        return redirect()->route('leaves.index')
            ->with('success', 'Leave request submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Use findOrFail and eager load relationships
        $leave = Leave::with(['employee', 'employee.department', 'employee.role', 'leaveType', 'approver'])
            ->findOrFail($id);
            
        // For regular employees, ensure they can only view their own leaves
        if (!auth()->user()->isAdmin() && !auth()->user()->isHR() && $leave->employee_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }
        
        return view('leaves.show', compact('leave'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $leave = Leave::with(['employee', 'leaveType'])->findOrFail($id);
        
        // Only allow editing of pending leaves
        if ($leave->status !== 'pending') {
            return redirect()->route('leaves.show', $leave->id)
                ->with('error', 'Cannot edit a leave request that has already been processed.');
        }
        
        // For regular employees, ensure they can only edit their own leaves
        if (!auth()->user()->isAdmin() && !auth()->user()->isHR() && $leave->employee_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }
        
        $employees = [];
        if (auth()->user()->isAdmin() || auth()->user()->isHR()) {
            $employees = Employee::where('status', 'active')->orderBy('first_name')->get();
        }
        
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();
        
        // Get leave credits for the employee
        $employeeId = $leave->employee_id;
        $leaveCredits = LeaveCredit::where('employee_id', $employeeId)
            ->where('fiscal_year', Carbon::now()->year)
            ->with('leaveType')
            ->get();
        
        // Calculate credit details for current leave
        $creditDetails = $this->leaveCreditService->calculateLeaveCredits(
            $leave->employee_id,
            $leave->leave_type_id,
            $leave->start_date,
            $leave->end_date
        );
        
        return view('leaves.edit', compact('leave', 'employees', 'leaveTypes', 'leaveCredits', 'creditDetails'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);
        
        // Only allow updating of pending leaves
        if ($leave->status !== 'pending') {
            return redirect()->route('leaves.show', $leave->id)
                ->with('error', 'Cannot update a leave request that has already been processed.');
        }
        
        // For regular employees, ensure they can only update their own leaves
        if (!auth()->user()->isAdmin() && !auth()->user()->isHR() && $leave->employee_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }
        
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'with_pay_days' => 'nullable|numeric|min:0',
            'without_pay_days' => 'nullable|numeric|min:0',
        ]);
        
        // Only admin/HR can change these fields
        if (auth()->user()->isAdmin() || auth()->user()->isHR()) {
            $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'leave_type_id' => 'required|exists:leave_types,id',
            ]);
            
            $leave->employee_id = $request->employee_id;
            $leave->leave_type_id = $request->leave_type_id;
        }
        
        // Calculate credit details
        $creditDetails = $this->leaveCreditService->calculateLeaveCredits(
            $leave->employee_id,
            $leave->leave_type_id,
            $validated['start_date'],
            $validated['end_date']
        );
        
        // Set default values for split payment
        $withPayDays = $request->input('with_pay_days') !== null 
            ? $request->input('with_pay_days') 
            : $creditDetails['with_pay_days'];
            
        $withoutPayDays = $request->input('without_pay_days') !== null 
            ? $request->input('without_pay_days') 
            : $creditDetails['without_pay_days'];
            
        $isWithoutPay = $withoutPayDays > 0;
        
        // Update the leave request
        $leave->start_date = $validated['start_date'];
        $leave->end_date = $validated['end_date'];
        $leave->reason = $validated['reason'];
        $leave->is_without_pay = $isWithoutPay;
        $leave->with_pay_days = round($withPayDays, 2);
        $leave->without_pay_days = round($withoutPayDays, 2);
        $leave->save();
        
        return redirect()->route('leaves.show', $leave->id)
            ->with('success', 'Leave request updated successfully.');
    }

    /**
     * Approve the specified leave request.
     */
    public function approve(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);
        
        // Only allow admins and HR to approve leaves
        if (!auth()->user()->isAdmin() && !auth()->user()->isHR()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Can only approve pending leaves
        if ($leave->status !== 'pending') {
            return back()->with('error', 'This leave request has already been processed.');
        }
        
        // Begin transaction
        DB::beginTransaction();
        
        try {
            // Update leave request status
            $leave->status = 'approved';
            $leave->approved_by = auth()->id();
            $leave->approved_at = now();
            
            // Process the leave credits if paid leave
            if ($leave->leaveType->is_paid) {
                $success = $this->leaveCreditService->processLeaveCredits($leave);
                if (!$success) {
                    throw new \Exception('Failed to process leave credits');
                }
            }
            
            $leave->save();
            
            // Commit transaction
            DB::commit();
            
            return redirect()->route('leaves.show', $leave)
                ->with('success', 'Leave request approved successfully.');
        } catch (\Exception $e) {
            // Rollback in case of error
            DB::rollBack();
            return back()->with('error', 'An error occurred while processing the leave approval: ' . $e->getMessage());
        }
    }

    /**
     * Reject the specified leave request.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'required|string|max:1000',
        ]);
        
        $leave = Leave::findOrFail($id);
        
        // Only allow admins and HR to reject leaves
        if (!auth()->user()->isAdmin() && !auth()->user()->isHR()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Can only reject pending leaves
        if ($leave->status !== 'pending') {
            return back()->with('error', 'This leave request has already been processed.');
        }
        
        // Update leave request status
        $leave->status = 'rejected';
        $leave->approved_by = auth()->id();
        $leave->approved_at = now();
        $leave->remarks = $request->remarks;
        $leave->save();
        
        return redirect()->route('leaves.show', $leave)
            ->with('success', 'Leave request rejected successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $leave = Leave::findOrFail($id);
        
        // Only allow admins, HR, or the employee who created the leave request to cancel
        if (!auth()->user()->isAdmin() && !auth()->user()->isHR() && $leave->employee_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Can only cancel pending leaves
        if ($leave->status !== 'pending') {
            return back()->with('error', 'Cannot cancel leaves that have already been processed.');
        }
        
        $leave->delete();
        
        return redirect()->route('leaves.index')
            ->with('success', 'Leave request cancelled successfully.');
    }
    
    /**
     * Check leave credits via AJAX.
     */
    public function checkCredits(Request $request)
    {
        $employeeId = $request->employee_id ?? auth()->id();
        $leaveTypeId = $request->leave_type_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        
        $leaveCredits = LeaveCredit::where('employee_id', $employeeId)
            ->where('fiscal_year', Carbon::now()->year)
            ->with('leaveType')
            ->get();
            
        $creditDetails = null;
        if ($leaveTypeId && $startDate && $endDate) {
            $creditDetails = $this->leaveCreditService->calculateLeaveCredits(
                $employeeId,
                $leaveTypeId,
                $startDate,
                $endDate
            );
        }
            
        return response()->json([
            'leave_credits' => $leaveCredits,
            'credit_details' => $creditDetails
        ]);
    }
}