<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\Department;
use App\Models\Leave;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function adminDashboard()
    {
        $today = Carbon::today()->toDateString();
        
        // Get status counts for today
        $presentStatus = AttendanceStatus::where('name', 'Present')->first();
        $presentCount = $presentStatus ? Attendance::where('date', $today)->where('attendance_status_id', $presentStatus->id)->count() : 0;
        
        $lateStatus = AttendanceStatus::where('name', 'Late')->first();
        $lateCount = $lateStatus ? Attendance::where('date', $today)->where('attendance_status_id', $lateStatus->id)->count() : 0;
        
        $absentStatus = AttendanceStatus::where('name', 'Absent')->first();
        $absentCount = $absentStatus ? Attendance::where('date', $today)->where('attendance_status_id', $absentStatus->id)->count() : 0;
        
        $onLeaveStatus = AttendanceStatus::where('name', 'On Leave')->first();
        $onLeaveCount = $onLeaveStatus ? Attendance::where('date', $today)->where('attendance_status_id', $onLeaveStatus->id)->count() : 0;
        
        // Department-wise attendance
        $departmentAttendance = Department::select('departments.name', DB::raw('COUNT(attendance.id) as count'))
            ->leftJoin('employees', 'departments.id', '=', 'employees.department_id')
            ->leftJoin('attendance', function ($join) use ($today) {
                $join->on('employees.id', '=', 'attendance.employee_id')
                    ->where('attendance.date', $today);
            })
            ->groupBy('departments.name')
            ->get();
        
        // Recent attendance
        $recentAttendance = Attendance::with(['employee', 'attendanceStatus'])
            ->where('date', $today)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Pending leave requests
        $pendingLeaves = Leave::with('employee')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Weekly attendance trend data
        $weeklyTrend = $this->getWeeklyTrendData();
        
        // Late employees - Using the Shift table to calculate late minutes
        $lateEmployees = Employee::join('attendance', 'employees.id', '=', 'attendance.employee_id')
            ->join('attendance_statuses', 'attendance.attendance_status_id', '=', 'attendance_statuses.id')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->join('shifts', 'employees.shift_id', '=', 'shifts.id')  // Join with shifts table
            ->select(
                'employees.id',
                'employees.first_name',
                'employees.last_name',
                'employees.employee_id',
                'departments.name as department_name',
                'attendance.clock_in_time',
                'shifts.start_time as shift_start_time',
                DB::raw('TIMESTAMPDIFF(MINUTE, CONCAT(attendance.date, " ", shifts.start_time), attendance.clock_in_time) as late_by_minutes')
            )
            ->where('attendance.date', $today)
            ->where('attendance_statuses.name', 'Late')
            ->orderBy('late_by_minutes', 'desc')
            ->limit(5)
            ->get();
        
        // Missing employees (expected but not checked in)
        $missingEmployees = Employee::with('department')
            ->whereDoesntHave('attendance', function ($query) use ($today) {
                $query->where('date', $today);
            })
            ->where('status', 'active')
            ->limit(5)
            ->get();
        
        return view('dashboards.admin', compact(
            'presentCount',
            'lateCount',
            'absentCount',
            'onLeaveCount',
            'departmentAttendance',
            'recentAttendance',
            'pendingLeaves',
            'weeklyTrend',
            'lateEmployees',
            'missingEmployees'
        ));
    }

    private function getWeeklyTrendData()
    {
        $result = [];
        
        // Get data for the last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $dayName = Carbon::now()->subDays($i)->format('D');
            
            $presentStatus = AttendanceStatus::where('name', 'Present')->first();
            $presentCount = $presentStatus ? Attendance::where('date', $date)->where('attendance_status_id', $presentStatus->id)->count() : 0;
            
            $lateStatus = AttendanceStatus::where('name', 'Late')->first();
            $lateCount = $lateStatus ? Attendance::where('date', $date)->where('attendance_status_id', $lateStatus->id)->count() : 0;
            
            $result[] = [
                'day' => $dayName,
                'present' => $presentCount,
                'late' => $lateCount
            ];
        }
        
        return $result;
    }

    public function hrDashboard()
    {
        // Similar to admin dashboard but potentially with different content
        return $this->adminDashboard();
    }

    public function employeeDashboard()
    {
        $employee = auth()->user();
        
        // Get today's attendance
        $today = Carbon::today()->toDateString();
        $todayAttendance = Attendance::with('attendanceStatus')
            ->where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();
        
        // Get recent attendance
        $recentAttendance = Attendance::with('attendanceStatus')
            ->where('employee_id', $employee->id)
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();
        
        // Get leave requests
        $leaves = Leave::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('dashboards.employee', compact(
            'employee',
            'todayAttendance',
            'recentAttendance',
            'leaves'
        ));
    }
}