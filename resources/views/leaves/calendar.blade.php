@extends('layouts.app')

@section('title', 'Leave Calendar')

@section('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />
<link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css' rel='stylesheet'>
<style>
    .calendar-container {
        height: calc(100vh - 250px);
        min-height: 600px;
    }
    .fc-event {
        cursor: pointer;
    }
    .fc-day-today {
        background-color: rgba(0, 120, 255, 0.1) !important;
    }
    .filter-bar {
        background-color: #f8f9fc;
        border-radius: 0.35rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    .legend-item {
        display: inline-block;
        margin-right: 15px;
    }
    .legend-color {
        display: inline-block;
        width: 15px;
        height: 15px;
        margin-right: 5px;
        border-radius: 3px;
    }
    .conflict-indicator {
        background-color: #ffecb3;
        border-left: 4px solid #ff9800;
        padding: 10px 15px;
        margin-bottom: 15px;
        border-radius: 4px;
    }
    .coverage-critical {
        color: #e74a3b;
        font-weight: bold;
    }
    .coverage-warning {
        color: #f6c23e;
        font-weight: bold;
    }
    .coverage-good {
        color: #1cc88a;
        font-weight: bold;
    }
    .conflict-date {
        font-weight: bold;
        color: #ff9800;
    }
    .conflict-list {
        max-height: 300px;
        overflow-y: auto;
    }
    #coverageChart {
        height: 250px;
        width: 100%;
    }
    .department-stats {
        background-color: #f8f9fc;
        border-radius: 0.35rem;
        padding: 1rem;
    }
    .stat-card {
        background-color: white;
        border-radius: 0.35rem;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 0.15rem 0.5rem rgba(0, 0, 0, 0.1);
        border-left: 4px solid #4e73df;
    }
    .conflict-panel {
        display: none;
    }
    .planning-tool {
        background-color: white;
        border-radius: 0.35rem;
        padding: 1rem;
        margin-top: 1rem;
        box-shadow: 0 0.15rem 0.5rem rgba(0, 0, 0, 0.1);
        border-top: 4px solid #4e73df;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Leave Calendar</h1>
        <div>
            <button type="button" id="leaveConflictBtn" class="d-none d-sm-inline-block btn btn-sm btn-warning shadow-sm">
                <i class="fas fa-exclamation-triangle fa-sm text-white-50"></i> Check Leave Conflicts
            </button>
            @if(auth()->user()->isAdmin() || auth()->user()->isHR())
                <a href="{{ route('leaves.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Create Leave
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-9">
            <!-- Filters -->
            <div class="filter-bar">
                <form id="filterForm" class="row g-3 align-items-center">
                    <div class="col-md-3">
                        <label for="department_filter" class="form-label">Department</label>
                        <select id="department_filter" class="form-select" {{ $userIsRestricted ? 'disabled' : '' }}>
                            @if(!$userIsRestricted)
                                <option value="">All Departments</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ $selectedDepartment == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            @else
                                @foreach($departments as $department)
                                    @if($selectedDepartment == $department->id)
                                        <option value="{{ $department->id }}" selected>{{ $department->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="leave_type_filter" class="form-label">Leave Type</label>
                        <select id="leave_type_filter" class="form-select">
                            <option value="">All Types</option>
                            @foreach($leaveTypes as $leaveType)
                                <option value="{{ $leaveType->id }}">{{ $leaveType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status_filter" class="form-label">Status</label>
                        <select id="status_filter" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="button" id="applyFilters" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <button type="button" id="resetFilters" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Calendar Legend -->
            <div class="mb-3">
                <div class="legend-item">
                    <span class="legend-color" style="background-color: #1cc88a;"></span> Approved
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background-color: #f6c23e;"></span> Pending
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background-color: #e74a3b;"></span> Rejected
                </div>
            </div>
            
            <!-- Leave Conflict Alert Panel (hidden by default) -->
            <div id="conflictPanel" class="conflict-panel mb-3">
                <div class="card border-left-warning shadow">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-exclamation-triangle"></i> Leave Coverage Issues Detected
                        </h6>
                        <button type="button" class="close" id="closeConflictPanel">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="conflictSummary" class="mb-3"></div>
                        <div id="conflictDetails" class="conflict-list"></div>
                    </div>
                </div>
            </div>
            
            <!-- Calendar Container -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div id="calendar" class="calendar-container"></div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <!-- Department Coverage Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Department Coverage</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h6 id="selectedDate" class="font-weight-bold">{{ date('Y-m-d') }}</h6>
                    </div>
                    
                    <div class="department-stats" id="departmentStats">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading department data...</p>
                        </div>
                    </div>
                    
                    <div id="coverageChartContainer" class="mt-4">
                        <h6 class="font-weight-bold text-primary mb-2">Coverage Chart</h6>
                        <canvas id="coverageChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Leave Planning Tool -->
            <div class="planning-tool">
                <h6 class="font-weight-bold text-primary mb-3">Leave Planning Tool</h6>
                
                <form id="planningForm">
                    <div class="mb-3">
                        <label for="plan_start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="plan_start_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="plan_end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="plan_end_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="plan_department" class="form-label">Department</label>
                        <select id="plan_department" class="form-select" {{ $userIsRestricted ? 'disabled' : '' }}>
                            @if(!$userIsRestricted)
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ $selectedDepartment == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            @else
                                @foreach($departments as $department)
                                    @if($selectedDepartment == $department->id)
                                        <option value="{{ $department->id }}" selected>{{ $department->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="d-grid">
                        <button type="button" id="checkAvailability" class="btn btn-primary">
                            <i class="fas fa-calendar-check"></i> Check Availability
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Leave Details Modal -->
<div class="modal fade" id="leaveDetailsModal" tabindex="-1" aria-labelledby="leaveDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="leaveDetailsModalLabel">Leave Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <th width="35%">Employee:</th>
                            <td id="modal_employee_name"></td>
                        </tr>
                        <tr>
                            <th>Department:</th>
                            <td id="modal_department"></td>
                        </tr>
                        <tr>
                            <th>Leave Type:</th>
                            <td id="modal_leave_type"></td>
                        </tr>
                        <tr>
                            <th>Duration:</th>
                            <td id="modal_duration"></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td id="modal_status"></td>
                        </tr>
                        <tr id="modal_payment_row">
                            <th>Payment:</th>
                            <td id="modal_payment"></td>
                        </tr>
                        <tr>
                            <th>Reason:</th>
                            <td id="modal_reason"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a id="viewLeaveBtn" href="#" class="btn btn-primary">View Leave</a>
            </div>
        </div>
    </div>
</div>

<!-- Leave Conflicts Modal -->
<div class="modal fade" id="leaveConflictModal" tabindex="-1" aria-labelledby="leaveConflictModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="leaveConflictModalLabel">
                    <i class="fas fa-exclamation-triangle"></i> Leave Coverage Analysis
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="conflictCheckForm" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="conflict_start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="conflict_start_date" required>
                        </div>
                        <div class="col-md-4">
                            <label for="conflict_end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="conflict_end_date" required>
                        </div>
                        <div class="col-md-4">
                            <label for="conflict_department" class="form-label">Department</label>
                            <select id="conflict_department" class="form-select" {{ $userIsRestricted ? 'disabled' : '' }}>
                                @if(!$userIsRestricted)
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ $selectedDepartment == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                @else
                                    @foreach($departments as $department)
                                        @if($selectedDepartment == $department->id)
                                            <option value="{{ $department->id }}" selected>{{ $department->name }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="mt-3 d-grid">
                        <button type="button" id="checkConflictsBtn" class="btn btn-warning">
                            <i class="fas fa-search"></i> Check Coverage
                        </button>
                    </div>
                </form>
                
                <div id="conflictResults" style="display: none;">
                    <h6 class="font-weight-bold mb-3">Coverage Analysis Results</h6>
                    
                    <div id="conflictSummaryInfo" class="alert alert-info">
                        <p class="mb-0">
                            <strong>Department:</strong> <span id="conflict_dept_name"></span> |
                            <strong>Total Employees:</strong> <span id="conflict_total_employees"></span>
                        </p>
                    </div>
                    
                    <div id="conflictWarning" class="alert alert-warning" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <span id="conflict_warning_text"></span>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Present</th>
                                    <th>On Leave</th>
                                    <th>Coverage</th>
                                </tr>
                            </thead>
                            <tbody id="conflictTableBody">
                                <!-- Coverage data will be populated here -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="coverageChartContainer" class="mt-4">
                        <canvas id="coverageTrendChart" height="200"></canvas>
                    </div>
                </div>
                
                <div id="conflictLoader" class="text-center py-5">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Analyzing leave coverage...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let calendar;
        let coverageChart;
        let coverageTrendChart;
        
        initCalendar();
        loadInitialDepartmentStats();
        setupEventListeners();
        
        function initCalendar() {
            const calendarEl = document.getElementById('calendar');
            
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth'
                },
                eventSources: [
                    {
                        url: '{{ route('leaves.calendar.events') }}',
                        method: 'GET',
                        extraParams: function() {
                            return {
                                department_id: $('#department_filter').val(),
                                leave_type_id: $('#leave_type_filter').val(),
                                status: $('#status_filter').val()
                            };
                        }
                    }
                ],
                eventClick: function(info) {
                    showLeaveDetails(info.event);
                },
                dateClick: function(info) {
                    updateDepartmentStats(info.dateStr);
                },
                dayMaxEvents: true, // allow "more" link when too many events
                loading: function(isLoading) {
                    if (!isLoading) {
                        // Check for conflicts on initial load
                        checkForConflictsInVisibleRange();
                    }
                }
            });
            
            calendar.render();
        }
        
        function loadInitialDepartmentStats() {
            const today = new Date().toISOString().slice(0, 10);
            updateDepartmentStats(today);
        }
        
        function updateDepartmentStats(dateStr) {
            $('#selectedDate').text(formatDate(dateStr));
            
            const departmentId = $('#department_filter').val();
            
            $('#departmentStats').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading department data...</p>
                </div>
            `);
            
            // Fetch department coverage stats
            $.ajax({
                url: '{{ route('leaves.calendar.coverage') }}',
                data: {
                    date: dateStr,
                    department_id: departmentId
                },
                success: function(response) {
                    if (!response.department) {
                        $('#departmentStats').html(`
                            <div class="alert alert-info">
                                Please select a department to view coverage information.
                            </div>
                        `);
                        return;
                    }
                    
                    // Calculate coverage color class
                    let coverageClass = 'coverage-good';
                    if (response.coverage_percentage < 70) {
                        coverageClass = 'coverage-critical';
                    } else if (response.coverage_percentage < 85) {
                        coverageClass = 'coverage-warning';
                    }
                    
                    let statsHtml = `
                        <div class="department-name mb-3">
                            <h5>${response.department}</h5>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="stat-card">
                                    <div class="stat-label">Present</div>
                                    <div class="stat-value">${response.present_employees}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stat-card">
                                    <div class="stat-label">On Leave</div>
                                    <div class="stat-value">${response.on_leave}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card border-left-primary">
                            <div class="stat-label">Coverage</div>
                            <div class="stat-value ${coverageClass}">${response.coverage_percentage}%</div>
                            <div class="progress mt-2" style="height: 10px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: ${response.coverage_percentage}%"></div>
                            </div>
                        </div>
                    `;
                    
                    // Add leave details if there are employees on leave
                    if (response.leave_details.length > 0) {
                        statsHtml += `
                            <div class="mt-3">
                                <h6 class="font-weight-bold">Employees on Leave:</h6>
                                <ul class="list-group">
                        `;
                        
                        response.leave_details.forEach(function(leave) {
                            statsHtml += `
                                <li class="list-group-item py-2">
                                    <div><strong>${leave.employee_name}</strong></div>
                                    <div class="text-muted small">${leave.leave_type}</div>
                                </li>
                            `;
                        });
                        
                        statsHtml += `
                                </ul>
                            </div>
                        `;
                    }
                    
                    $('#departmentStats').html(statsHtml);
                    
                    // Update the chart
                    updateCoverageChart(response.present_employees, response.on_leave);
                },
                error: function() {
                    $('#departmentStats').html(`
                        <div class="alert alert-danger">
                            Failed to load department coverage data.
                        </div>
                    `);
                }
            });
        }
        
        function updateCoverageChart(presentCount, leaveCount) {
            const ctx = document.getElementById('coverageChart');
            
            // Destroy existing chart if it exists
            if (coverageChart) {
                coverageChart.destroy();
            }
            
            // Create new chart
            coverageChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Present', 'On Leave'],
                    datasets: [{
                        data: [presentCount, leaveCount],
                        backgroundColor: [
                            '#1cc88a',  // green for present
                            '#f6c23e'   // yellow for on leave
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
        
        function showLeaveDetails(event) {
            const props = event.extendedProps;
            
            // Format dates
            const startDate = new Date(event.start).toLocaleDateString();
            let endDate = '';
            
            if (event.end) {
                // Subtract one day from end date (FullCalendar adds an extra day)
                const end = new Date(event.end);
                end.setDate(end.getDate() - 1);
                endDate = end.toLocaleDateString();
            } else {
                endDate = startDate;
            }
            
            // Populate modal
            $('#modal_employee_name').text(props.employee_name);
            $('#modal_department').text(props.department);
            $('#modal_leave_type').text(props.leave_type);
            $('#modal_duration').text(`${startDate} to ${endDate}`);
            
            // Set status with appropriate styling
            let statusHtml = '';
            switch(props.status.toLowerCase()) {
                case 'approved':
                    statusHtml = '<span class="badge bg-success">Approved</span>';
                    break;
                case 'pending':
                    statusHtml = '<span class="badge bg-warning">Pending</span>';
                    break;
                case 'rejected':
                    statusHtml = '<span class="badge bg-danger">Rejected</span>';
                    break;
                default:
                    statusHtml = props.status;
            }
            $('#modal_status').html(statusHtml);
            
            // Payment information
            if (props.requires_split_payment) {
                $('#modal_payment').html(`
                    <div class="badge bg-warning mb-2">Split Payment</div><br>
                    <small>
                        With Pay: ${props.with_pay_days} days<br>
                        Without Pay: ${props.without_pay_days} days
                    </small>
                `);
            } else if (props.is_without_pay) {
                $('#modal_payment').html('<span class="badge bg-warning">Without Pay</span>');
            } else {
                $('#modal_payment').html('<span class="badge bg-success">With Pay</span>');
            }
            
            $('#modal_reason').text(props.reason);
            
            // Set link to view leave
            $('#viewLeaveBtn').attr('href', `/leaves/${event.id}`);
            
            // Show modal
            const leaveModal = new bootstrap.Modal(document.getElementById('leaveDetailsModal'));
            leaveModal.show();
        }
        
        function checkForConflictsInVisibleRange() {
            const dateRange = calendar.view.getCurrentData().dateProfile.activeRange;
            const departmentId = $('#department_filter').val();
            
            if (!departmentId) return;
            
            const startDateStr = new Date(dateRange.start).toISOString().slice(0, 10);
            const endDateStr = new Date(dateRange.end).toISOString().slice(0, 10);
            
            $.ajax({
                url: '{{ route('leaves.calendar.conflicts') }}',
                data: {
                    start_date: startDateStr,
                    end_date: endDateStr,
                    department_id: departmentId
                },
                success: function(response) {
                    if (response.conflict_days && response.conflict_days.length > 0) {
                        // Show the conflict panel
                        const conflictCount = response.conflict_days.length;
                        
                        // Format the conflict summary
                        let summaryHtml = `
                            <p>
                                <strong>${conflictCount}</strong> day${conflictCount !== 1 ? 's' : ''} with low staff coverage detected 
                                in <strong>${response.department_name}</strong>.
                            </p>
                        `;
                        
                        // Format conflict details
                        let detailsHtml = '<ul class="list-group">';
                        
                        response.conflict_days.forEach(function(day) {
                            const employeeCount = Object.keys(day.employees_on_leave).length;
                            
                            detailsHtml += `
                                <li class="list-group-item">
                                    <div>
                                        <span class="conflict-date">${formatDate(day.date)} (${day.day_name})</span> -
                                        <span class="coverage-critical">${day.coverage}% coverage</span>
                                    </div>
                                    <div class="small mt-1">
                                        <span class="text-muted">${day.absent} out of ${response.total_employees} employees on leave</span>
                                    </div>
                                </li>
                            `;
                        });
                        
                        detailsHtml += '</ul>';
                        
                        // Update and show the panel
                        $('#conflictSummary').html(summaryHtml);
                        $('#conflictDetails').html(detailsHtml);
                        $('#conflictPanel').slideDown();
                    } else {
                        // Hide the panel if no conflicts
                        $('#conflictPanel').slideUp();
                    }
                }
            });
        }
        
        function setupEventListeners() {
            // Apply filters
            $('#applyFilters').click(function() {
                calendar.refetchEvents();
                
                const departmentId = $('#department_filter').val();
                const today = new Date().toISOString().slice(0, 10);
                updateDepartmentStats(today);
            });
            
            // Reset filters
            $('#resetFilters').click(function() {
                $('#department_filter').val('');
                $('#leave_type_filter').val('');
                $('#status_filter').val('');
                calendar.refetchEvents();
                
                const today = new Date().toISOString().slice(0, 10);
                updateDepartmentStats(today);
            });
            
            // Close conflict panel
            $('#closeConflictPanel').click(function() {
                $('#conflictPanel').slideUp();
            });
            
            // Leave conflict button
            $('#leaveConflictBtn').click(function() {
                const dateRange = calendar.view.getCurrentData().dateProfile.activeRange;
                
                // Set default dates in the form
                $('#conflict_start_date').val(new Date(dateRange.start).toISOString().slice(0, 10));
                $('#conflict_end_date').val(new Date(dateRange.end).toISOString().slice(0, 10));
                
                // Show the modal
                const conflictModal = new bootstrap.Modal(document.getElementById('leaveConflictModal'));
                conflictModal.show();
                
                // Hide results and reset form
                $('#conflictResults').hide();
                $('#conflictLoader').hide();
                $('#conflictWarning').hide();
            });
            
            // Check conflicts button
            $('#checkConflictsBtn').click(function() {
                // Validate form
                const startDate = $('#conflict_start_date').val();
                const endDate = $('#conflict_end_date').val();
                const departmentId = $('#conflict_department').val();
                
                if (!startDate || !endDate) {
                    alert('Please select start and end dates');
                    return;
                }
                
                // Show loader, hide results
                $('#conflictLoader').show();
                $('#conflictResults').hide();
                
                // Fetch conflict data
                $.ajax({
                    url: '{{ route('leaves.calendar.conflicts') }}',
                    data: {
                        start_date: startDate,
                        end_date: endDate,
                        department_id: departmentId
                    },
                    success: function(response) {
                        // Hide loader
                        $('#conflictLoader').hide();
                        
                        // Update department info
                        $('#conflict_dept_name').text(response.department_name);
                        $('#conflict_total_employees').text(response.total_employees);
                        
                        // Check if there are conflict days
                        if (response.conflict_days && response.conflict_days.length > 0) {
                            $('#conflict_warning_text').text(
                                `${response.conflict_days.length} day(s) have coverage below 70%.`
                            );
                            $('#conflictWarning').show();
                        } else {
                            $('#conflictWarning').hide();
                        }
                        
                        // Build table rows
                        let tableHtml = '';
                        let chartLabels = [];
                        let chartData = [];
                        
                        response.daily_coverage.forEach(function(day) {
                            // Add to chart data
                            chartLabels.push(formatDate(day.date));
                            chartData.push(day.coverage);
                            
                            // Determine coverage class
                            let coverageClass = 'coverage-good';
                            if (day.coverage < 70) {
                                coverageClass = 'coverage-critical';
                            } else if (day.coverage < 85) {
                                coverageClass = 'coverage-warning';
                            }
                            
                            tableHtml += `
                                <tr>
                                    <td>${formatDate(day.date)}</td>
                                    <td>${day.day_name}</td>
                                    <td>${day.present}</td>
                                    <td>${day.absent}</td>
                                    <td class="${coverageClass}">${day.coverage}%</td>
                                </tr>
                            `;
                        });
                        
                        $('#conflictTableBody').html(tableHtml);
                        
                        // Update chart
                        updateCoverageTrendChart(chartLabels, chartData);
                        
                        // Show results
                        $('#conflictResults').show();
                    },
                    error: function() {
                        $('#conflictLoader').hide();
                        alert('Failed to load coverage data. Please try again.');
                    }
                });
            });
            
            // Check availability button
            $('#checkAvailability').click(function() {
                const startDate = $('#plan_start_date').val();
                const endDate = $('#plan_end_date').val();
                const departmentId = $('#plan_department').val();
                
                if (!startDate || !endDate) {
                    alert('Please select start and end dates');
                    return;
                }
                
                // Set the values in the conflict check form
                $('#conflict_start_date').val(startDate);
                $('#conflict_end_date').val(endDate);
                $('#conflict_department').val(departmentId);
                
                // Open the conflict modal
                const conflictModal = new bootstrap.Modal(document.getElementById('leaveConflictModal'));
                conflictModal.show();
                
                // Hide results and reset form
                $('#conflictResults').hide();
                $('#conflictLoader').hide();
                $('#conflictWarning').hide();
                
                // Trigger the check button
                $('#checkConflictsBtn').click();
            });
        }
        
        function updateCoverageTrendChart(labels, data) {
            const ctx = document.getElementById('coverageTrendChart');
            
            // Destroy existing chart if it exists
            if (coverageTrendChart) {
                coverageTrendChart.destroy();
            }
            
            // Create background colors array based on coverage values
            const bgColors = data.map(value => {
                if (value < 70) return 'rgba(231, 74, 59, 0.8)';  // Red - Critical
                if (value < 85) return 'rgba(246, 194, 62, 0.8)';  // Yellow - Warning
                return 'rgba(28, 200, 138, 0.8)';  // Green - Good
            });
            
            // Create new chart
            coverageTrendChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Coverage %',
                        data: data,
                        backgroundColor: bgColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Coverage: ${context.raw}%`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            title: {
                                display: true,
                                text: 'Coverage %'
                            }
                        }
                    }
                }
            });
        }
        
        // Helper function to format dates
        function formatDate(dateStr) {
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return new Date(dateStr).toLocaleDateString(undefined, options);
        }
    });
</script>
@endsection