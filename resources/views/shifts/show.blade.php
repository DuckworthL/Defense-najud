@extends('layouts.app')

@section('title', 'Shift Details')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Shift Details</h5>
                    <div>
                        <a href="{{ route('shifts.edit', $shift) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @if($shift->employees->isEmpty())
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h4>{{ $shift->name }}</h4>
                            <p class="text-muted">
                                <span class="badge bg-{{ $shift->is_active ? 'success' : 'danger' }}">
                                    {{ $shift->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                            <p>{{ $shift->description ?? 'No description available.' }}</p>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5>Shift Schedule</h5>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <p>
                                                <strong>Start Time:</strong><br>
                                                <span class="badge bg-primary p-2" style="font-size: 1rem;">
                                                    <i class="fas fa-clock"></i> 
                                                    {{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }}
                                                </span>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p>
                                                <strong>End Time:</strong><br>
                                                <span class="badge bg-info p-2" style="font-size: 1rem;">
                                                    <i class="fas fa-clock"></i> 
                                                    {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    <p>
                                        <strong>Grace Period:</strong> {{ $shift->grace_period_minutes }} minutes<br>
                                        <small class="text-muted">Employees arriving within {{ $shift->grace_period_minutes }} minutes after start time will be marked as Present</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2 mb-3">Employees in this Shift</h5>
                            
                            @if($shift->employees->isEmpty())
                                <div class="alert alert-info">
                                    No employees assigned to this shift yet.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Employee</th>
                                                <th>Department</th>
                                                <th>Position</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($shift->employees as $employee)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            @if($employee->profile_picture)
                                                            <img src="{{ asset('storage/' . $employee->profile_picture) }}" class="rounded-circle" width="40" height="40" alt="{{ $employee->full_name }}">
                                                            @else
                                                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                                                            </div>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            {{ $employee->full_name }}<br>
                                                            <small class="text-muted">{{ $employee->employee_id }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $employee->department->name }}</td>
                                                <td>{{ $employee->role->name }}</td>
                                                <td>{{ $employee->email }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $employee->status == 'active' ? 'success' : 'danger' }}">
                                                        {{ ucfirst($employee->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('employees.show', $employee) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('shifts.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Shifts
                    </a>
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
                Are you sure you want to delete the shift: {{ $shift->name }}?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('shifts.destroy', $shift) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
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