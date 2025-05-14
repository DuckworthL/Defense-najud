@extends('layouts.app')

@section('title', 'Employee Archive')

@section('styles')
<style>
    .table-actions {
        white-space: nowrap;
    }
    .badge {
        font-size: 85%;
    }
    .archive-header {
        background-color: #f8f9fc;
        padding: 1rem;
        border-radius: 0.35rem;
        margin-bottom: 1rem;
    }
    .bulk-action-row {
        background-color: #eef2f9;
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 15px;
        display: none;
    }
    .archived-date {
        color: #6c757d;
        font-size: 0.85rem;
    }
    .employee-info {
        display: flex;
        align-items: center;
    }
    .employee-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        margin-right: 10px;
        background-color: #4e73df;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    .preview-table {
        max-height: 300px;
        overflow-y: auto;
    }
    .progress {
        height: 20px;
    }
    .progress-bar {
        transition: width 0.5s ease;
    }
    .preview-count {
        background-color: #e8f4fd;
        border-left: 4px solid #4e73df;
        padding: 10px 15px;
        margin-bottom: 15px;
    }
    .preview-avatar {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 5px;
        background-color: #4e73df;
        color: white;
        font-size: 10px;
        font-weight: bold;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Employee Archive</h1>
        <a href="{{ route('employees.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Employees
        </a>
    </div>

    <div class="archive-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4><i class="fas fa-archive text-primary"></i> Archived Employees</h4>
                <p class="text-muted">Manage employees who have been archived from the system.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0">Total archived: <span class="badge bg-secondary">{{ $employees->total() }}</span></p>
            </div>
        </div>
    </div>

    <!-- Search and Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Search & Filter Archived Employees</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('employees.archive') }}" method="GET" class="mb-0">
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
                        <a href="{{ route('employees.archive') }}" class="btn btn-sm btn-outline-secondary">
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

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Archived Employee List</h6>
            <div>
                <button type="button" class="btn btn-sm btn-outline-primary toggle-bulk-actions" id="toggleBulkActions">
                    <i class="fas fa-tasks"></i> Batch Restore
                </button>
            </div>
        </div>
        <div class="card-body">
            <form id="bulkActionForm" action="/employee-archive/bulk-restore" method="POST">
                @csrf
                <div class="bulk-action-row" id="bulkActionRow">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                                <label class="form-check-label" for="selectAll">
                                    Select All
                                </label>
                                <span class="ms-2 badge bg-primary" id="selectedCount">0 selected</span>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-success" id="previewRestoreBtn" disabled>
                                    <i class="fas fa-eye"></i> Preview Restore
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="submitBulkAction('delete')" data-bs-toggle="tooltip" title="This action cannot be undone">
                                    <i class="fas fa-trash-alt"></i> Delete Selected
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            
                @if($employees->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th width="40px"></th>
                                    <th>ID</th>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Status</th>
                                    <th>Archived Date</th>
                                    <th class="table-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $employee)
                                    <tr data-employee-name="{{ $employee->first_name }} {{ $employee->last_name }}" 
                                        data-employee-id="{{ $employee->employee_id }}" 
                                        data-employee-dept="{{ $employee->department->name ?? 'No Department' }}" 
                                        data-employee-role="{{ $employee->role->name ?? 'No Position' }}" 
                                        data-employee-avatar="{{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}">
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input employee-checkbox" type="checkbox" name="selected_ids[]" value="{{ $employee->id }}">
                                            </div>
                                        </td>
                                        <td>{{ $employee->employee_id }}</td>
                                        <td>
                                            <div class="employee-info">
                                                @if($employee->profile_picture)
                                                    <img src="{{ asset('storage/' . $employee->profile_picture) }}" alt="{{ $employee->first_name }}" class="employee-avatar">
                                                @else
                                                    <div class="employee-avatar">
                                                        {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <div>{{ $employee->first_name }} {{ $employee->last_name }}</div>
                                                    <div class="text-muted small">{{ $employee->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $employee->department->name ?? 'No Department' }}</td>
                                        <td>{{ $employee->role->name ?? 'No Position' }}</td>
                                        <td><span class="badge bg-secondary">Archived</span></td>
                                        <td>
                                            <div>{{ $employee->deleted_at->format('M d, Y') }}</div>
                                            <small class="archived-date">{{ $employee->deleted_at->format('h:i A') }}</small>
                                        </td>
                                        <td class="table-actions">
                                            <div class="btn-group" role="group">
                                                <!-- Individual Restore Button -->
                                                <form action="/employee-restore/{{ $employee->id }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to restore this employee?')">
                                                        <i class="fas fa-trash-restore"></i> Restore
                                                    </button>
                                                </form>
                                                
                                                <!-- Delete Button -->
                                                <form action="/employee-archive/{{ $employee->id }}/force-delete" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to permanently delete this employee? This action cannot be undone!')">
                                                        <i class="fas fa-trash-alt"></i> Delete
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
                            Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} of {{ $employees->total() }} archived employees
                        </div>
                        <div>
                            {{ $employees->appends(request()->except('page'))->links() }}
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> There are no archived employees.
                        @if(request('search') || request('department') || request('role'))
                            <a href="{{ route('employees.archive') }}" class="alert-link">Clear search filters</a> to see all archived employees.
                        @endif
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

<!-- Restore Preview Modal -->
<div class="modal fade" id="restorePreviewModal" tabindex="-1" aria-labelledby="restorePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="restorePreviewModalLabel"><i class="fas fa-trash-restore"></i> Restore Preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="preview-count mb-3">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-users me-2 text-primary"></i>
                        <span>You are about to restore <strong id="employeeCount">0</strong> employees</span>
                    </div>
                </div>
                
                <div class="preview-table">
                    <table class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="40px">#</th>
                                <th>Employee</th>
                                <th>ID</th>
                                <th>Department</th>
                                <th>Position</th>
                            </tr>
                        </thead>
                        <tbody id="previewTableBody">
                            <!-- Preview rows will be added here dynamically -->
                        </tbody>
                    </table>
                </div>
                
                <div id="restoreProgressContainer" class="mt-4" style="display: none;">
                    <h6>Restore Progress</h6>
                    <div class="progress">
                        <div id="restoreProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%"></div>
                    </div>
                    <p class="text-center mt-2" id="restoreProgressText">Initializing...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmRestoreBtn">
                    <i class="fas fa-trash-restore"></i> Confirm Restore
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle bulk action row
        const toggleBtn = document.getElementById('toggleBulkActions');
        const bulkActionRow = document.getElementById('bulkActionRow');
        
        toggleBtn.addEventListener('click', function() {
            if (bulkActionRow.style.display === 'block') {
                bulkActionRow.style.display = 'none';
                toggleBtn.innerHTML = '<i class="fas fa-tasks"></i> Batch Restore';
            } else {
                bulkActionRow.style.display = 'block';
                toggleBtn.innerHTML = '<i class="fas fa-times"></i> Hide Options';
            }
        });
        
        // Select all functionality
        const selectAllCheckbox = document.getElementById('selectAll');
        const employeeCheckboxes = document.querySelectorAll('.employee-checkbox');
        const selectedCountBadge = document.getElementById('selectedCount');
        const previewRestoreBtn = document.getElementById('previewRestoreBtn');
        
        function updateSelectedCount() {
            const checkedCount = document.querySelectorAll('.employee-checkbox:checked').length;
            selectedCountBadge.textContent = checkedCount + ' selected';
            
            // Enable or disable the preview button based on selection count
            if (checkedCount > 0) {
                previewRestoreBtn.removeAttribute('disabled');
            } else {
                previewRestoreBtn.setAttribute('disabled', 'disabled');
            }
        }
        
        selectAllCheckbox.addEventListener('change', function() {
            employeeCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            updateSelectedCount();
        });
        
        // Update select all checkbox state when individual checkboxes change
        employeeCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = [...employeeCheckboxes].every(cb => cb.checked);
                const allUnchecked = [...employeeCheckboxes].every(cb => !cb.checked);
                
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = !allChecked && !allUnchecked;
                
                updateSelectedCount();
            });
        });
        
        // Preview restore functionality
        const previewModal = new bootstrap.Modal(document.getElementById('restorePreviewModal'));
        const previewTableBody = document.getElementById('previewTableBody');
        const employeeCountElem = document.getElementById('employeeCount');
        const confirmRestoreBtn = document.getElementById('confirmRestoreBtn');
        
        previewRestoreBtn.addEventListener('click', function() {
            const selectedCheckboxes = document.querySelectorAll('.employee-checkbox:checked');
            const selectedCount = selectedCheckboxes.length;
            
            if (selectedCount === 0) {
                alert('Please select at least one employee to restore.');
                return;
            }
            
            // Clear previous rows
            previewTableBody.innerHTML = '';
            
            // Update the count
            employeeCountElem.textContent = selectedCount;
            
            // Add row for each selected employee
            selectedCheckboxes.forEach((checkbox, index) => {
                const row = checkbox.closest('tr');
                const employeeName = row.dataset.employeeName;
                const employeeId = row.dataset.employeeId;
                const employeeDept = row.dataset.employeeDept;
                const employeeRole = row.dataset.employeeRole;
                const employeeAvatar = row.dataset.employeeAvatar;
                
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td>
                        <span class="preview-avatar">${employeeAvatar}</span>
                        ${employeeName}
                    </td>
                    <td>${employeeId}</td>
                    <td>${employeeDept}</td>
                    <td>${employeeRole}</td>
                `;
                
                previewTableBody.appendChild(tr);
            });
            
            // Reset progress indicators
            document.getElementById('restoreProgressContainer').style.display = 'none';
            document.getElementById('restoreProgressBar').style.width = '0%';
            document.getElementById('restoreProgressText').textContent = 'Initializing...';
            
            // Show the modal
            previewModal.show();
        });
        
        // Confirm restore button handler
        confirmRestoreBtn.addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('.employee-checkbox:checked')).map(cb => cb.value);
            const totalItems = selectedIds.length;
            
            // Show progress indicators
            document.getElementById('restoreProgressContainer').style.display = 'block';
            
            // Disable the confirm button during processing
            confirmRestoreBtn.disabled = true;
            confirmRestoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            
            // Start the restore process
            processRestore(selectedIds, 0, totalItems);
        });
        
        // Restore process with batch processing
        function processRestore(selectedIds, currentIndex, totalItems) {
            // Update progress bar
            const progressPercentage = Math.round((currentIndex / totalItems) * 100);
            document.getElementById('restoreProgressBar').style.width = progressPercentage + '%';
            document.getElementById('restoreProgressText').textContent = `Processing ${currentIndex} of ${totalItems} employees (${progressPercentage}%)`;
            
            // If we've processed all items, submit the form
            if (currentIndex >= totalItems) {
                document.getElementById('restoreProgressText').textContent = 'Completing restoration...';
                document.getElementById('restoreProgressBar').style.width = '100%';
                
                // Submit the form after a small delay to show 100% progress
                setTimeout(() => {
                    const form = document.getElementById('bulkActionForm');
                    form.action = "/employee-archive/bulk-restore";
                    form.submit();
                }, 500);
                
                return;
            }
            
            // Process animation in batches to show progress
            // This is just simulating progress for UX purposes
            // The actual restore will happen when the form submits
            setTimeout(() => {
                // Process next batch
                processRestore(selectedIds, currentIndex + 1, totalItems);
            }, 100); // Adjust timing for smoother or faster animation
        }
        
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
        
        // Initial count update
        updateSelectedCount();
    });
    
    // Submit bulk action form for delete operations
    function submitBulkAction(action) {
        if (action !== 'delete') return;
        
        const selectedCheckboxes = document.querySelectorAll('.employee-checkbox:checked');
        
        if (selectedCheckboxes.length === 0) {
            alert('Please select at least one employee.');
            return;
        }
        
        if (confirm('Are you sure you want to permanently delete the selected employees? This action cannot be undone!')) {
            const form = document.getElementById('bulkActionForm');
            form.action = "/employee-archive/bulk-force-delete";
            form.method = 'POST';
            
            // Remove any existing _method field first
            const existingMethodField = form.querySelector('input[name="_method"]');
            if (existingMethodField) {
                existingMethodField.remove();
            }
            
            // Then add a new one for DELETE
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);
            
            form.submit();
        }
    }
</script>
@endsection