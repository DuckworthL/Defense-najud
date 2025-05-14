@extends('layouts.app')

@section('title', 'HR Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">HR Dashboard</h1>
        </div>
    </div>

    <!-- System Info Bar -->
    <div class="system-info-bar mb-4">
        <div>
            <i class="far fa-calendar-alt"></i> Current Date/Time (UTC): <span id="dashboard-datetime">{{ date('Y-m-d H:i:s') }}</span>
        </div>
        <div>
            <i class="fas fa-user"></i> Logged in as: {{ auth()->user()->employee_id ?? auth()->user()->username ?? auth()->user()->email }} (HR Manager)
        </div>
    </div>

    <!-- Quick Action Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100 quick-action-card">
                <div class="card-body">
                    <div class="d-flex flex-column justify-content-between h-100">
                        <div>
                            <h5 class="card-title">Present</h5>
                            <p class="mb-0">View present employees</p>
                        </div>
                        <div class="text-end mt-3">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="{{ route('attendance.index', ['attendance_status_id' => 1]) }}" class="btn btn-light btn-sm w-100">
                        <i class="fas fa-eye me-1"></i> View Details
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white h-100 quick-action-card">
                <div class="card-body">
                    <div class="d-flex flex-column justify-content-between h-100">
                        <div>
                            <h5 class="card-title">Late</h5>
                            <p class="mb-0">Manage late arrivals</p>
                        </div>
                        <div class="text-end mt-3">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="{{ route('attendance.index', ['attendance_status_id' => 2]) }}" class="btn btn-light btn-sm w-100">
                        <i class="fas fa-eye me-1"></i> View Details
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-danger text-white h-100 quick-action-card">
                <div class="card-body">
                    <div class="d-flex flex-column justify-content-between h-100">
                        <div>
                            <h5 class="card-title">Absent</h5>
                            <p class="mb-0">Track absences</p>
                        </div>
                        <div class="text-end mt-3">
                            <i class="fas fa-user-times"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="{{ route('attendance.index', ['attendance_status_id' => 3]) }}" class="btn btn-light btn-sm w-100">
                        <i class="fas fa-eye me-1"></i> View Details
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-secondary text-white h-100 quick-action-card">
                <div class="card-body">
                    <div class="d-flex flex-column justify-content-between h-100">
                        <div>
                            <h5 class="card-title">On Leave</h5>
                            <p class="mb-0">View leave records</p>
                        </div>
                        <div class="text-end mt-3">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="{{ route('leaves.index', ['status' => 'approved']) }}" class="btn btn-light btn-sm w-100">
                        <i class="fas fa-eye me-1"></i> View Details
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Today's Attendance Cards -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Today's Attendance</h5>
                    <div>
                        <a href="{{ route('attendance.dashboard') }}" class="btn btn-sm btn-primary me-2">
                            <i class="fas fa-tachometer-alt me-1"></i> Realtime Dashboard
                        </a>
                        <a href="{{ route('counter.dashboard') }}" class="btn btn-sm btn-info me-2">
                            <i class="fas fa-desktop me-1"></i> Counter
                        </a>
                        <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-list me-1"></i> All Records
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row" id="attendance-cards">
                        @forelse($recentAttendance as $attendance)
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-start border-4 border-{{ $attendance->attendanceStatus->name == 'Present' ? 'success' : ($attendance->attendanceStatus->name == 'Late' ? 'warning' : ($attendance->attendanceStatus->name == 'Absent' ? 'danger' : 'secondary')) }}">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            @if($attendance->employee->profile_picture)
                                            <img src="{{ asset('storage/' . $attendance->employee->profile_picture) }}" class="rounded-circle" width="60" height="60" alt="{{ $attendance->employee->full_name }}">
                                            @else
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                {{ strtoupper(substr($attendance->employee->first_name, 0, 1) . substr($attendance->employee->last_name, 0, 1)) }}
                                            </div>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $attendance->employee->full_name }}</h6>
                                            <div class="small text-muted">{{ $attendance->employee->employee_id }}</div>
                                            <div class="d-flex align-items-center mt-1">
                                                <span class="badge bg-{{ $attendance->attendanceStatus->name == 'Present' ? 'success' : ($attendance->attendanceStatus->name == 'Late' ? 'warning' : ($attendance->attendanceStatus->name == 'Absent' ? 'danger' : 'secondary')) }}">
                                                    {{ $attendance->attendanceStatus->name }}
                                                </span>
                                                <span class="ms-2 small">
                                                    @if($attendance->clock_in_time)
                                                    <i class="far fa-clock"></i> {{ $attendance->clock_in_time->format('h:i A') }}
                                                    @endif
                                                    @if($attendance->clock_out_time)
                                                    - {{ $attendance->clock_out_time->format('h:i A') }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2 d-flex justify-content-end">
                                        <a href="{{ route('attendance.edit', $attendance) }}" class="btn btn-sm btn-outline-primary me-2">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="{{ route('employees.show', $attendance->employee) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-user"></i> Profile
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> No attendance records found for today.
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('attendance.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Attendance Record
                    </a>
                </div>
            </div>
        </div>

        <!-- Pending Approvals -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pending Approvals</h5>
                    <a href="{{ route('leaves.index', ['status' => 'pending']) }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    @forelse($pendingLeaves as $leave)
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="mb-1">{{ $leave->employee->full_name }}</h6>
                            <div class="small text-muted mb-2">{{ $leave->employee->department->name }}</div>
                            <div class="d-flex justify-content-between">
                                <div>
                                    <i class="far fa-calendar"></i> {{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }}
                                    <div class="small text-muted">{{ $leave->start_date->diffInDays($leave->end_date) + 1 }} days</div>
                                </div>
                                <a href="{{ route('leaves.show', $leave) }}" class="btn btn-sm btn-primary">Review</a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> No pending leave requests.
                    </div>
                    @endforelse
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('leaves.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Create Leave
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('employees.index') }}" class="text-decoration-none">
                                <div class="card bg-light h-100 border-0 shadow-sm quick-action-card">
                                    <div class="card-body text-center p-4">
                                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                                        <h5 class="card-title">Manage Employees</h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('reports.index') }}" class="text-decoration-none">
                                <div class="card bg-light h-100 border-0 shadow-sm quick-action-card">
                                    <div class="card-body text-center p-4">
                                        <i class="fas fa-chart-bar fa-3x text-success mb-3"></i>
                                        <h5 class="card-title">Generate Reports</h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('employees.import-form') }}" class="text-decoration-none">
                                <div class="card bg-light h-100 border-0 shadow-sm quick-action-card">
                                    <div class="card-body text-center p-4">
                                        <i class="fas fa-file-import fa-3x text-info mb-3"></i>
                                        <h5 class="card-title">Import Employees</h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('counter.search') }}" class="text-decoration-none">
                                <div class="card bg-light h-100 border-0 shadow-sm quick-action-card">
                                    <div class="card-body text-center p-4">
                                        <i class="fas fa-search fa-3x text-warning mb-3"></i>
                                        <h5 class="card-title">Search Employee</h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Update dashboard datetime
        setInterval(function() {
            const now = new Date();
            const year = now.getUTCFullYear();
            const month = String(now.getUTCMonth() + 1).padStart(2, '0');
            const day = String(now.getUTCDate()).padStart(2, '0');
            const hours = String(now.getUTCHours()).padStart(2, '0');
            const minutes = String(now.getUTCMinutes()).padStart(2, '0');
            const seconds = String(now.getUTCSeconds()).padStart(2, '0');
            
            const formattedDatetime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
            document.getElementById('dashboard-datetime').textContent = formattedDatetime;
        }, 1000);
    });
</script>
@en