@extends('layouts.app')

@section('title', 'Employee Management')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Employee Management</h1>
        <a href="{{ route('employees.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Employee
        </a>
    </div>
    
    <!-- Search and Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Search & Filter Employees</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('employees.index') }}" method="GET" class="mb-0">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Search by name, email, ID..."
                                   value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="department" class="form-select">
                            <option value="">-- All Departments --</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ request('department') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="role" class="form-select">
                            <option value="">-- All Positions --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </div>
                </div>
                
                @if(request('search') || request('department') || request('role'))
                    <div class="mt-3">
                        <a href="{{ route('employees.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                        <span class="ms-2 text-muted">
                            Showing results for: 
                            @if(request('search'))
                                <span class="badge bg-info text-white">Search: "{{ request('search') }}"</span>
                            @endif
                            @if(request('department'))
                                <span class="badge bg-info text-white">
                                    Department: {{ $departments->find(request('department'))->name ?? '' }}
                                </span>
                            @endif
                            @if(request('role'))
                                <span class="badge bg-info text-white">
                                    Position: {{ $roles->find(request('role'))->name ?? '' }}
                                </span>
                            @endif
                        </span>
                    </div>
                @endif
            </form>
        </div>
    </div>
    
    <!-- Employees List Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Employee List</h6>
            <div>
                <a href="{{ route('employees.archive') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-archive"></i> View Archive
                </a>
                <a href="{{ route('employees.import-form') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-file-import"></i> Import Employees
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if($employees->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employees as $employee)
                                <tr>
                                    <td>{{ $employee->employee_id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($employee->profile_picture)
                                                <img src="{{ asset('storage/' . $employee->profile_picture) }}" alt="{{ $employee->first_name }}" class="rounded-circle mr-2" width="32" height="32">
                                            @else
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white mr-2" style="width: 32px; height: 32px; font-weight: bold;">
                                                    {{ strtoupper(substr($employee->first_name, 0, 1)) }}{{ strtoupper(substr($employee->last_name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="ml-2">
                                                {{ $employee->first_name }} {{ $employee->last_name }}
                                                <div class="small text-muted">{{ $employee->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $employee->department->name ?? 'Not Assigned' }}</td>
                                    <td>{{ $employee->role->name ?? 'Not Assigned' }}</td>
                                    <td>
                                        @if($employee->phone)
                                            <div><i class="fas fa-phone-alt text-primary"></i> {{ $employee->phone }}</div>
                                        @endif
                                        <div class="small">
                                            @if($employee->address)
                                                <i class="fas fa-map-marker-alt text-muted"></i> {{ Str::limit($employee->address, 30) }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($employee->status == 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($employee->status == 'on_leave')
                                            <span class="badge bg-warning text-dark">On Leave</span>
                                        @elseif($employee->status == 'suspended')
                                            <span class="badge bg-danger">Suspended</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($employee->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to archive this employee?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} of {{ $employees->total() }} employees
                    </div>
                    <div>
                        {{ $employees->appends(request()->except('page'))->links() }}
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No employees found.
                    @if(request('search') || request('department') || request('role'))
                        <a href="{{ route('employees.index') }}" class="alert-link">Clear search filters</a> to see all employees.
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Add any additional scripts needed for the employee page
    document.addEventListener('DOMContentLoaded', function() {
        // Enable tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Quick filter change submission
        const departmentSelect = document.querySelector('select[name="department"]');
        const roleSelect = document.querySelector('select[name="role"]');
        
        if (departmentSelect && roleSelect) {
            departmentSelect.addEventListener('change', function() {
                if (document.querySelector('input[name="search"]').value || this.value) {
                    this.form.submit();
                }
            });
            
            roleSelect.addEventListener('change', function() {
                if (document.querySelector('input[name="search"]').value || this.value) {
                    this.form.submit();
                }
            });
        }
    });
</script>
@endsection