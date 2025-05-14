<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CounterController extends Controller
{
    /**
     * Display the counter terminal dashboard.
     */
    public function dashboard()
    {
        // Get today's recent attendance (using app timezone from config)
        $today = Carbon::today()->format('Y-m-d');
        $recentAttendance = Attendance::with(['employee', 'employee.department', 'attendanceStatus'])
            ->where('date', $today)
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();
        
        return view('dashboards.counter', compact('recentAttendance'));
    }
    
    /**
     * Display the counter search page.
     */
    public function search(Request $request)
    {
        $query = $request->query('query');
        $employees = collect();
        
        if ($query && strlen($query) >= 2) {
            $employees = Employee::with(['department', 'role', 'shift'])
                ->where('status', 'active')
                ->where(function($q) use ($query) {
                    $q->where('employee_id', $query)
                      ->orWhere('employee_id', 'like', "%{$query}%")
                      ->orWhere('first_name', 'like', "%{$query}%")
                      ->orWhere('last_name', 'like', "%{$query}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"]);
                })
                ->take(10)
                ->get();
                
            Log::info('Employee search for "' . $query . '" found ' . $employees->count() . ' results');
        }
        
        return view('attendance.counter-search', [
            'employees' => $employees,
            'searchQuery' => $query
        ]);
    }
    
    /**
     * Process employee search from counter.
     */
    public function processSearch(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);
        
        $query = $request->input('query');
        
        Log::info('Processing search for: ' . $query);
        
        try {
            $employees = Employee::with(['department', 'role', 'shift'])
                ->where('status', 'active')
                ->where(function($q) use ($query) {
                    $q->where('employee_id', $query)
                      ->orWhere('employee_id', 'like', "{$query}%")
                      ->orWhere('first_name', 'like', "%{$query}%")
                      ->orWhere('last_name', 'like', "%{$query}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"]);
                })
                ->take(10)
                ->get();
                
            Log::info('Query results: ' . json_encode($employees->pluck('employee_id')));
            
            return response()->json([
                'success' => true,
                'employees' => $employees,
                'count' => $employees->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Search error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display the verification page for employee attendance.
     */
    public function verify($id)
    {
        try {
            $employee = Employee::with(['department', 'role', 'shift'])->findOrFail($id);
            
            // Check if attendance record exists for today
            $today = Carbon::today()->format('Y-m-d');
            $attendance = Attendance::with('attendanceStatus')
                ->where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();
                
            return view('attendance.counter-verify', compact('employee', 'attendance'));
        } catch (\Exception $e) {
            Log::error('Error in verification page: ' . $e->getMessage());
            return redirect()->route('counter.search')
                ->with('error', 'Unable to verify employee: ' . $e->getMessage());
        }
    }
    
    /**
     * Process attendance verification from counter.
     */
    public function processVerification(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'verified_by' => 'required|exists:employees,id',
            'action' => 'required|in:clock_in,clock_out',
        ]);
        
        try {
            if ($request->action === 'clock_in') {
                return $this->clockIn($request);
            } else {
                return $this->clockOut($request);
            }
        } catch (\Exception $e) {
            Log::error('Verification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Process clock in for an employee.
     */
    public function clockIn(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'verified_by' => 'required|exists:employees,id',
        ]);
        
        $employee = Employee::with('shift')->find($request->employee_id);
        
        // Use Carbon now() with app timezone from config
        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        
        // Check if attendance record already exists
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();
            
        if ($attendance && $attendance->clock_in_time) {
            return response()->json([
                'success' => false,
                'message' => 'Employee has already clocked in today at ' . $attendance->clock_in_time->format('h:i:s A')
            ]);
        }
        
        // Determine if late based on shift schedule
        $shift = $employee->shift;
        $shiftStart = Carbon::parse($today . ' ' . $shift->start_time);
        $graceEndTime = $shiftStart->copy()->addMinutes($shift->grace_period_minutes);
        
        if ($now->gt($graceEndTime)) {
            // Employee is late - get Late status from database
            $statusId = AttendanceStatus::where('name', 'Late')->first()->id;
            $statusName = 'Late';
        } else {
            // Employee is present - get Present status from database
            $statusId = AttendanceStatus::where('name', 'Present')->first()->id;
            $statusName = 'Present';
        }
        
        try {
            if ($attendance) {
                // Update existing record
                $attendance->clock_in_time = $now;
                $attendance->attendance_status_id = $statusId;
                $attendance->verified_by = $request->verified_by;
                $attendance->verification_time = $now;
                $attendance->updated_by = $request->verified_by;
                $attendance->save();
            } else {
                // Create new record
                $attendance = Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $today,
                    'attendance_status_id' => $statusId,
                    'clock_in_time' => $now,
                    'verified_by' => $request->verified_by,
                    'verification_time' => $now,
                    'created_by' => $request->verified_by,
                    'updated_by' => $request->verified_by,
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => "Employee successfully clocked in at " . $now->format('h:i A') . ". Status: " . $statusName,
                'attendance' => $attendance
            ]);
        } catch (\Exception $e) {
            Log::error('Clock-in error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while clocking in: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Process clock out for an employee.
     */
    public function clockOut(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'verified_by' => 'required|exists:employees,id',
        ]);
        
        $employee = Employee::find($request->employee_id);
        
        // Use Carbon now() with app timezone from config
        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        
        // Check if attendance record exists
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();
            
        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'No clock-in record found for today. Please clock in first.'
            ]);
        }
        
        if (!$attendance->clock_in_time) {
            return response()->json([
                'success' => false,
                'message' => 'Employee has not clocked in yet today. Clock in is required before clock out.'
            ]);
        }
        
        if ($attendance->clock_out_time) {
            return response()->json([
                'success' => false,
                'message' => 'Employee has already clocked out today at ' . $attendance->clock_out_time->format('h:i:s A')
            ]);
        }
        
        try {
            // Update record with clock out time
            $attendance->clock_out_time = $now;
            $attendance->verified_by = $request->verified_by;
            $attendance->verification_time = $now;
            $attendance->updated_by = $request->verified_by;
            $attendance->save();
            
            // Calculate hours worked
            $hoursWorked = $attendance->clock_in_time->diffInHours($attendance->clock_out_time);
            $minutesWorked = $attendance->clock_in_time->diffInMinutes($attendance->clock_out_time) % 60;
            
            return response()->json([
                'success' => true,
                'message' => "Employee successfully clocked out at " . $now->format('h:i A') . ". Total hours worked: " . $hoursWorked . "h " . $minutesWorked . "m",
                'attendance' => $attendance
            ]);
        } catch (\Exception $e) {
            Log::error('Clock-out error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while clocking out: ' . $e->getMessage()
            ], 500);
        }
    }
}