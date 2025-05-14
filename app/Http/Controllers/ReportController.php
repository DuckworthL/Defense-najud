<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Show the report dashboard.
     */
    public function index()
    {
        $departments = Department::where('is_active', true)->get();
        return view('reports.index', compact('departments'));
    }
    
    /**
     * Generate attendance report.
     */
    public function attendanceReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'department_id' => 'nullable|exists:departments,id',
            'employee_id' => 'nullable|exists:employees,id',
            'report_type' => 'required|in:daily,summary',
            'export_format' => 'nullable|in:pdf,excel,csv',
        ]);
        
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $period = CarbonPeriod::create($startDate, $endDate);
        
        // Build employee query
        $employeeQuery = Employee::where('status', 'active');
        
        if ($request->department_id) {
            $employeeQuery->where('department_id', $request->department_id);
        }
        
        if ($request->employee_id) {
            $employeeQuery->where('id', $request->employee_id);
        }
        
        $employees = $employeeQuery->with(['department', 'shift'])->get();
        
        // Get attendance data
        $attendanceData = [];
        $summaryData = [];
        
        foreach ($employees as $employee) {
            $employeeAttendance = [];
            $present = 0;
            $late = 0;
            $absent = 0;
            $leave = 0;
            $earlyDeparture = 0;
            
            foreach ($period as $date) {
                $dateString = $date->format('Y-m-d');
                
                $attendance = Attendance::where('employee_id', $employee->id)
                    ->where('date', $dateString)
                    ->with('attendanceStatus')
                    ->first();
                
                if ($attendance) {
                    $status = $attendance->attendanceStatus->name;
                    
                    // Count status for summary
                    if ($status == 'Present') $present++;
                    elseif ($status == 'Late') $late++;
                    elseif ($status == 'Absent') $absent++;
                    elseif ($status == 'On Leave') $leave++;
                    elseif ($status == 'Early Departure') $earlyDeparture++;
                    
                    $employeeAttendance[$dateString] = [
                        'status' => $status,
                        'color' => $attendance->attendanceStatus->color_code,
                        'clock_in' => $attendance->clock_in_time ? $attendance->clock_in_time->format('h:i A') : '-',
                        'clock_out' => $attendance->clock_out_time ? $attendance->clock_out_time->format('h:i A') : '-',
                        'work_hours' => $attendance->work_hours ?? '-',
                        'remarks' => $attendance->remarks,
                    ];
                } else {
                    // If no record, consider as absent for past dates
                    if ($date->lt(Carbon::today())) {
                        $absent++;
                        $employeeAttendance[$dateString] = [
                            'status' => 'Absent',
                            'color' => '#dc3545', // Red
                            'clock_in' => '-',
                            'clock_out' => '-',
                            'work_hours' => '-',
                            'remarks' => 'No attendance record',
                        ];
                    } else {
                        $employeeAttendance[$dateString] = [
                            'status' => '-',
                            'color' => '#ffffff', // White
                            'clock_in' => '-',
                            'clock_out' => '-',
                            'work_hours' => '-',
                            'remarks' => '',
                        ];
                    }
                }
            }
            
            $attendanceData[$employee->id] = [
                'employee' => $employee,
                'attendance' => $employeeAttendance,
            ];
            
            $totalDays = $present + $late + $absent + $leave + $earlyDeparture;
            $attendanceRate = $totalDays > 0 ? round((($present + $late + $earlyDeparture) / $totalDays) * 100, 2) : 0;
            
            $summaryData[$employee->id] = [
                'employee' => $employee,
                'present' => $present,
                'late' => $late,
                'early_departure' => $earlyDeparture,
                'absent' => $absent,
                'leave' => $leave,
                'total_days' => $totalDays,
                'attendance_rate' => $attendanceRate,
            ];
        }
        
        // Prepare data based on report type
        if ($request->report_type == 'daily') {
            $data = [
                'title' => 'Daily Attendance Report',
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'period' => $period,
                'attendanceData' => $attendanceData,
            ];
        } else { // summary
            $data = [
                'title' => 'Attendance Summary Report',
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'summaryData' => $summaryData,
            ];
        }
        
        // Handle export if requested
        if ($request->has('export_format') && $request->export_format) {
            if ($request->export_format === 'pdf') {
                return $this->exportToPdf($request->report_type, $data);
            } else {
                return $this->exportToExcel($request->report_type, $request->export_format, $data);
            }
        }
        
        // Display report
        if ($request->report_type == 'daily') {
            return view('reports.attendance-daily', $data);
        } else {
            return view('reports.attendance-summary', $data);
        }
    }
    
    /**
     * Generate leave report.
     */
    public function leaveReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'department_id' => 'nullable|exists:departments,id',
            'employee_id' => 'nullable|exists:employees,id',
            'status' => 'nullable|in:pending,approved,rejected',
            'export_format' => 'nullable|in:pdf,excel,csv',
        ]);
        
        // Build leave query
        $leaveQuery = Leave::with(['employee.department', 'approver']);
        
        if ($request->start_date) {
            $leaveQuery->where(function($q) use ($request) {
                $q->where('start_date', '>=', $request->start_date)
                  ->orWhere('end_date', '>=', $request->start_date);
            });
        }
        
        if ($request->end_date) {
            $leaveQuery->where(function($q) use ($request) {
                $q->where('start_date', '<=', $request->end_date)
                  ->orWhere('end_date', '<=', $request->end_date);
            });
        }
        
        if ($request->status) {
            $leaveQuery->where('status', $request->status);
        }
        
        if ($request->department_id) {
            $leaveQuery->whereHas('employee', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }
        
        if ($request->employee_id) {
            $leaveQuery->where('employee_id', $request->employee_id);
        }
        
        $leaves = $leaveQuery->orderBy('start_date', 'desc')->get();
        
        $data = [
            'title' => 'Leave Report',
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'leaves' => $leaves,
        ];
        
        // Handle export if requested
        if ($request->has('export_format') && $request->export_format) {
            if ($request->export_format === 'pdf') {
                return $this->exportToPdf('leave', $data);
            } else {
                return $this->exportToExcel('leave', $request->export_format, $data);
            }
        }
        
        return view('reports.leave', $data);
    }
    
    /**
     * Generate late arrival report.
     */
    public function lateReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'department_id' => 'nullable|exists:departments,id',
            'employee_id' => 'nullable|exists:employees,id',
            'export_format' => 'nullable|in:pdf,excel,csv',
        ]);
        
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        
        // Get Late status ID
        $lateStatusId = AttendanceStatus::where('name', 'Late')->first()->id;
        
        // Build query
        $query = Attendance::with(['employee.department', 'employee.shift'])
            ->whereBetween('date', [$startDate, $endDate])
            ->where('attendance_status_id', $lateStatusId);
            
        if ($request->department_id) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }
        
        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }
        
        $lateAttendances = $query->orderBy('date', 'desc')->get();
        
        // Group by employee for summary
        $employeeSummary = [];
        
        foreach ($lateAttendances as $attendance) {
            $employeeId = $attendance->employee_id;
            
            if (!isset($employeeSummary[$employeeId])) {
                $employeeSummary[$employeeId] = [
                    'employee' => $attendance->employee,
                    'count' => 0,
                    'dates' => [],
                ];
            }
            
            $employeeSummary[$employeeId]['count']++;
            $employeeSummary[$employeeId]['dates'][] = [
                'date' => $attendance->date->format('Y-m-d'),
                'clock_in' => $attendance->clock_in_time ? $attendance->clock_in_time->format('h:i A') : '-',
                'minutes_late' => $this->calculateMinutesLate($attendance),
            ];
        }
        
        $data = [
            'title' => 'Late Arrival Report',
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'employeeSummary' => $employeeSummary,
        ];
        
        // Handle export if requested
        if ($request->has('export_format') && $request->export_format) {
            if ($request->export_format === 'pdf') {
                return $this->exportToPdf('late', $data);
            } else {
                return $this->exportToExcel('late', $request->export_format, $data);
            }
        }
        
        return view('reports.late', $data);
    }
    
    /**
     * Generate early departure report.
     */
    public function earlyDepartureReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'department_id' => 'nullable|exists:departments,id',
            'employee_id' => 'nullable|exists:employees,id',
            'export_format' => 'nullable|in:pdf,excel,csv',
        ]);
        
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        
        // Get Early Departure status ID or use a where condition to identify early departures
        $earlyDepartureStatusId = AttendanceStatus::where('name', 'Early Departure')->first();
        
        // If the status exists, use it; otherwise, we'll detect early departures manually
        $query = Attendance::with(['employee.department', 'employee.shift'])
            ->whereBetween('date', [$startDate, $endDate]);
            
        if ($earlyDepartureStatusId) {
            $query->where('attendance_status_id', $earlyDepartureStatusId->id);
        } else {
            // Manual early departure detection (fallback if status doesn't exist)
            $query->whereNotNull('clock_out_time');
        }
            
        if ($request->department_id) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }
        
        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }
        
        $earlyDepartures = $query->orderBy('date', 'desc')->get();
        
        // Post-filter if we need to manually detect early departures
        if (!$earlyDepartureStatusId) {
            $earlyDepartures = $earlyDepartures->filter(function ($attendance) {
                return $this->calculateMinutesEarly($attendance) > 0;
            });
        }
        
        // Group by employee for summary
        $employeeSummary = [];
        
        foreach ($earlyDepartures as $attendance) {
            $employeeId = $attendance->employee_id;
            
            if (!isset($employeeSummary[$employeeId])) {
                $employeeSummary[$employeeId] = [
                    'employee' => $attendance->employee,
                    'count' => 0,
                    'dates' => [],
                ];
            }
            
            $employeeSummary[$employeeId]['count']++;
            $employeeSummary[$employeeId]['dates'][] = [
                'date' => $attendance->date->format('Y-m-d'),
                'clock_out' => $attendance->clock_out_time ? $attendance->clock_out_time->format('h:i A') : '-',
                'minutes_early' => $this->calculateMinutesEarly($attendance),
                'remarks' => $attendance->remarks ?? '',
            ];
        }
        
        $data = [
            'title' => 'Early Departure Report',
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'employeeSummary' => $employeeSummary,
        ];
        
        // Handle export if requested
        if ($request->has('export_format') && $request->export_format) {
            if ($request->export_format === 'pdf') {
                return $this->exportToPdf('early-departure', $data);
            } else {
                return $this->exportToExcel('early-departure', $request->export_format, $data);
            }
        }
        
        return view('reports.early-departure', $data);
    }
    
    /**
     * Export report to PDF.
     */
    private function exportToPdf($reportType, $data)
    {
        $fileName = $reportType . '_report_' . date('Y-m-d_H-i-s') . '.pdf';
        
        // Map report types to their views
        $viewMap = [
            'daily' => 'reports.exports.attendance-daily-pdf',
            'summary' => 'reports.exports.attendance-summary-pdf',
            'leave' => 'reports.exports.leave-pdf',
            'late' => 'reports.exports.late-pdf',
            'early-departure' => 'reports.exports.early-departure-pdf',
        ];
        
        // If view exists, use it; otherwise use a default view
        $view = isset($viewMap[$reportType]) ? $viewMap[$reportType] : 'reports.exports.generic-pdf';
        
        // Create PDF
        $pdf = PDF::loadView($view, $data);
        
        // Set paper size and orientation based on report type
        if (in_array($reportType, ['daily', 'summary'])) {
            $pdf->setPaper('a4', 'landscape');
        } else {
            $pdf->setPaper('a4', 'portrait');
        }
        
        return $pdf->download($fileName);
    }
    
    /**
     * Export report to Excel or CSV.
     */
    private function exportToExcel($reportType, $format, $data)
    {
        $fileName = $reportType . '_report_' . date('Y-m-d_H-i-s');
        
        // For now, we'll return a fallback message
        return back()->with('warning', "Export to {$format} for {$reportType} report is under development.");
        
        // This would be implemented when Excel exports are set up
        /*
        $exportClass = null;
        
        // Map report types to export classes
        switch ($reportType) {
            case 'daily':
            case 'summary':
                $exportClass = new AttendanceExport($data);
                break;
            case 'leave':
                $exportClass = new LeaveExport($data);
                break;
            case 'late':
                $exportClass = new LateExport($data);
                break;
            case 'early-departure':
                $exportClass = new EarlyDepartureExport($data);
                break;
        }
        
        if ($exportClass) {
            if ($format == 'excel') {
                return Excel::download($exportClass, $fileName . '.xlsx');
            } else { // csv
                return Excel::download($exportClass, $fileName . '.csv');
            }
        }
        
        return back()->with('error', 'Export failed. Invalid report type.');
        */
    }
    
    /**
     * Calculate minutes late for an attendance record.
     */
    private function calculateMinutesLate($attendance)
    {
        if (!$attendance->clock_in_time) {
            return '-';
        }
        
        $shift = $attendance->employee->shift;
        $shiftStartTime = Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $shift->start_time);
        $gracePeriod = $shift->grace_period_minutes;
        
        $maxAllowedTime = $shiftStartTime->copy()->addMinutes($gracePeriod);
        
        if ($attendance->clock_in_time->lte($maxAllowedTime)) {
            return 0;
        }
        
        return $attendance->clock_in_time->diffInMinutes($shiftStartTime);
    }
    
    /**
     * Calculate minutes early for an attendance record.
     */
    private function calculateMinutesEarly($attendance)
    {
        if (!$attendance->clock_out_time) {
            return '-';
        }
        
        $shift = $attendance->employee->shift;
        $shiftEndTime = Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $shift->end_time);
        
        // Handle overnight shifts
        if ($shift->end_time < $shift->start_time) {
            $shiftEndTime->addDay();
        }
        
        // If clock out is after shift end, not early
        if ($attendance->clock_out_time->gte($shiftEndTime)) {
            return 0;
        }
        
        return $shiftEndTime->diffInMinutes($attendance->clock_out_time);
    }
}