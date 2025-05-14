@extends('layouts.app')

@section('title', 'Employee Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">My Dashboard</h1>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Profile Summary -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Profile</h5>
                    <a href="{{ route('profile') }}" class="btn btn-sm btn-primary">Edit Profile</a>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if($employee->profile_picture)
                        <img src="{{ asset('storage/' . $employee->profile_picture) }}" class="rounded-circle" width="120" height="120" alt="{{ $employee->full_name }}">
                        @else
                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px; font-size: 48px;">
                            {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                        </div>
                        @endif
                    </div>
                    <h4>{{ $employee->full_name }}</h4>
                    <p class="text-muted">{{ $employee->employee_id }}</p>
                    <div class="row mt-4">
                        <div class="col-6 text-right border-right">
                            <h6>Department</h6>
                            <p>{{ $employee->department->name }}</p>
                        </div>
                        <div class="col-6 text-left">
                            <h6>Position</h6>
                            <p>{{ $employee->role->name }}</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Joined: {{ $employee->date_hired->format('M d, Y') }}</small>
                        </div>
                        <div class="col-6 text-right">
                            <small class="text-muted">Shift: {{ $employee->shift->name }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Attendance Status -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Today's Attendance</h5>
                </div>
                <div class="card-body text-center">
                    @if($todayAttendance)
                        <div class="mb-3">
                            <span class="badge badge-pill badge-{{ $todayAttendance->attendanceStatus->name == 'Present' ? 'success' : ($todayAttendance->attendanceStatus->name == 'Late' ? 'warning' : ($todayAttendance->attendanceStatus->name == 'Absent' ? 'danger' : 'secondary')) }} p-3">
                                {{ $todayAttendance->attendanceStatus->name }}
                            </span>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Clock In</h6>
                                        <h5>{{ $todayAttendance->clock_in_time ? $todayAttendance->clock_in_time->format('h:i A') : 'N/A' }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Clock Out</h6>
                                        <h5>{{ $todayAttendance->clock_out_time ? $todayAttendance->clock_out_time->format('h:i A') : 'N/A' }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if($todayAttendance->remarks)
                        <div class="mt-4">
                            <h6>Remarks:</h6>
                            <p>{{ $todayAttendance->remarks }}</p>
                        </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            <h5 class="alert-heading">No attendance record for today.</h5>
                            <p>Please visit the HR counter to clock in.</p>
                        </div>
                    @endif
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('my-attendance') }}" class="btn btn-primary">View My Attendance History</a>
                </div>
            </div>
        </div>
        
        <!-- Shift Information -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">My Shift Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <h5>{{ $employee->shift->name }}</h5>
                            <p class="text-muted">{{ $employee->shift->description }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 text-center">
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6>Start Time</h6>
                                    <h4>{{ \Carbon\Carbon::parse($employee->shift->start_time)->format('h:i A') }}</h4>
                                </div>
                            </div>
                            <div class="small">
                                Grace period: {{ $employee->shift->grace_period_minutes }} minutes
                            </div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6>End Time</h6>
                                    <h4>{{ \Carbon\Carbon::parse($employee->shift->end_time)->format('h:i A') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Attendance -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Attendance</h5>
                    <a href="{{ route('my-attendance') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Clock In</th>
                                    <th>Clock Out</th>
                                    <th>Work Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAttendance as $attendance)
                                <tr>
                                    <td>{{ $attendance->date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $attendance->attendanceStatus->name == 'Present' ? 'success' : ($attendance->attendanceStatus->name == 'Late' ? 'warning' : ($attendance->attendanceStatus->name == 'Absent' ? 'danger' : 'secondary')) }}">
                                            {{ $attendance->attendanceStatus->name }}
                                        </span>
                                    </td>
                                    <td>{{ $attendance->clock_in_time ? $attendance->clock_in_time->format('h:i A') : 'N/A' }}</td>
                                    <td>{{ $attendance->clock_out_time ? $attendance->clock_out_time->format('h:i A') : 'N/A' }}</td>
                                    <td>{{ $attendance->work_hours ?? 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No attendance records found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leave Requests -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Leave Requests</h5>
                    <a href="{{ route('leaves.create') }}" class="btn btn-sm btn-primary">Request Leave</a>
                </div>
                <div class="card-body">
                    @forelse($leaves as $leave)
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge badge-{{ $leave->status == 'approved' ? 'success' : ($leave->status == 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($leave->status) }}
                                    </span>
                                    <small class="text-muted ml-2">{{ $leave->created_at->format('M d, Y') }}</small>
                                </div>
                            </div>
                            <div class="mt-2">
                                <i class="far fa-calendar"></i> {{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }}
                                <div class="small text-muted">{{ $leave->start_date->diffInDays($leave->end_date) + 1 }} days</div>
                            </div>
                            <div class="mt-2">
                                <a href="{{ route('leaves.show', $leave) }}" class="btn btn-sm btn-outline-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted">No leave requests found.</p>
                    @endforelse
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('leaves.index') }}" class="btn btn-primary">View All Leaves</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection