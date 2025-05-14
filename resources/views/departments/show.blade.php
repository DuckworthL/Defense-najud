@extends('layouts.app')

@section('title', 'Department Details')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Department Details</h5>
                    <div>
                        <a href="{{ route('departments.edit', $department) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @if($department->employees->isEmpty())
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h4>{{ $department->name }}</h4>
                            <p class="text-muted">
                                <span class="badge bg-{{ $department->is_active ? 'success' : 'danger' }}">
                                    {{ $department->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                            <p>{{ $department->description ?? 'No description available.' }}</p>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5>Department Statistics</h5>
                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <p><strong>Total Employees:</strong> {{ $department->employees->count() }}</p>
                                            <p><strong>Active Employees:</strong> {{ $department->employees->where('status', 'active')->count() }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p><strong>Created:</strong> {{ $department->created_at->format('M d, Y') }}</p>
                                            <p><strong>Last Updated:</strong> {{ $department->updated_at->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2 mb-3">Employees in this Department</h5>
                            
                            @if($department->employees->isEmpty())
                                <div class="alert alert-info">
                                    No employees assigned to this department yet.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Employee</th>
                                                <th>Position</th>
                                                <th>Email</th>
                                                <th>Shift</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($department->employees as $employee)
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
                                                <td>{{ $employee->role->name }}</td>
                                                <td>{{ $employee->email }}</td>
                                                <td>{{ $employee->shift->name }}</td>
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
                    <a href="{{ route('departments.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Departments
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
                Are you sure you want to delete the department: {{ $department->name }}?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('departments.destroy', $department) }}" method="POST" class="d-inline">
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