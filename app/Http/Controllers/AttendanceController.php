<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Apply filters if provided
        $query = Attendance::with(['employee', 'attendanceStatus', 'verifiedBy'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');
            
        if ($request->has('date') && $request->date) {
            $query->whereDate('date', $request->date);
        }
        
        if ($request->has('department_id') && $request->department_id) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }
        
        if ($request->has('attendance_status_id') && $request->attendance_status_id) {
            $query->where('attendance_status_id', $request->attendance_status_id);
        }
        
        // Paginate results
        $attendances = $query->paginate(15);
        
        // Get departments for the filter
        $departments = \App\Models\Department::where('is_active', true)->get();
        
        return view('attendance.index', compact('attendances', 'departments'));
    }

    /**
 * Show the form for creating a new attendance record.
 *
 * @return \Illuminate\Http\Response
 */
public function create()
{
    $employees = \App\Models\Employee::orderBy('first_name')
        ->select('id', 'first_name', 'last_name', 'employee_id')
        ->get();
    $statuses = \App\Models\AttendanceStatus::all();
    
    return view('attendance.create', compact('employees', 'statuses'));
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'attendance_status_id' => 'required|exists:attendance_statuses,id',
            'clock_in_time' => 'nullable|date_format:Y-m-d\TH:i',
            'clock_out_time' => 'nullable|date_format:Y-m-d\TH:i|after_or_equal:clock_in_time',
            'remarks' => 'nullable|string|max:500',
        ]);
        
        // Check if attendance record already exists
        $exists = Attendance::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->exists();
            
        if ($exists) {
            return back()->with('error', 'Attendance record already exists for this employee on the selected date.');
        }
        
        $attendance = Attendance::create([
            'employee_id' => $request->employee_id,
            'date' => $request->date,
            'attendance_status_id' => $request->attendance_status_id,
            'clock_in_time' => $request->clock_in_time,
            'clock_out_time' => $request->clock_out_time,
            'remarks' => $request->remarks,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);
        
        // If verified checkbox is checked, mark as verified
        if ($request->has('verified')) {
            $attendance->verified_by = Auth::id();
            $attendance->verification_time = now();
            $attendance->save();
        }
        
        return redirect()->route('attendance.index')
            ->with('success', 'Attendance record created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance)
    {
        return view('attendance.show', compact('attendance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance)
    {
        $statuses = AttendanceStatus::all();
        
        return view('attendance.edit', compact('attendance', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'attendance_status_id' => 'required|exists:attendance_statuses,id',
            'clock_in_time' => 'nullable|date_format:Y-m-d\TH:i',
            'clock_out_time' => 'nullable|date_format:Y-m-d\TH:i|after_or_equal:clock_in_time',
            'remarks' => 'nullable|string|max:500',
        ]);
        
        // Check if clock-in time has changed and needs to be reset
        if ($request->has('is_clock_in_reset') && 
            $request->clock_in_time !== $attendance->clock_in_time?->format('Y-m-d\TH:i')) {
            
            $attendance->is_clock_in_reset = true;
            $attendance->clock_in_reset_by = Auth::id();
            $attendance->clock_in_reset_reason = $request->clock_in_reset_reason;
        }
        
        // Check if clock-out time has changed and needs to be reset
        if ($request->has('is_clock_out_reset') && 
            $request->clock_out_time !== $attendance->clock_out_time?->format('Y-m-d\TH:i')) {
            
            $attendance->is_clock_out_reset = true;
            $attendance->clock_out_reset_by = Auth::id();
            $attendance->clock_out_reset_reason = $request->clock_out_reset_reason;
        }
        
        // Update attendance record
        $attendance->attendance_status_id = $request->attendance_status_id;
        $attendance->clock_in_time = $request->clock_in_time;
        $attendance->clock_out_time = $request->clock_out_time;
        $attendance->remarks = $request->remarks;
        $attendance->updated_by = Auth::id();
        $attendance->save();
        
        return redirect()->route('attendance.show', $attendance)
            ->with('success', 'Attendance record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        
        return redirect()->route('attendance.index')
            ->with('success', 'Attendance record deleted successfully.');
    }
    
    /**
     * Show counter search page.
     */
    public function counterSearch()
    {
        return view('attendance.counter-search');
    }
    
    /**
     * Process counter search.
     */
    public function processSearch(Request $request)
    {
        $request->validate([
            'query' => 'required|min:2',
        ]);
        
        $query = $request->query;
        
        $employees = Employee::with('department')
            ->where('status', 'active')
            ->where(function($q) use ($query) {
                $q->where('employee_id', 'like', "%{$query}%")
                  ->orWhere('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%");
            })
            ->take(10)
            ->get();
            
        return response()->json([
            'success' => true,
            'employees' => $employees,
        ]);
    }
    
    /**
     * Show counter verification page.
     */
    public function counterVerify($id)
    {
        $employee = Employee::findOrFail($id);
        
        // Check if attendance record exists for today
        $today = Carbon::today()->format('Y-m-d');
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();
            
        return view('attendance.counter-verify', compact('employee', 'attendance'));
    }
    
    /**
     * Process counter clock in.
     */
    public function clockIn(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'verified_by' => 'required|exists:employees,id',
        ]);
        
        $employee = Employee::find($request->employee_id);
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now();
        
        // Check if attendance record already exists
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();
            
        if ($attendance) {
            if ($attendance->clock_in_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee has already clocked in today.',
                ]);
            }
            
            // Update existing record
            $attendance->clock_in_time = $now;
            $attendance->verified_by = $request->verified_by;
            $attendance->verification_time = $now;
            
            // Determine if late
            $shift = $employee->shift;
            $startTime = Carbon::parse($shift->start_time);
            $graceEndTime = $startTime->copy()->addMinutes($shift->grace_period_minutes);
            
            if ($now->format('H:i:s') > $graceEndTime->format('H:i:s')) {
                // Employee is late
                $attendance->attendance_status_id = AttendanceStatus::where('name', 'Late')->first()->id;
            } else {
                // Employee is present
                $attendance->attendance_status_id = AttendanceStatus::where('name', 'Present')->first()->id;
            }
            
            $attendance->save();
        } else {
            // Create new record
            $shift = $employee->shift;
            $startTime = Carbon::parse($shift->start_time);
            $graceEndTime = $startTime->copy()->addMinutes($shift->grace_period_minutes);
            
            // Determine attendance status
            if ($now->format('H:i:s') > $graceEndTime->format('H:i:s')) {
                // Employee is late
                $statusId = AttendanceStatus::where('name', 'Late')->first()->id;
            } else {
                // Employee is present
                $statusId = AttendanceStatus::where('name', 'Present')->first()->id;
            }
            
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
            'message' => 'Employee clocked in successfully.',
            'attendance' => $attendance,
        ]);
    }
    
    /**
     * Process counter clock out.
     */
    public function clockOut(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'verified_by' => 'required|exists:employees,id',
        ]);
        
        $employee = Employee::find($request->employee_id);
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now();
        
        // Check if attendance record exists
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();
            
        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'No clock-in record found for today.',
            ]);
        }
        
        if ($attendance->clock_out_time) {
            return response()->json([
                'success' => false,
                'message' => 'Employee has already clocked out today.',
            ]);
        }
        
        // Update record with clock out time
        $attendance->clock_out_time = $now;
        $attendance->verified_by = $request->verified_by;
        $attendance->verification_time = $now;
        $attendance->updated_by = $request->verified_by;
        $attendance->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Employee clocked out successfully.',
            'attendance' => $attendance,
        ]);
    }

    /**
     * Process counter verification.
     */
    public function processVerification(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'verified_by' => 'required|exists:employees,id',
            'action' => 'required|in:clock_in,clock_out',
        ]);
        
        if ($request->action === 'clock_in') {
            return $this->clockIn($request);
        } else {
            return $this->clockOut($request);
        }
    }
}