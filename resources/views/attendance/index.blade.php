@extends('layouts.app')

@section('title', 'Attendance Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Filter Attendance</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('attendance.index') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" value="{{ request('date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label for="department_id" class="form-label">Department</label>
                            <select class="form-select" id="department_id" name="department_id">
                                <option value="">All Departments</option>
                                @foreach(\App\Models\Department::where('is_active', true)->get() as $department)
                                <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="attendance_status_id" class="form-label">Status</label>
                            <select class="form-select" id="attendance_status_id" name="attendance_status_id">
                                <option value="">All Statuses</option>
                                @foreach(\App\Models\AttendanceStatus::all() as $status)
                                <option value="{{ $status->id }}" {{ request('attendance_status_id') == $status->id ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                                <i class="fas fa-sync"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-3">
        <div class="col-md-8">
            <h1>Attendance Records</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('attendance.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Attendance Record
            </a>
            <a href="{{ route('counter.dashboard') }}" class="btn btn-success">
                <i class="fas fa-desktop"></i> Counter Terminal
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Clock In</th>
                                    <th>Clock Out</th>
                                    <th>Work Hours</th>
                                    <th>Verified By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $attendance)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-2">
                                                @if($attendance->employee->profile_picture)
                                                <img src="{{ asset('storage/' . $attendance->employee->profile_picture) }}" class="rounded-circle" width="40" height="40" alt="{{ $attendance->employee->full_name }}">
                                                @else
                                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    {{ strtoupper(substr($attendance->employee->first_name, 0, 1) . substr($attendance->employee->last_name, 0, 1)) }}
                                                </div>
                                                @endif
                                            </div>
                                            <div>
                                                {{ $attendance->employee->full_name }}<br>
                                                <small class="text-muted">{{ $attendance->employee->employee_id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $attendance->date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $attendance->attendanceStatus->name == 'Present' ? 'success' : ($attendance->attendanceStatus->name == 'Late' ? 'warning' : ($attendance->attendanceStatus->name == 'Absent' ? 'danger' : 'secondary')) }}">
                                            {{ $attendance->attendanceStatus->name }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($attendance->clock_in_time)
                                            {{ $attendance->clock_in_time->format('h:i A') }}
                                            @if($attendance->is_clock_in_reset)
                                            <i class="fas fa-edit text-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Modified by {{ $attendance->clockInResetBy->full_name ?? 'Admin' }}"></i>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($attendance->clock_out_time)
                                            {{ $attendance->clock_out_time->format('h:i A') }}
                                            @if($attendance->is_clock_out_reset)
                                            <i class="fas fa-edit text-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Modified by {{ $attendance->clockOutResetBy->full_name ?? 'Admin' }}"></i>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $attendance->work_hours ?? '-' }}</td>
                                    <td>
                                        @if($attendance->verified_by)
                                            {{ $attendance->verifiedBy->full_name }}<br>
                                            <small class="text-muted">{{ $attendance->verification_time->format('h:i A') }}</small>
                                        @else
                                            <span class="badge bg-warning">Not Verified</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('attendance.show', $attendance) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('attendance.edit', $attendance) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $attendance->id }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        
                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal{{ $attendance->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $attendance->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteModalLabel{{ $attendance->id }}">Confirm Delete</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete this attendance record for {{ $attendance->employee->full_name }} on {{ $attendance->date->format('M d, Y') }}?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <form action="{{ route('attendance.destroy', $attendance) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No attendance records found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $attendances->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endsection