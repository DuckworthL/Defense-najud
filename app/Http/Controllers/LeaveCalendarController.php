<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveCalendarController extends Controller
{
    /**
     * Display the leave calendar view.
     */
    public function index(Request $request)
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $leaveTypes = \App\Models\LeaveType::where('is_active', true)->orderBy('name')->get();
        
        // For HR/Admin, show all departments by default
        // For regular employees, filter to their department only
        $selectedDepartment = null;
        $userIsRestricted = !auth()->user()->isAdmin() && !auth()->user()->isHR();
        
        if ($userIsRestricted) {
            $employeeDepartment = auth()->user()->department;
            if ($employeeDepartment) {
                $selectedDepartment = $employeeDepartment->id;
            }
        } else {
            $selectedDepartment = $request->department_id;
        }
        
        return view('leaves.calendar', compact('departments', 'leaveTypes', 'selectedDepartment', 'userIsRestricted'));
    }
    
    /**
     * Get leave events for the calendar.
     */
    public function getEvents(Request $request)
    {
        $startDate = $request->start;
        $endDate = $request->end;
        $departmentId = $request->department_id;
        $leaveTypeId = $request->leave_type_id;
        $status = $request->status;
        
        $query = Leave::with(['employee', 'employee.department', 'leaveType'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                   ->orWhereBetween('end_date', [$startDate, $endDate])
                   ->orWhere(function ($q) use ($startDate, $endDate) {
                       $q->where('start_date', '<', $startDate)
                         ->where('end_date', '>', $endDate);
                   });
            });
        
        // Filter by department if provided
        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        } else if (!auth()->user()->isAdmin() && !auth()->user()->isHR()) {
            // Regular employees can only see leaves in their department
            $employeeDepartment = auth()->user()->department_id;
            $query->whereHas('employee', function ($q) use ($employeeDepartment) {
                $q->where('department_id', $employeeDepartment);
            });
        }
        
        // Filter by leave type if provided
        if ($leaveTypeId) {
            $query->where('leave_type_id', $leaveTypeId);
        }
        
        // Filter by status if provided
        if ($status) {
            $query->where('status', $status);
        }
        
        $leaves = $query->get();
        
        $events = [];
        foreach ($leaves as $leave) {
            // Skip if employee or department is missing
            if (!$leave->employee || !$leave->employee->department) {
                continue;
            }
            
            // Determine event color based on status
            $color = $this->getStatusColor($leave->status);
            $borderColor = $color;
            
            // Add additional styling for without pay leaves
            $textColor = '#ffffff';
            $title = $leave->employee->first_name . ' ' . $leave->employee->last_name;
            
            if ($leave->is_without_pay) {
                $title .= ' (Without Pay)';
                if ($leave->requires_split_payment) {
                    $title .= ' - Split';
                }
            }
            
            $events[] = [
                'id' => $leave->id,
                'title' => $title,
                'start' => $leave->start_date->format('Y-m-d'),
                'end' => $leave->end_date->addDay()->format('Y-m-d'), // Add a day for proper display in FullCalendar
                'color' => $color,
                'borderColor' => $borderColor,
                'textColor' => $textColor,
                'extendedProps' => [
                    'employee_id' => $leave->employee_id,
                    'employee_name' => $leave->employee->first_name . ' ' . $leave->employee->last_name,
                    'department' => $leave->employee->department->name,
                    'department_id' => $leave->employee->department_id,
                    'leave_type' => $leave->leaveType ? $leave->leaveType->name : 'Unknown',
                    'status' => ucfirst($leave->status),
                    'with_pay_days' => $leave->with_pay_days,
                    'without_pay_days' => $leave->without_pay_days,
                    'is_without_pay' => $leave->is_without_pay,
                    'requires_split_payment' => $leave->requires_split_payment,
                    'reason' => $leave->reason,
                ]
            ];
        }
        
        return response()->json($events);
    }
    
    /**
     * Get department coverage data
     */
    public function getDepartmentCoverage(Request $request)
    {
        $date = $request->date ?? Carbon::now()->format('Y-m-d');
        $departmentId = $request->department_id;
        
        // If not admin/HR and no department specified, use employee's department
        if ((!auth()->user()->isAdmin() && !auth()->user()->isHR()) && !$departmentId) {
            $departmentId = auth()->user()->department_id;
        }
        
        // If still no department, return empty response
        if (!$departmentId) {
            return response()->json([
                'department' => null,
                'total_employees' => 0,
                'present_employees' => 0,
                'on_leave' => 0,
                'coverage_percentage' => 0,
                'leave_details' => []
            ]);
        }
        
        // Get department info
        $department = Department::find($departmentId);
        
        if (!$department) {
            return response()->json([
                'error' => 'Department not found'
            ], 404);
        }
        
        // Get total active employees in department
        $totalEmployees = Employee::where('department_id', $departmentId)
            ->where('status', 'active')
            ->count();
            
        // Get employees on leave for the date
        $employeesOnLeave = Leave::whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId)
                  ->where('status', 'active');
            })
            ->where('status', 'approved')
            ->where(function ($q) use ($date) {
                $q->whereDate('start_date', '<=', $date)
                  ->whereDate('end_date', '>=', $date);
            })
            ->with(['employee', 'leaveType'])
            ->get();
            
        $onLeaveCount = $employeesOnLeave->count();
        $presentEmployees = $totalEmployees - $onLeaveCount;
        $coveragePercentage = $totalEmployees > 0 ? round(($presentEmployees / $totalEmployees) * 100, 1) : 0;
        
        $leaveDetails = [];
        foreach ($employeesOnLeave as $leave) {
            $leaveDetails[] = [
                'employee_name' => $leave->employee->first_name . ' ' . $leave->employee->last_name,
                'leave_type' => $leave->leaveType ? $leave->leaveType->name : 'Unknown',
                'start_date' => $leave->start_date->format('Y-m-d'),
                'end_date' => $leave->end_date->format('Y-m-d'),
                'days_count' => $leave->days_count,
                'is_without_pay' => $leave->is_without_pay,
            ];
        }
        
        return response()->json([
            'department' => $department->name,
            'total_employees' => $totalEmployees,
            'present_employees' => $presentEmployees,
            'on_leave' => $onLeaveCount,
            'coverage_percentage' => $coveragePercentage,
            'leave_details' => $leaveDetails
        ]);
    }
    
    /**
     * Get leave conflicts for a given date range and department
     */
    public function getLeaveConflicts(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'department_id' => 'nullable|exists:departments,id',
        ]);
        
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $departmentId = $request->department_id;
        
        // If not admin/HR and no department specified, use employee's department
        if ((!auth()->user()->isAdmin() && !auth()->user()->isHR()) && !$departmentId) {
            $departmentId = auth()->user()->department_id;
        }
        
        // Get approved leaves in the date range for the department
        $query = Leave::whereHas('employee', function ($q) use ($departmentId) {
                if ($departmentId) {
                    $q->where('department_id', $departmentId);
                }
                $q->where('status', 'active');
            })
            ->where('status', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                   ->orWhereBetween('end_date', [$startDate, $endDate])
                   ->orWhere(function ($q) use ($startDate, $endDate) {
                       $q->where('start_date', '<', $startDate)
                         ->where('end_date', '>', $endDate);
                   });
            })
            ->with(['employee', 'leaveType']);
            
        $leaves = $query->get();
        
        // Calculate daily coverage and find conflict days
        $dateRange = new \DatePeriod(
            new \DateTime($startDate),
            new \DateInterval('P1D'),
            (new \DateTime($endDate))->modify('+1 day')
        );
        
        $dailyCoverage = [];
        $departmentEmployeeCount = Employee::where('department_id', $departmentId)
            ->where('status', 'active')
            ->count();
            
        $conflictDays = [];
        $employeeLeaveMap = [];
        
        // Initialize each day in the date range
        foreach ($dateRange as $date) {
            $day = $date->format('Y-m-d');
            $dailyCoverage[$day] = [
                'date' => $day,
                'day_name' => $date->format('l'),
                'present' => $departmentEmployeeCount,
                'absent' => 0,
                'coverage' => 100,
            ];
            
            // Initialize empty arrays for each employee on each day
            $employeeLeaveMap[$day] = [];
        }
        
        // For each leave, mark the employee as absent on each day of the leave
        foreach ($leaves as $leave) {
            $leaveStart = max($leave->start_date->format('Y-m-d'), $startDate);
            $leaveEnd = min($leave->end_date->format('Y-m-d'), $endDate);
            
            $leaveDateRange = new \DatePeriod(
                new \DateTime($leaveStart),
                new \DateInterval('P1D'),
                (new \DateTime($leaveEnd))->modify('+1 day')
            );
            
            foreach ($leaveDateRange as $leaveDate) {
                $day = $leaveDate->format('Y-m-d');
                if (isset($dailyCoverage[$day])) {
                    $dailyCoverage[$day]['absent']++;
                    $dailyCoverage[$day]['present']--;
                    
                    // If employee is already marked for this day, we have a conflict
                    $employeeLeaveMap[$day][$leave->employee_id] = $leave->leaveType->name ?? 'Leave';
                    
                    // Recalculate coverage percentage
                    $dailyCoverage[$day]['coverage'] = $departmentEmployeeCount > 0 
                        ? round(($dailyCoverage[$day]['present'] / $departmentEmployeeCount) * 100, 1) 
                        : 0;
                    
                    // Check if coverage drops below 70% (configurable)
                    if ($dailyCoverage[$day]['coverage'] < 70) {
                        $conflictDays[$day] = [
                            'date' => $day,
                            'day_name' => $leaveDate->format('l'),
                            'coverage' => $dailyCoverage[$day]['coverage'],
                            'present' => $dailyCoverage[$day]['present'],
                            'absent' => $dailyCoverage[$day]['absent'],
                            'employees_on_leave' => $employeeLeaveMap[$day],
                        ];
                    }
                }
            }
        }
        
        return response()->json([
            'department_id' => $departmentId,
            'department_name' => Department::find($departmentId)->name ?? 'All Departments',
            'total_employees' => $departmentEmployeeCount,
            'conflict_days' => array_values($conflictDays),
            'daily_coverage' => array_values($dailyCoverage),
        ]);
    }
    
    /**
     * Get color code for leave status
     */
    private function getStatusColor($status)
    {
        switch ($status) {
            case 'pending':
                return '#f6c23e'; // warning/amber
            case 'approved':
                return '#1cc88a'; // success/green
            case 'rejected':
                return '#e74a3b'; // danger/red
            default:
                return '#858796'; // secondary/gray
        }
    }
}