@extends('layouts.app')

@section('title', 'Bulk Allocate Leave Credits')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Bulk Allocate Leave Credits</h1>
        <a href="{{ route('leave-credits.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Leave Credits
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Allocation Details</h6>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form action="{{ route('leave-credits.process-bulk-allocate') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label>Allocation Type <span class="text-danger">*</span></label>
                    <div class="form-check mb-2">
                        <input type="radio" class="form-check-input" id="allocation_type_all" name="allocation_type" value="all" {{ old('allocation_type', 'all') == 'all' ? 'checked' : '' }}>
                        <label class="form-check-label" for="allocation_type_all">All Active Employees</label>
                    </div>
                    <div class="form-check mb-2">
                        <input type="radio" class="form-check-input" id="allocation_type_dept" name="allocation_type" value="department" {{ old('allocation_type') == 'department' ? 'checked' : '' }}>
                        <label class="form-check-label" for="allocation_type_dept">By Department</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" id="allocation_type_selected" name="allocation_type" value="selected" {{ old('allocation_type') == 'selected' ? 'checked' : '' }}>
                        <label class="form-check-label" for="allocation_type_selected">Selected Employees</label>
                    </div>
                </div>
                
                <div id="department_selector" class="form-group" style="display: none;">
                    <label for="department_id">Department <span class="text-danger">*</span></label>
                    <select class="form-control" id="department_id" name="department_id">
                        <option value="">-- Select Department --</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div id="employee_selector" class="form-group" style="display: none;">
                    <label for="employee_ids">Employees <span class="text-danger">*</span></label>
                    <select class="form-control" id="employee_ids" name="employee_ids[]" multiple size="10">
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ in_array($employee->id, old('employee_ids', [])) ? 'selected' : '' }}>
                                {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_id }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Hold Ctrl (or Cmd on Mac) to select multiple employees.</small>
                </div>
                
                <div class="form-group">
                    <label for="leave_type_id">Leave Type <span class="text-danger">*</span></label>
                    <select class="form-control" id="leave_type_id" name="leave_type_id" required>
                        <option value="">-- Select Leave Type --</option>
                        @foreach($leaveTypes as $leaveType)
                            <option value="{{ $leaveType->id }}" {{ old('leave_type_id') == $leaveType->id ? 'selected' : '' }}>
                                {{ $leaveType->name }} {{ $leaveType->is_paid ? '(Paid)' : '(Unpaid)' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="fiscal_year">Fiscal Year <span class="text-danger">*</span></label>
                    <select class="form-control" id="fiscal_year" name="fiscal_year" required>
                        @php
                            $currentYear = date('Y');
                            $startYear = $currentYear - 1;
                            $endYear = $currentYear + 2;
                        @endphp
                        @for($year = $startYear; $year <= $endYear; $year++)
                            <option value="{{ $year }}" {{ old('fiscal_year', $defaultYear) == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="allocated_days">Allocated Days <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="allocated_days" name="allocated_days" value="{{ old('allocated_days') }}" step="0.01" min="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="expiry_date">Expiry Date</label>
                    <input type="date" class="form-control" id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}">
                    <small class="text-muted">Leave blank if there is no expiry date.</small>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Allocate Credits
                </button>
                <a href="{{ route('leave-credits.index') }}" class="btn btn-secondary ml-2">
                    <i class="fas fa-times mr-1"></i> Cancel
                </a>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show/hide department and employee selectors based on allocation type
        const allocationTypeRadios = document.querySelectorAll('input[name="allocation_type"]');
        const departmentSelector = document.getElementById('department_selector');
        const employeeSelector = document.getElementById('employee_selector');
        
        function updateSelectors() {
            const selectedValue = document.querySelector('input[name="allocation_type"]:checked').value;
            
            if (selectedValue === 'department') {
                departmentSelector.style.display = 'block';
                employeeSelector.style.display = 'none';
            } else if (selectedValue === 'selected') {
                departmentSelector.style.display = 'none';
                employeeSelector.style.display = 'block';
            } else {
                departmentSelector.style.display = 'none';
                employeeSelector.style.display = 'none';
            }
        }
        
        allocationTypeRadios.forEach(function(radio) {
            radio.addEventListener('change', updateSelectors);
        });
        
        // Initial setup
        updateSelectors();
    });
</script>
@endsection