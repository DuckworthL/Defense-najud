@extends('layouts.app')

@section('title', 'Generate Reports')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Generate Reports</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Attendance Report</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('reports.attendance') }}" method="GET" target="_blank">
                                        <div class="mb-3">
                                            <label for="attendance_start_date" class="form-label">Start Date</label>
                                            <input type="date" class="form-control" id="attendance_start_date" name="start_date" required value="{{ date('Y-m-01') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="attendance_end_date" class="form-label">End Date</label>
                                            <input type="date" class="form-control" id="attendance_end_date" name="end_date" required value="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="attendance_department_id" class="form-label">Department</label>
                                            <select class="form-select" id="attendance_department_id" name="department_id">
                                                <option value="">All Departments</option>
                                                @foreach($departments as $department)
                                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="attendance_report_type" class="form-label">Report Type</label>
                                            <select class="form-select" id="attendance_report_type" name="report_type" required>
                                                <option value="daily">Daily Detailed Report</option>
                                                <option value="summary">Summary Report</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="attendance_export_format" class="form-label">Export Format</label>
                                            <select class="form-select" id="attendance_export_format" name="export_format">
                                                <option value="">View in Browser</option>
                                                <option value="pdf">PDF</option>
                                                <option value="excel">Excel</option>
                                                <option value="csv">CSV</option>
                                            </select>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-file-alt"></i> Generate Report
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">Leave Report</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('reports.leave') }}" method="GET" target="_blank">
                                        <div class="mb-3">
                                            <label for="leave_start_date" class="form-label">Start Date</label>
                                            <input type="date" class="form-control" id="leave_start_date" name="start_date" required value="{{ date('Y-m-01') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="leave_end_date" class="form-label">End Date</label>
                                            <input type="date" class="form-control" id="leave_end_date" name="end_date" required value="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="leave_department_id" class="form-label">Department</label>
                                            <select class="form-select" id="leave_department_id" name="department_id">
                                                <option value="">All Departments</option>
                                                @foreach($departments as $department)
                                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="leave_status" class="form-label">Leave Status</label>
                                            <select class="form-select" id="leave_status" name="status">
                                                <option value="">All Statuses</option>
                                                <option value="pending">Pending</option>
                                                <option value="approved">Approved</option>
                                                <option value="rejected">Rejected</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="leave_export_format" class="form-label">Export Format</label>
                                            <select class="form-select" id="leave_export_format" name="export_format">
                                                <option value="">View in Browser</option>
                                                <option value="pdf">PDF</option>
                                                <option value="excel">Excel</option>
                                                <option value="csv">CSV</option>
                                            </select>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-calendar-alt"></i> Generate Report
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-warning text-white">
                                    <h5 class="mb-0">Late Arrivals Report</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('reports.late') }}" method="GET" target="_blank">
                                        <div class="mb-3">
                                            <label for="late_start_date" class="form-label">Start Date</label>
                                            <input type="date" class="form-control" id="late_start_date" name="start_date" required value="{{ date('Y-m-01') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="late_end_date" class="form-label">End Date</label>
                                            <input type="date" class="form-control" id="late_end_date" name="end_date" required value="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="late_department_id" class="form-label">Department</label>
                                            <select class="form-select" id="late_department_id" name="department_id">
                                                <option value="">All Departments</option>
                                                @foreach($departments as $department)
                                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="late_export_format" class="form-label">Export Format</label>
                                            <select class="form-select" id="late_export_format" name="export_format">
                                                <option value="">View in Browser</option>
                                                <option value="pdf">PDF</option>
                                                <option value="excel">Excel</option>
                                                <option value="csv">CSV</option>
                                            </select>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-warning">
                                                <i class="fas fa-clock"></i> Generate Report
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="mb-0">Early Departure Report</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('reports.early-departure') }}" method="GET" target="_blank">
                                        <div class="mb-3">
                                            <label for="early_departure_start_date" class="form-label">Start Date</label>
                                            <input type="date" class="form-control" id="early_departure_start_date" name="start_date" required value="{{ date('Y-m-01') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="early_departure_end_date" class="form-label">End Date</label>
                                            <input type="date" class="form-control" id="early_departure_end_date" name="end_date" required value="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="early_departure_department_id" class="form-label">Department</label>
                                            <select class="form-select" id="early_departure_department_id" name="department_id">
                                                <option value="">All Departments</option>
                                                @foreach($departments as $department)
                                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="early_departure_export_format" class="form-label">Export Format</label>
                                            <select class="form-select" id="early_departure_export_format" name="export_format">
                                                <option value="">View in Browser</option>
                                                <option value="pdf">PDF</option>
                                                <option value="excel">Excel</option>
                                                <option value="csv">CSV</option>
                                            </select>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-sign-out-alt"></i> Generate Report
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set min/max date for date inputs
        const dateInputs = {
            'attendance': ['start_date', 'end_date'],
            'leave': ['start_date', 'end_date'],
            'late': ['start_date', 'end_date'],
            'early_departure': ['start_date', 'end_date']
        };
        
        // Set up date range validation for each report type
        for (const [reportType, fields] of Object.entries(dateInputs)) {
            const startDateInput = document.getElementById(`${reportType}_${fields[0]}`);
            const endDateInput = document.getElementById(`${reportType}_${fields[1]}`);
            
            if (startDateInput && endDateInput) {
                // Set min date for end date based on start date
                startDateInput.addEventListener('change', function() {
                    endDateInput.min = this.value;
                });
                
                // Set max date for start date based on end date
                endDateInput.addEventListener('change', function() {
                    startDateInput.max = this.value;
                });
            }
        }
    });
</script>
@endsection