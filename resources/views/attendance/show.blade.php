@extends('layouts.app')

@section('title', 'Attendance Details')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Attendance Record Details</h5>
                    <div>
                        <a href="{{ route('attendance.edit', $attendance) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-2 text-center mb-3 mb-md-0">
                            @if($attendance->employee->profile_picture)
                            <img src="{{ asset('storage/' . $attendance->employee->profile_picture) }}" class="img-fluid rounded-circle" style="max-width: 100px;" alt="{{ $attendance->employee->full_name }}">
                            @else
                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 100px; font-size: 36px;">
                                {{ strtoupper(substr($attendance->employee->first_name, 0, 1) . substr($attendance->employee->last_name, 0, 1)) }}
                            </div>
                            @endif
                        </div>
                        <div class="col-md-10">
                            <h4>{{ $attendance->employee->full_name }}</h4>
                            <p class="mb-1">
                                <strong>Employee ID:</strong> {{ $attendance->employee->employee_id }}
                            </p>
                            <p class="mb-1">
                                <strong>Department:</strong> {{ $attendance->employee->department->name }}
                            </p>
                            <p class="mb-1">
                                <strong>Position:</strong> {{ $attendance->employee->role->name }}
                            </p>
                            <p class="mb-1">
                                <strong>Shift:</strong> {{ $attendance->employee->shift->name }} 
                                ({{ \Carbon\Carbon::parse($attendance->employee->shift->start_time)->format('h:i A') }} - 
                                {{ \Carbon\Carbon::parse($attendance->employee->shift->end_time)->format('h:i A') }})
                            </p>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Attendance Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Date:</strong> {{ $attendance->date->format('l, F d, Y') }}</p>
                                    <p>
                                        <strong>Status:</strong> 
                                        <span class="badge bg-{{ $attendance->attendanceStatus->name == 'Present' ? 'success' : ($attendance->attendanceStatus->name == 'Late' ? 'warning' : ($attendance->attendanceStatus->name == 'Absent' ? 'danger' : 'secondary')) }}">
                                            {{ $attendance->attendanceStatus->name }}
                                        </span>
                                    </p>
                                    <p><strong>Working Hours:</strong> {{ $attendance->work_hours ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p>
                                        <strong>Clock In:</strong> 
                                        {{ $attendance->clock_in_time ? $attendance->clock_in_time->format('h:i:s A') : 'N/A' }}
                                        @if($attendance->is_clock_in_reset)
                                            <span class="badge bg-info">Modified</span>
                                        @endif
                                    </p>
                                    <p>
                                                                                <strong>Clock Out:</strong> 
                                        {{ $attendance->clock_out_time ? $attendance->clock_out_time->format('h:i:s A') : 'N/A' }}
                                        @if($attendance->is_clock_out_reset)
                                            <span class="badge bg-info">Modified</span>
                                        @endif
                                    </p>
                                    <p>
                                        <strong>Verification:</strong>
                                        @if($attendance->verified_by)
                                            Verified by {{ $attendance->verifiedBy->full_name }}
                                            at {{ $attendance->verification_time->format('h:i A') }}
                                        @else
                                            <span class="badge bg-warning">Not Verified</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            
                            @if($attendance->remarks)
                            <div class="mt-3">
                                <strong>Remarks:</strong>
                                <p>{{ $attendance->remarks }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    @if($attendance->is_clock_in_reset || $attendance->is_clock_out_reset)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Modification History</h6>
                        </div>
                        <div class="card-body">
                            @if($attendance->is_clock_in_reset)
                            <div class="mb-3">
                                <h6><i class="fas fa-history"></i> Clock In Modified</h6>
                                <p><strong>Modified By:</strong> {{ $attendance->clockInResetBy->full_name ?? 'System' }}</p>
                                <p><strong>Reason:</strong> {{ $attendance->clock_in_reset_reason }}</p>
                            </div>
                            @endif
                            
                            @if($attendance->is_clock_out_reset)
                            <div>
                                <h6><i class="fas fa-history"></i> Clock Out Modified</h6>
                                <p><strong>Modified By:</strong> {{ $attendance->clockOutResetBy->full_name ?? 'System' }}</p>
                                <p><strong>Reason:</strong> {{ $attendance->clock_out_reset_reason }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <div>
                            <a href="{{ route('attendance.edit', $attendance) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="{{ route('employees.show', $attendance->employee) }}" class="btn btn-info">
                                <i class="fas fa-user"></i> Employee Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
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
@endsection