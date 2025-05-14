<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceDashboardController extends Controller
{
    /**
     * Show the real-time attendance dashboard
     */
    public function index(Request $request)
    {
        $today = Carbon::today()->format('Y-m-d');
        $selectedDate = $request->date ?? $today;
        $selectedDepartment = $request->department_id;
        
        // Get all departments for the filter
        $departments = Department::where('is_active', true)
                      ->orderBy('name')
                      ->get();
        
        // Get attendance statuses
        $statuses = AttendanceStatus::all();
        
        // Get shift information
        $shifts = Shift::all();
        
        return view('attendance.dashboard', compact(
            'departments',
            'statuses',
            'shifts',
            'selectedDate',
            'selectedDepartment',
            'today'
        ));
    }
    
    /**
     * Get attendance summary data via AJAX
     */
    public function getSummaryData(Request $request)
    {
        $date = $request->date ?? Carbon::today()->format('Y-m-d');
        $departmentId = $request->department_id;
        
        // Base query
        $query = Attendance::where('date', $date);
        
        // Apply department filter if provided
        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        // Get count by status
        $statusCounts = $query->select('attendance_status_id', DB::raw('count(*) as count'))
            ->groupBy('attendance_status_id')
            ->pluck('count', 'attendance_status_id')
            ->toArray();
        
        // Get all status IDs to ensure we have all statuses represented
        $allStatuses = AttendanceStatus::select('id', 'name', 'color_code')->get();
        
        // Format status data for the chart
        $statusSummary = [];
        foreach ($allStatuses as $status) {
            $statusSummary[] = [
                'id' => $status->id,
                'name' => $status->name,
                'color' => $status->color_code ?? $this->getDefaultStatusColor($status->name),
                'count' => $statusCounts[$status->id] ?? 0
            ];
        }
        
        // Calculate expected vs. actual attendance
        $totalEmployees = Employee::query()
            ->where('status', 'active')
            ->when($departmentId, function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })
            ->count();
            
        $totalRecorded = array_sum($statusCounts);
        $notRecorded = $totalEmployees - $totalRecorded;
        
        // Calculate present percentage
        $presentId = AttendanceStatus::where('name', 'Present')->first()->id ?? 0;
        $presentCount = $statusCounts[$presentId] ?? 0;
        $presentPercentage = $totalEmployees > 0 ? round(($presentCount / $totalEmployees) * 100, 1) : 0;
        
        return response()->json([
            'status_summary' => $statusSummary,
            'total_employees' => $totalEmployees,
            'total_recorded' => $totalRecorded,
            'not_recorded' => $notRecorded,
            'present_percentage' => $presentPercentage
        ]);
    }
    
    /**
     * Get department attendance breakdown via AJAX
     */
    public function getDepartmentData(Request $request)
    {
        $date = $request->date ?? Carbon::today()->format('Y-m-d');
        
        // Get departments with attendance counts
        $departments = Department::where('is_active', true)
            ->with(['employees' => function ($q) {
                $q->where('status', 'active');
            }])
            ->withCount(['employees' => function ($q) {
                $q->where('status', 'active');
            }])
            ->get();
            
        $presentStatusId = AttendanceStatus::where('name', 'Present')->first()->id ?? 0;
        $lateStatusId = AttendanceStatus::where('name', 'Late')->first()->id ?? 0;
        $absentStatusId = AttendanceStatus::where('name', 'Absent')->first()->id ?? 0;
        
        $deptData = [];
        
        foreach ($departments as $dept) {
            // Skip departments with no active employees
            if ($dept->employees_count === 0) continue;
            
            // Get attendance counts for this department
            $present = Attendance::where('date', $date)
                ->where('attendance_status_id', $presentStatusId)
                ->whereHas('employee', function ($q) use ($dept) {
                    $q->where('department_id', $dept->id)
                      ->where('status', 'active');
                })
                ->count();
                
            $late = Attendance::where('date', $date)
                ->where('attendance_status_id', $lateStatusId)
                ->whereHas('employee', function ($q) use ($dept) {
                    $q->where('department_id', $dept->id)
                      ->where('status', 'active');
                })
                ->count();
                
            $absent = Attendance::where('date', $date)
                ->where('attendance_status_id', $absentStatusId)
                ->whereHas('employee', function ($q) use ($dept) {
                    $q->where('department_id', $dept->id)
                      ->where('status', 'active');
                })
                ->count();
            
            // Calculate attendance coverage
            $recorded = $present + $late + $absent;
            $coverage = $dept->employees_count > 0 ? round(($recorded / $dept->employees_count) * 100, 1) : 0;
            $presentPercentage = $dept->employees_count > 0 ? round((($present + $late) / $dept->employees_count) * 100, 1) : 0;
            
            // Add to result array
            $deptData[] = [
                'id' => $dept->id,
                'name' => $dept->name,
                'total' => $dept->employees_count,
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'recorded' => $recorded,
                'unrecorded' => $dept->employees_count - $recorded,
                'coverage' => $coverage,
                'present_percentage' => $presentPercentage
            ];
        }
        
        // Sort by coverage (lowest first to highlight problems)
        usort($deptData, function ($a, $b) {
            return $a['coverage'] <=> $b['coverage'];
        });
        
        return response()->json([
            'departments' => $deptData
        ]);
    }
    
    /**
     * Get live attendance updates via AJAX
     */
    public function getLiveUpdates(Request $request)
    {
        $date = $request->date ?? Carbon::today()->format('Y-m-d');
        $departmentId = $request->department_id;
        $lastTimestamp = $request->last_timestamp ?? '00:00:00';
        
        // Get recent attendance records
        $query = Attendance::with(['employee', 'employee.department', 'attendanceStatus'])
            ->where('date', $date)
            ->where(function ($q) use ($lastTimestamp) {
                $q->where('created_at', '>', $date . ' ' . $lastTimestamp)
                  ->orWhere(function ($q) use ($lastTimestamp, $date) {
                      $q->where('updated_at', '>', $date . ' ' . $lastTimestamp)
                        ->where('updated_at', '>', DB::raw('created_at'));
                  });
            });
        
        // Apply department filter if provided
        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        $records = $query->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();
            
        $formattedRecords = [];
        $currentTimestamp = Carbon::now()->format('H:i:s');
        
        foreach ($records as $record) {
            $formattedRecords[] = [
                'id' => $record->id,
                'employee_id' => $record->employee_id,
                'employee_name' => $record->employee->first_name . ' ' . $record->employee->last_name,
                'employee_code' => $record->employee->employee_id,
                'department' => $record->employee->department->name ?? 'Not Assigned',
                'status' => $record->attendanceStatus->name,
                'status_color' => $record->attendanceStatus->color_code ?? $this->getDefaultStatusColor($record->attendanceStatus->name),
                'clock_in' => $record->clock_in_time ? $record->clock_in_time->format('h:i A') : null,
                'clock_out' => $record->clock_out_time ? $record->clock_out_time->format('h:i A') : null,
                'updated_at' => $record->updated_at->format('Y-m-d H:i:s'),
                'is_new' => $record->created_at->format('Y-m-d') === $date,
                'is_updated' => $record->updated_at->gt($record->created_at)
            ];
        }
        
        return response()->json([
            'records' => $formattedRecords,
            'current_timestamp' => $currentTimestamp
        ]);
    }
    
    /**
     * Get attendance trend data via AJAX
     */
    public function getTrendData(Request $request)
    {
        $departmentId = $request->department_id;
        $days = $request->days ?? 7;
        
        // Calculate date range
        $endDate = Carbon::today();
        $startDate = (clone $endDate)->subDays($days - 1);
        
        $presentStatusId = AttendanceStatus::where('name', 'Present')->first()->id ?? 0;
        $lateStatusId = AttendanceStatus::where('name', 'Late')->first()->id ?? 0;
        
        $dateRange = [];
        $presentData = [];
        $lateData = [];
        $activeEmployeeCount = [];
        
        // Get active employee count for each day
        for ($date = clone $startDate; $date->lte($endDate); $date->addDay()) {
            $formattedDate = $date->format('Y-m-d');
            $dateRange[] = $date->format('D, M d');
            
            // Count active employees for each day
            $employeeCount = Employee::where('status', 'active')
                ->where(function ($q) use ($formattedDate) {
                    $q->whereNull('date_hired')
                      ->orWhere('date_hired', '<=', $formattedDate);
                })
                ->when($departmentId, function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                })
                ->count();
            $activeEmployeeCount[$formattedDate] = $employeeCount;
            
            // Present count
            $presentCount = Attendance::where('date', $formattedDate)
                ->where('attendance_status_id', $presentStatusId)
                ->when($departmentId, function ($q) use ($departmentId) {
                    $q->whereHas('employee', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    });
                })
                ->count();
            $presentData[] = $presentCount;
            
            // Late count
            $lateCount = Attendance::where('date', $formattedDate)
                ->where('attendance_status_id', $lateStatusId)
                ->when($departmentId, function ($q) use ($departmentId) {
                    $q->whereHas('employee', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    });
                })
                ->count();
            $lateData[] = $lateCount;
        }
        
        // Calculate percentage data
        $presentPercentage = [];
        $latePercentage = [];
        
        for ($i = 0; $i < count($dateRange); $i++) {
            $formattedDate = (clone $startDate)->addDays($i)->format('Y-m-d');
            $totalEmployees = $activeEmployeeCount[$formattedDate];
            
            $presentPercentage[] = $totalEmployees > 0 ? round(($presentData[$i] / $totalEmployees) * 100, 1) : 0;
            $latePercentage[] = $totalEmployees > 0 ? round(($lateData[$i] / $totalEmployees) * 100, 1) : 0;
        }
        
        return response()->json([
            'dates' => $dateRange,
            'present_counts' => $presentData,
            'late_counts' => $lateData,
            'present_percentage' => $presentPercentage,
            'late_percentage' => $latePercentage
        ]);
    }
    
    /**
     * Get exception data via AJAX
     */
    public function getExceptionData(Request $request)
    {
        $date = $request->date ?? Carbon::today()->format('Y-m-d');
        $departmentId = $request->department_id;
        
        // Get employees with issues
        $lateStatusId = AttendanceStatus::where('name', 'Late')->first()->id ?? 0;
        
        // Get employees who are late
        $lateEmployees = Attendance::with(['employee', 'employee.department', 'employee.shift'])
            ->where('date', $date)
            ->where('attendance_status_id', $lateStatusId)
            ->when($departmentId, function ($q) use ($departmentId) {
                $q->whereHas('employee', function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            })
            ->orderBy('clock_in_time', 'desc')
            ->take(5)
            ->get()
            ->map(function ($attendance) {
                $shift = $attendance->employee->shift;
                $lateByMinutes = 0;
                
                if ($shift && $attendance->clock_in_time) {
                    $shiftStart = Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $shift->start_time)
                        ->addMinutes($shift->grace_period_minutes);
                    $lateByMinutes = $attendance->clock_in_time->diffInMinutes($shiftStart);
                }
                
                return [
                    'id' => $attendance->id,
                    'employee_id' => $attendance->employee_id,
                    'employee_name' => $attendance->employee->first_name . ' ' . $attendance->employee->last_name,
                    'department' => $attendance->employee->department->name ?? 'Not Assigned',
                    'clock_in_time' => $attendance->clock_in_time ? $attendance->clock_in_time->format('h:i A') : null,
                    'shift_start' => $shift ? Carbon::parse($shift->start_time)->format('h:i A') : 'N/A',
                    'late_by_minutes' => $lateByMinutes,
                    'exception_type' => 'Late Arrival'
                ];
            })
            ->toArray();
        
        // Get employees with partial-day attendance (clocked in but not out)
        $partialDayEmployees = Attendance::with(['employee', 'employee.department'])
            ->where('date', $date)
            ->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time')
            ->when($departmentId, function ($q) use ($departmentId) {
                $q->whereHas('employee', function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            })
            ->whereRaw('TIME(clock_in_time) < ?', [Carbon::now()->subHours(8)->format('H:i:s')])
            ->orderBy('clock_in_time', 'asc')
            ->take(5)
            ->get()
            ->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'employee_id' => $attendance->employee_id,
                    'employee_name' => $attendance->employee->first_name . ' ' . $attendance->employee->last_name,
                    'department' => $attendance->employee->department->name ?? 'Not Assigned',
                    'clock_in_time' => $attendance->clock_in_time ? $attendance->clock_in_time->format('h:i A') : null,
                    'hours_since_in' => $attendance->clock_in_time ? Carbon::now()->diffInHours($attendance->clock_in_time) : 0,
                    'exception_type' => 'No Clock-Out'
                ];
            })
            ->toArray();
        
        // Get active employees without attendance record
        $activeEmployeesWithoutAttendance = Employee::with('department')
            ->where('status', 'active')
            ->whereDoesntHave('attendances', function ($query) use ($date) {
                $query->where('date', $date);
            })
            ->when($departmentId, function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })
            ->orderBy('first_name')
            ->take(10)
            ->get()
            ->map(function ($employee) {
                return [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                    'department' => $employee->department->name ?? 'Not Assigned',
                    'employee_code' => $employee->employee_id,
                    'exception_type' => 'No Attendance Record'
                ];
            })
            ->toArray();
            
        return response()->json([
            'late_employees' => $lateEmployees,
            'partial_day_employees' => $partialDayEmployees,
            'missing_attendance' => $activeEmployeesWithoutAttendance
        ]);
    }
    
    /**
     * Get default color for attendance status
     */
    private function getDefaultStatusColor($statusName)
    {
        switch (strtolower($statusName)) {
            case 'present':
                return '#28a745'; // green
            case 'late':
                return '#ffc107'; // yellow
            case 'absent':
                return '#dc3545'; // red
            case 'on leave':
                return '#6c757d'; // gray
            default:
                return '#17a2b8'; // cyan
        }
    }
}