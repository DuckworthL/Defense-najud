<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CounterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeaveCreditController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\LeaveCalendarController;
use App\Http\Controllers\AttendanceDashboardController;
use App\Models\Attendance;
use App\Models\LeaveCredit;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    
    // Redirect to appropriate dashboard based on role
    Route::get('/dashboard', function () {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif (auth()->user()->isHR()) {
            return redirect()->route('hr.dashboard');
        } else {
            return redirect()->route('employee.dashboard');
        }
    })->name('dashboard');
    
    // Admin routes
    Route::middleware(['role:Admin'])->group(function () {
        Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
        
        // Department and Shift management
        Route::resource('departments', DepartmentController::class);
        Route::resource('shifts', ShiftController::class);
    });
    
    // Admin and HR routes
    Route::middleware(['role:Admin,HR'])->group(function () {
        Route::get('/hr/dashboard', [DashboardController::class, 'hrDashboard'])->name('hr.dashboard');
        
        // Employee archive routes - FIXED ROUTES
        Route::get('/employee-archive', [EmployeeController::class, 'archive'])->name('employees.archive');
        
        // CRITICAL FIX: Handle POST to /employee-archive
        Route::post('/employee-archive', [EmployeeController::class, 'bulkRestore']);
        
        // Individual employee restore routes
        Route::post('/employee-restore/{id}', [EmployeeController::class, 'restore'])->name('employees.restore');
        Route::post('/employee-archive/{id}/restore', [EmployeeController::class, 'restore']);
        
        // Bulk operations
        Route::post('/employee-archive/bulk-restore', [EmployeeController::class, 'bulkRestore'])->name('employees.bulk-restore');
        Route::delete('/employee-archive/{id}/force-delete', [EmployeeController::class, 'forceDelete'])->name('employees.force-delete');
        Route::post('/employee-archive/bulk-force-delete', [EmployeeController::class, 'bulkForceDelete'])->name('employees.bulk-force-delete');
        
        // Employee management
        Route::resource('employees', EmployeeController::class);
        Route::post('/employees/{employee}/change-password', [EmployeeController::class, 'changePassword'])->name('employees.change-password');
        Route::get('/employees-import', [EmployeeController::class, 'importForm'])->name('employees.import-form');
        Route::post('/employees-import', [EmployeeController::class, 'import'])->name('employees.import');

        // Real-time Attendance Dashboard
        Route::get('/attendance-dashboard', [AttendanceDashboardController::class, 'index'])->name('attendance.dashboard');
        Route::get('/attendance-dashboard/summary', [AttendanceDashboardController::class, 'getSummaryData'])->name('attendance.dashboard.summary');
        Route::get('/attendance-dashboard/departments', [AttendanceDashboardController::class, 'getDepartmentData'])->name('attendance.dashboard.departments');
        Route::get('/attendance-dashboard/live-updates', [AttendanceDashboardController::class, 'getLiveUpdates'])->name('attendance.dashboard.live');
        Route::get('/attendance-dashboard/trend', [AttendanceDashboardController::class, 'getTrendData'])->name('attendance.dashboard.trend');
        Route::get('/attendance-dashboard/exceptions', [AttendanceDashboardController::class, 'getExceptionData'])->name('attendance.dashboard.exceptions');

        // Leave Calendar
        Route::get('/leave-calendar', [LeaveCalendarController::class, 'index'])->name('leaves.calendar');
        Route::get('/leave-calendar/events', [LeaveCalendarController::class, 'getEvents'])->name('leaves.calendar.events');
        Route::get('/leave-calendar/department-coverage', [LeaveCalendarController::class, 'getDepartmentCoverage'])->name('leaves.calendar.coverage');
        Route::get('/leave-calendar/conflicts', [LeaveCalendarController::class, 'getLeaveConflicts'])->name('leaves.calendar.conflicts');
        
        // Attendance management
        Route::resource('attendance', AttendanceController::class);
        
        // Leave approval
        Route::post('/leaves/{leave}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
        Route::post('/leaves/{leave}/reject', [LeaveController::class, 'reject'])->name('leaves.reject');
        
        // Counter terminal routes
        Route::get('/counter', [CounterController::class, 'dashboard'])->name('counter.dashboard');
        Route::get('/counter/search', [CounterController::class, 'search'])->name('counter.search');
        Route::post('/counter/process-search', [CounterController::class, 'processSearch'])->name('counter.process-search');
        Route::get('/counter/verify/{id}', [CounterController::class, 'verify'])->name('counter.verify');
        Route::post('/counter/process-verification', [CounterController::class, 'processVerification'])->name('counter.process-verification');
        
        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/attendance', [ReportController::class, 'attendanceReport'])->name('reports.attendance');
        Route::get('/reports/leave', [ReportController::class, 'leaveReport'])->name('reports.leave');
        Route::get('/reports/late', [ReportController::class, 'lateReport'])->name('reports.late');
        Route::get('/reports/early-departure', [ReportController::class, 'earlyDepartureReport'])->name('reports.early-departure');
        
        // Leave Types Management
        Route::resource('leave-types', LeaveTypeController::class);
        
        // Leave Credits Management - IMPORTANT: Custom routes BEFORE resource route
        Route::get('/leave-credits/bulk-allocate', [LeaveCreditController::class, 'bulkAllocate'])->name('leave-credits.bulk-allocate');
        Route::post('/leave-credits/process-bulk-allocate', [LeaveCreditController::class, 'processBulkAllocate'])->name('leave-credits.process-bulk-allocate');
        Route::resource('leave-credits', LeaveCreditController::class);
    });
    
    // Employee routes
    Route::middleware(['role:Admin,HR,Employee'])->group(function () {
        Route::get('/employee/dashboard', [DashboardController::class, 'employeeDashboard'])->name('employee.dashboard');
        
        // Employee profile
        Route::get('/profile', function () {
            return view('employees.profile', ['employee' => auth()->user()]);
        })->name('profile');
        
        Route::put('/profile', function (Request $request) {
            $user = auth()->user();
            $request->validate([
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'profile_picture' => 'nullable|image|max:2048',
            ]);
            
            if ($request->hasFile('profile_picture')) {
                if ($user->profile_picture) {
                    Storage::disk('public')->delete($user->profile_picture);
                }
                $user->profile_picture = $request->file('profile_picture')->store('profile_pictures', 'public');
            }
            
            $user->phone = $request->phone;
            $user->address = $request->address;
            $user->save();
            
            return redirect()->route('profile')->with('success', 'Profile updated successfully.');
        })->name('profile.update');
        
        Route::post('/change-password', function (Request $request) {
            $request->validate([
                'current_password' => 'required|current_password',
                'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            ]);
            
            $user = auth()->user();
            $user->password = Hash::make($request->password);
            $user->save();
            
            return redirect()->route('profile')->with('success', 'Password changed successfully.');
        })->name('password.change');
        
        // Leave management for employees
        Route::resource('leaves', LeaveController::class);
        
        // Check leave credits (AJAX)
        Route::get('/leaves/check-credits', [LeaveController::class, 'checkCredits'])->name('leaves.check-credits');
        
        // My leave credits dashboard
        Route::get('/my-leave-credits', function () {
            $employeeId = auth()->id();
            $currentYear = Carbon::now()->year;
            
            // Get all leave credits for the current year
            $leaveCredits = LeaveCredit::where('employee_id', $employeeId)
                ->where('fiscal_year', $currentYear)
                ->with('leaveType')
                ->get();
                
            // Get leave statistics
            $leaves = \App\Models\Leave::where('employee_id', $employeeId)
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
            $pendingLeaves = \App\Models\Leave::where('employee_id', $employeeId)
                ->where('status', 'pending')
                ->count();
                
            // Get recent leave history
            $recentLeaves = \App\Models\Leave::where('employee_id', $employeeId)
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
        })->name('my-leave-credits');
        
        // View own attendance
        Route::get('/my-attendance', function (Request $request) {
            $query = Attendance::with('attendanceStatus')
                ->where('employee_id', auth()->id());
                
            if ($request->has('month') && $request->month) {
                $date = Carbon::createFromFormat('Y-m', $request->month);
                $query->whereMonth('date', $date->month)
                      ->whereYear('date', $date->year);
            } else {
                $currentMonth = Carbon::now();
                $query->whereMonth('date', $currentMonth->month)
                      ->whereYear('date', $currentMonth->year);
            }
            
            $attendances = $query->orderBy('date', 'desc')->get();
            
            return view('attendance.my-attendance', compact('attendances'));
        })->name('my-attendance');
    });
    Route::post('/toggle-sidebar', function (Request $request) {
        $collapsed = $request->input('collapsed', false);
        session(['sidebar_collapsed' => $collapsed]);
        return response()->json(['success' => true]);
    })->middleware('auth');
});