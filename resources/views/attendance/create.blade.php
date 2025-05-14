@extends('layouts.app')

@section('title', 'Add Attendance Record')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .form-group {
        margin-bottom: 1rem;
    }
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #d1d3e2;
        border-radius: 0.35rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    .employee-info {
        display: flex;
        align-items: center;
    }
    .employee-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        margin-right: 10px;
        background-color: #4e73df;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Add New Attendance Record</h1>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Attendance Details</h6>
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
            
            <form action="{{ route('attendance.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="employee_id">Employee</label>
                    <select class="form-control employee-select" name="employee_id" id="employee_id" required>
                        <option value="">-- Select Employee --</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" data-first-name="{{ $employee->first_name }}" data-last-name="{{ $employee->last_name }}" data-employee-id="{{ $employee->employee_id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_id }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="text" class="form-control datepicker" id="date" name="date" placeholder="Select date" value="{{ old('date', date('d/m/Y')) }}" required>
                </div>
                
                <div class="form-group">
                    <label for="status_id">Status</label>
                    <select class="form-control" name="status_id" id="status_id" required>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" {{ old('status_id') == $status->id ? 'selected' : '' }}>
                                {{ $status->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="clock_in">Clock In Time</label>
                    <input type="text" class="form-control timepicker" id="clock_in" name="clock_in" placeholder="Select clock in time" value="{{ old('clock_in') }}" required>
                </div>
                
                <div class="form-group">
                    <label for="clock_out">Clock Out Time</label>
                    <input type="text" class="form-control timepicker" id="clock_out" name="clock_out" placeholder="Select clock out time" value="{{ old('clock_out') }}">
                </div>
                
                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea class="form-control" id="remarks" name="remarks" rows="3">{{ old('remarks') }}</textarea>
                </div>
                
                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_verified" name="is_verified" value="1" {{ old('is_verified') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_verified">
                            Mark as Verified
                        </label>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Save Attendance
                    </button>
                    <a href="{{ route('attendance.index') }}" class="btn btn-secondary ml-2">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize date picker
        flatpickr(".datepicker", {
            dateFormat: "d/m/Y",
            allowInput: true,
            defaultDate: "today"
        });
        
        // Initialize time pickers
        flatpickr(".timepicker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "d/m/Y H:i K",
            time_24hr: false,
            allowInput: true,
            defaultDate: new Date()
        });
        
        // Initialize Select2 for employee dropdown with search
        $('.employee-select').select2({
            placeholder: "Search for an employee...",
            allowClear: true,
            width: '100%',
            templateResult: formatEmployee,
            templateSelection: formatEmployeeSelection
        });
        
        // Custom format for dropdown items
        function formatEmployee(employee) {
            if (!employee.id) {
                return employee.text;
            }
            
            const firstName = $(employee.element).data('first-name');
            const lastName = $(employee.element).data('last-name');
            const employeeId = $(employee.element).data('employee-id');
            const initials = firstName.charAt(0) + lastName.charAt(0);
            
            const $employee = $(
                `<div class="employee-info">
                    <div class="employee-avatar">${initials.toUpperCase()}</div>
                    <div>
                        <div><strong>${firstName} ${lastName}</strong></div>
                        <div class="text-muted small">${employeeId}</div>
                    </div>
                </div>`
            );
            
            return $employee;
        }
        
        // Custom format for selected item
        function formatEmployeeSelection(employee) {
            if (!employee.id) {
                return employee.text;
            }
            
            const firstName = $(employee.element).data('first-name');
            const lastName = $(employee.element).data('last-name');
            const employeeId = $(employee.element).data('employee-id');
            
            return `${firstName} ${lastName} (${employeeId})`;
        }
    });
</script>
@endsection