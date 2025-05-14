@extends('layouts.app')

@section('title', 'Employee Details')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="card-body text-center">
                    @if($employee->profile_picture)
                    <img src="{{ asset('storage/' . $employee->profile_picture) }}" class="rounded-circle img-fluid" style="width: 150px;" alt="{{ $employee->full_name }}">
                    @else
                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center mx-auto" style="width: 150px; height: 150px; font-size: 60px;">
                        {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                    </div>
                    @endif
                    <h5 class="my-3">{{ $employee->full_name }}</h5>
                    <p class="text-muted mb-1">{{ $employee->role->name }}</p>
                    <p class="text-muted mb-4">{{ $employee->department->name }}</p>
                    <div class="d-flex justify-content-center mb-2">
                        <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <button type="button" class="btn btn-outline-danger ms-1" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Status</h5>
                </div>
                <div class="card-body text-center">
                    <span class="badge bg-{{ $employee->status == 'active' ? 'success' : 'danger' }} p-2" style="font-size: 1rem;">
                        {{ ucfirst($employee->status) }}
                    </span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Personal Information</h5>
                    <span class="badge bg-primary">ID: {{ $employee->employee_id }}</span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <p class="mb-0 fw-bold">Full Name</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0">{{ $employee->full_name }}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <p class="mb-0 fw-bold">Email</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0">{{ $employee->email }}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <p class="mb-0 fw-bold">Phone</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0">{{ $employee->phone ?? 'Not provided' }}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <p class="mb-0 fw-bold">Address</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0">{{ $employee->address ?? 'Not provided' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Employment Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-5">
                                    <p class="mb-0 fw-bold">Department</p>
                                </div>
                                <div class="col-sm-7">
                                    <p class="text-muted mb-0">{{ $employee->department->name }}</p>
                                </div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-sm-5">
                                    <p class="mb-0 fw-bold">Role</p>
                                </div>
                                <div class="col-sm-7">
                                    <p class="text-muted mb-0">{{ $employee->role->name }}</p>
                                </div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-sm-5">
                                    <p class="mb-0 fw-bold">Date Hired</p>
                                </div>
                                <div class="col-sm-7">
                                    <p class="text-muted mb-0">{{ $employee->date_hired->format('F d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <!-- Replace the Recent Activities section with this corrected code -->
<div class="col-md-12">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Activities</h5>
            <div>
                <a href="{{ route('attendance.index', ['employee_id' => $employee->id]) }}" class="btn btn-sm btn-primary me-2">
                    <i class="fas fa-clipboard-list"></i> Attendance History
                </a>
                <a href="{{ route('leaves.index', ['employee_id' => $employee->id]) }}" class="btn btn-sm btn-info">
                    <i class="fas fa-calendar-alt"></i> Leave History
                </a>
            </div>
        </div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                @forelse($employee->attendance()->orderBy('date', 'desc')->take(5)->get() as $attendance)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-clipboard-check me-2"></i>
                            <span class="fw-bold">{{ $attendance->date->format('M d, Y') }}:</span>
                            
                            @if($attendance->attendanceStatus)
                                <span class="badge" style="background-color: {{ $attendance->attendanceStatus->color_code ?? '#6c757d' }}">
                                    {{ $attendance->attendanceStatus->name }}
                                </span>
                            @else
                                <span class="badge bg-secondary">Unknown</span>
                            @endif
                            
                            @if($attendance->clock_in_time)
                                Clock in: {{ $attendance->clock_in_time->format('h:i A') }}
                            @endif
                            
                            @if($attendance->clock_out_time)
                                , Clock out: {{ $attendance->clock_out_time->format('h:i A') }}
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="list-group-item text-center">
                        <p class="text-muted mb-0">No recent attendance records found.</p>
                    </li>
                @endforelse
            </ul>
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
                Are you sure you want to delete this employee: {{ $employee->full_name }}?
                <p class="text-danger mt-2">
                    <strong>Warning:</strong> This will also delete all attendance records and leaves associated with this employee.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection