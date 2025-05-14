@extends('layouts.app')

@section('title', 'Real-time Attendance Dashboard')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0/dist/apexcharts.min.css">
<style>
    .dashboard-container {
        margin-bottom: 30px;
    }
    
    .stats-card {
        border-radius: 8px;
        height: 100%;
        transition: transform 0.2s;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
    }
    
    .status-counter {
        font-size: 1.75rem;
        font-weight: bold;
    }
    
    .status-icon {
        font-size: 2.5rem;
        margin-bottom: 10px;
    }
    
    .department-card {
        transition: all 0.3s;
    }
    
    .department-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .dept-low-coverage {
        border-left: 4px solid #dc3545;
    }
    
    .dept-medium-coverage {
        border-left: 4px solid #ffc107;
    }
    
    .dept-high-coverage {
        border-left: 4px solid #28a745;
    }
    
    .activity-feed {
        max-height: 600px;
        overflow-y: auto;
    }
    
    .activity-item {
        border-left: 3px solid #eee;
        padding: 10px 15px;
        margin-bottom: 10px;
        background-color: #fff;
        transition: all 0.2s;
    }
    
    .activity-item:hover {
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    }
    
    .activity-item.new-entry {
        border-left-color: #4e73df;
        animation: fadeIn 1s;
    }
    
    .activity-item.updated-entry {
        border-left-color: #1cc88a;
    }
    
    .activity-item.present-status {
        border-left-color: #28a745;
    }
    
    .activity-item.late-status {
        border-left-color: #ffc107;
    }
    
    .activity-item.absent-status {
        border-left-color: #dc3545;
    }
    
    .activity-item.leave-status {
        border-left-color: #6c757d;
    }
    
    .exception-badge {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        display: inline-block;
    }
    
    .exception-item {
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 4px;
        background-color: #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        transition: all 0.2s;
    }
    
    .exception-item:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .exception-item .badge {
        font-size: 85%;
        font-weight: 500;
    }
    
    .exception-item.late-exception {
        border-left: 3px solid #ffc107;
    }
    
    .exception-item.missing-exception {
        border-left: 3px solid #dc3545;
    }
    
    .exception-item.partial-exception {
        border-left: 3px solid #17a2b8;
    }
    
    .quick-action {
        display: block;
        padding: 15px;
        border-radius: 8px;
        background-color: #fff;
        text-align: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        transition: all 0.2s;
        margin-bottom: 15px;
        text-decoration: none !important;
        color: #5a5c69;
    }
    
    .quick-action:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        color: #4e73df;
    }
    
    .quick-action i {
        font-size: 2rem;
        margin-bottom: 10px;
        color: #4e73df;
    }
    
    .filter-bar {
        background-color: #f8f9fc;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    .department-overview {
        max-height: 600px;
        overflow-y: auto;
    }
    
    .chart-container {
        min-height: 300px;
        position: relative;
    }
    
    .loading-indicator {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10;
    }
    
    .system-info-bar {
        background-color: #f8f9fc;
        border-radius: 0.35rem;
        padding: 0.75rem 1rem;
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.85rem;
        color: #6c757d;
    }
    
    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(78, 115, 223, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(78, 115, 223, 0); }
        100% { box-shadow: 0 0 0 0 rgba(78, 115, 223, 0); }
    }
    
    .pulse {
        animation: pulse 2s infinite;
    }
    
    /* Responsive styles */
    @media (max-width: 768px) {
        .status-counter {
            font-size: 1.25rem;
        }
        
        .status-icon {
            font-size: 1.5rem;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Real-time Attendance Dashboard</h1>
        <div>
            <a href="{{ route('counter.dashboard') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-desktop fa-sm text-white-50"></i> Counter Terminal
            </a>
            <a href="{{ route('attendance.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-list fa-sm text-white-50"></i> All Records
            </a>
        </div>
    </div>
    
    <!-- System Info Bar -->
    <div class="system-info-bar">
        <div>
            <i class="far fa-calendar-alt"></i> Current Date/Time (UTC): <span id="current-datetime">{{ date('Y-m-d H:i:s') }}</span>
        </div>
        <div>
            <i class="fas fa-user"></i> Logged in as: {{ auth()->user()->employee_id ?? auth()->user()->username ?? auth()->user()->email }} ({{ auth()->user()->isAdmin() ? 'Administrator' : (auth()->user()->isHR() ? 'HR Manager' : 'Employee') }})
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filter-bar">
        <form id="filter-form" class="row g-3 align-items-center">
            <div class="col-md-4">
                <label for="date-filter" class="form-label">Date</label>
                <input type="date" class="form-control" id="date-filter" value="{{ $selectedDate }}">
            </div>
            <div class="col-md-4">
                <label for="department-filter" class="form-label">Department</label>
                <select class="form-select" id="department-filter">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ $selectedDepartment == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="button" id="apply-filters" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Main Content Row -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <!-- Summary Stats Cards -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow mb-0">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Attendance Overview</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="chart-container">
                                        <div id="attendance-summary-chart"></div>
                                        <div class="loading-indicator" id="summary-loading">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-gradient-primary text-white mb-3">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-uppercase mb-1">Present Rate</div>
                                                    <div class="h3 mb-0 font-weight-bold" id="present-rate-counter">0%</div>
                                                </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-percentage fa-2x text-gray-300"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Quick Actions -->
                                    <div class="row">
                                        <div class="col-6">
                                            <a href="{{ route('counter.search') }}" class="quick-action">
                                                <i class="fas fa-sign-in-alt"></i>
                                                <span>Clock In</span>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="{{ route('counter.search') }}" class="quick-action">
                                                <i class="fas fa-sign-out-alt"></i>
                                                <span>Clock Out</span>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="{{ route('attendance.create') }}" class="quick-action">
                                                <i class="fas fa-plus-circle"></i>
                                                <span>Add Record</span>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="{{ route('reports.attendance') }}" class="quick-action">
                                                <i class="fas fa-file-alt"></i>
                                                <span>Reports</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Department Breakdown -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Department Attendance</h6>
                    <span class="text-xs" id="dept-last-update"></span>
                </div>
                <div class="card-body">
                    <div class="department-overview" id="department-container">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading department data...</span>
                            </div>
                            <p class="mt-2">Loading department data...</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Attendance Trends -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Attendance Trends</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <div id="trend-chart" class="w-100"></div>
                        <div class="loading-indicator" id="trend-loading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Live Activity Feed -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Live Activity</h6>
                    <div>
                        <span class="badge bg-success" id="live-indicator">LIVE</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="activity-feed" id="activity-feed">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading activity...</span>
                            </div>
                            <p class="mt-2">Loading activity...</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Attendance Exceptions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Attendance Exceptions</h6>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="exceptions-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="late-tab" data-bs-toggle="tab" data-bs-target="#late" type="button" role="tab" aria-selected="true">
                                Late <span class="badge bg-warning text-white" id="late-count">0</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="missing-tab" data-bs-toggle="tab" data-bs-target="#missing" type="button" role="tab" aria-selected="false">
                                Missing <span class="badge bg-danger text-white" id="missing-count">0</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="partial-tab" data-bs-toggle="tab" data-bs-target="#partial" type="button" role="tab" aria-selected="false">
                                No Clock-out <span class="badge bg-info text-white" id="partial-count">0</span>
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="exceptions-tab-content">
                        <div class="tab-pane fade show active" id="late" role="tabpanel" aria-labelledby="late-tab">
                            <div id="late-exceptions-container">
                                <div class="text-center py-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="missing" role="tabpanel" aria-labelledby="missing-tab">
                            <div id="missing-exceptions-container">
                                <div class="text-center py-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="partial" role="tabpanel" aria-labelledby="partial-tab">
                            <div id="partial-exceptions-container">
                                <div class="text-center py-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
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
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0/dist/apexcharts.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let summaryChart;
        let trendChart;
        let lastTimestamp = '00:00:00';
        let refreshInterval;
        let summaryRefreshInterval;
        let exceptionRefreshInterval;
        
        // Initialize data
        loadSummaryData();
        loadDepartmentData();
        loadLiveUpdates();
        loadTrendData();
        loadExceptionData();
        
        // Start automatic refresh for live data
        startAutoRefresh();
        
        // Filter event listeners
        document.getElementById('apply-filters').addEventListener('click', function() {
            applyFilters();
        });
        
        // Update the current datetime every second with proper UTC format
        setInterval(function() {
            const now = new Date();
            
            // Format date as YYYY-MM-DD HH:MM:SS
            const year = now.getUTCFullYear();
            const month = String(now.getUTCMonth() + 1).padStart(2, '0');
            const day = String(now.getUTCDate()).padStart(2, '0');
            const hours = String(now.getUTCHours()).padStart(2, '0');
            const minutes = String(now.getUTCMinutes()).padStart(2, '0');
            const seconds = String(now.getUTCSeconds()).padStart(2, '0');
            
            const formattedDatetime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
            document.getElementById('current-datetime').textContent = formattedDatetime;
        }, 1000);
        
        function applyFilters() {
            // Reset timestamp for live updates when filters change
            lastTimestamp = '00:00:00';
            
            // Refresh all data
            loadSummaryData();
            loadDepartmentData();
            loadLiveUpdates();
            loadTrendData();
            loadExceptionData();
            
            // Update URL parameters for better sharing
            const dateFilter = document.getElementById('date-filter').value;
            const departmentFilter = document.getElementById('department-filter').value;
            
            const url = new URL(window.location);
            if (dateFilter) url.searchParams.set('date', dateFilter);
            else url.searchParams.delete('date');
            
            if (departmentFilter) url.searchParams.set('department_id', departmentFilter);
            else url.searchParams.delete('department_id');
            
            window.history.replaceState({}, '', url);
        }
        
        function startAutoRefresh() {
            // Refresh live updates every 30 seconds
            if (refreshInterval) clearInterval(refreshInterval);
            refreshInterval = setInterval(loadLiveUpdates, 30000);
            
            // Refresh summary data every 5 minutes
            if (summaryRefreshInterval) clearInterval(summaryRefreshInterval);
            summaryRefreshInterval = setInterval(loadSummaryData, 300000);
            
            // Refresh exception data every 3 minutes
            if (exceptionRefreshInterval) clearInterval(exceptionRefreshInterval);
            exceptionRefreshInterval = setInterval(loadExceptionData, 180000);
            
            // Department data is refreshed on demand only
            
            // Add pulse effect to live indicator
            document.getElementById('live-indicator').classList.add('pulse');
        }
        
        function getFilters() {
            return {
                date: document.getElementById('date-filter').value,
                department_id: document.getElementById('department-filter').value
            };
        }
        
        function loadSummaryData() {
            const filters = getFilters();
            document.getElementById('summary-loading').style.display = 'flex';
            
            fetch(`/attendance-dashboard/summary?date=${filters.date}&department_id=${filters.department_id}`)
                .then(response => response.json())
                .then(data => {
                    // Update present rate counter
                    document.getElementById('present-rate-counter').textContent = data.present_percentage + '%';
                    
                    // Prepare chart data
                    const statuses = data.status_summary.map(status => status.name);
                    const counts = data.status_summary.map(status => status.count);
                    const colors = data.status_summary.map(status => status.color);
                    
                    // Initialize or update chart
                    if (summaryChart) {
                        summaryChart.updateOptions({
                            labels: statuses,
                            colors: colors
                        });
                        summaryChart.updateSeries(counts);
                    } else {
                        initializeSummaryChart(statuses, counts, colors);
                    }
                    
                    document.getElementById('summary-loading').style.display = 'none';
                })
                .catch(error => {
                    console.error("Error loading summary data:", error);
                    document.getElementById('summary-loading').style.display = 'none';
                });
        }
        
        function initializeSummaryChart(statuses, counts, colors) {
            const options = {
                series: counts,
                chart: {
                    type: 'donut',
                    height: 350
                },
                labels: statuses,
                colors: colors,
                legend: {
                    position: 'bottom'
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            height: 300
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '50%',
                            labels: {
                                show: true,
                                name: {
                                    show: true
                                },
                                value: {
                                    show: true,
                                    formatter: function(val) {
                                        return val;
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: function(w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    }
                                }
                            }
                        }
                    }
                }
            };
            
            summaryChart = new ApexCharts(document.getElementById('attendance-summary-chart'), options);
            summaryChart.render();
        }
        
        function loadDepartmentData() {
            const filters = getFilters();
            const departmentContainer = document.getElementById('department-container');
            
            departmentContainer.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading department data...</span>
                    </div>
                    <p class="mt-2">Loading department data...</p>
                </div>
            `;
            
            fetch(`/attendance-dashboard/departments?date=${filters.date}&department_id=${filters.department_id}`)
                .then(response => response.json())
                .then(data => {
                    const departments = data.departments;
                    
                    if (departments.length === 0) {
                        departmentContainer.innerHTML = `
                            <div class="alert alert-info">
                                No department data available for the selected filters.
                            </div>
                        `;
                        return;
                    }
                    
                    let departmentHtml = '';
                    
                    departments.forEach(dept => {
                        // Determine coverage class
                        let coverageClass = 'dept-high-coverage';
                        if (dept.coverage < 70) {
                            coverageClass = 'dept-low-coverage';
                        } else if (dept.coverage < 90) {
                            coverageClass = 'dept-medium-coverage';
                        }
                        
                        departmentHtml += `
                            <div class="card mb-3 department-card ${coverageClass}">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <h6 class="mb-1">${dept.name}</h6>
                                            <div class="small text-muted">Total: ${dept.total} employees</div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="progress mb-2" style="height: 10px;">
                                                <div class="progress-bar bg-success" role="progressbar" 
                                                    style="width: ${(dept.present / dept.total) * 100}%;" 
                                                    aria-valuenow="${dept.present}" aria-valuemin="0" aria-valuemax="${dept.total}">
                                                </div>
                                                <div class="progress-bar bg-warning" role="progressbar" 
                                                    style="width: ${(dept.late / dept.total) * 100}%;" 
                                                    aria-valuenow="${dept.late}" aria-valuemin="0" aria-valuemax="${dept.total}">
                                                </div>
                                                <div class="progress-bar bg-danger" role="progressbar" 
                                                    style="width: ${(dept.absent / dept.total) * 100}%;" 
                                                    aria-valuenow="${dept.absent}" aria-valuemin="0" aria-valuemax="${dept.total}">
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <small>Present: ${dept.present}</small>
                                                <small>Late: ${dept.late}</small>
                                                <small>Absent: ${dept.absent}</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2 text-center">
                                            <div class="h4 mb-0">${dept.coverage}%</div>
                                            <div class="small text-muted">Coverage</div>
                                        </div>
                                        <div class="col-md-2 text-end">
                                            <a href="/attendance?department_id=${dept.id}&date=${filters.date}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    departmentContainer.innerHTML = departmentHtml;
                    document.getElementById('dept-last-update').textContent = `Last updated: ${new Date().toLocaleTimeString()}`;
                })
                .catch(error => {
                    console.error("Error loading department data:", error);
                    departmentContainer.innerHTML = `
                        <div class="alert alert-danger">
                            Error loading department data. Please try refreshing the page.
                        </div>
                    `;
                });
        }
        
        function loadLiveUpdates() {
            const filters = getFilters();
            
            fetch(`/attendance-dashboard/live-updates?date=${filters.date}&department_id=${filters.department_id}&last_timestamp=${lastTimestamp}`)
                .then(response => response.json())
                .then(data => {
                    // Update last timestamp for next fetch
                    lastTimestamp = data.current_timestamp;
                    
                    const activityFeed = document.getElementById('activity-feed');
                    const records = data.records;
                    
                    if (records.length > 0) {
                        // Remove loading indicator if present
                        const loadingIndicator = activityFeed.querySelector('.text-center.py-5');
                        if (loadingIndicator) {
                            activityFeed.removeChild(loadingIndicator);
                        }
                        
                        // Prevent excessive DOM updates if too many records
                        const fragment = document.createDocumentFragment();
                        
                        // Add new records at the top
                        records.forEach(record => {
                            const activityItem = document.createElement('div');
                            activityItem.className = `activity-item ${record.is_new ? 'new-entry' : ''} ${record.is_updated ? 'updated-entry' : ''} ${record.status.toLowerCase()}-status`;
                            
                            activityItem.innerHTML = `
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>${record.employee_name}</strong>
                                        <span class="badge bg-${getStatusBadgeClass(record.status)}">${record.status}</span>
                                    </div>
                                    <small class="text-muted">${formatTime(record.clock_in)}</small>
                                </div>
                                <div class="text-muted small">${record.department} · ${record.employee_code}</div>
                                <div class="mt-2">
                                    ${record.clock_in ? `<i class="fas fa-sign-in-alt text-success"></i> ${formatTime(record.clock_in)}` : ''}
                                    ${record.clock_in && record.clock_out ? ' · ' : ''}
                                    ${record.clock_out ? `<i class="fas fa-sign-out-alt text-danger"></i> ${formatTime(record.clock_out)}` : ''}
                                </div>
                            `;
                            
                            fragment.prepend(activityItem);
                        });
                        
                        // Insert at the top of the feed
                        if (activityFeed.firstChild) {
                            activityFeed.insertBefore(fragment, activityFeed.firstChild);
                        } else {
                            activityFeed.appendChild(fragment);
                        }
                        
                        // Limit the number of items to prevent performance issues
                        const items = activityFeed.querySelectorAll('.activity-item');
                        const maxItems = 50;
                        
                        if (items.length > maxItems) {
                            for (let i = maxItems; i < items.length; i++) {
                                activityFeed.removeChild(items[i]);
                            }
                        }
                    } else if (activityFeed.childElementCount === 0) {
                        activityFeed.innerHTML = `
                            <div class="text-center py-5">
                                <i class="fas fa-info-circle fa-2x text-info mb-3"></i>
                                <p>No attendance records found for the selected date.</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error("Error loading live updates:", error);
                    document.getElementById('live-indicator').classList.remove('pulse');
                    document.getElementById('live-indicator').classList.add('bg-danger');
                    document.getElementById('live-indicator').textContent = 'ERROR';
                });
        }
        
        function loadTrendData() {
            const filters = getFilters();
            document.getElementById('trend-loading').style.display = 'flex';
            
            fetch(`/attendance-dashboard/trend?department_id=${filters.department_id}`)
                .then(response => response.json())
                .then(data => {
                    // Initialize or update trend chart
                    if (trendChart) {
                        trendChart.updateOptions({
                            xaxis: {
                                categories: data.dates
                            }
                        });
                        trendChart.updateSeries([
                            {
                                name: 'Present Percentage',
                                data: data.present_percentage
                            },
                            {
                                name: 'Late Percentage',
                                data: data.late_percentage
                            }
                        ]);
                    } else {
                        initializeTrendChart(data.dates, data.present_percentage, data.late_percentage);
                    }
                    
                    document.getElementById('trend-loading').style.display = 'none';
                })
                .catch(error => {
                    console.error("Error loading trend data:", error);
                    document.getElementById('trend-loading').style.display = 'none';
                });
        }
        
        function initializeTrendChart(dates, presentData, lateData) {
            const options = {
                series: [
                    {
                        name: 'Present Percentage',
                        data: presentData
                    },
                    {
                        name: 'Late Percentage',
                        data: lateData
                    }
                ],
                chart: {
                    height: 350,
                    type: 'line',
                    zoom: {
                        enabled: false
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: [3, 3],
                    dashArray: [0, 5]
                },
                colors: ['#28a745', '#ffc107'],
                xaxis: {
                    categories: dates
                },
                yaxis: {
                    title: {
                        text: 'Percentage (%)'
                    },
                    min: 0,
                    max: 100
                },
                legend: {
                    position: 'top'
                },
                markers: {
                    size: 5
                },
                grid: {
                    borderColor: '#e7e7e7',
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    }
                }
            };
            
            trendChart = new ApexCharts(document.getElementById('trend-chart'), options);
            trendChart.render();
        }
        
        function loadExceptionData() {
            const filters = getFilters();
            
            fetch(`/attendance-dashboard/exceptions?date=${filters.date}&department_id=${filters.department_id}`)
                .then(response => response.json())
                .then(data => {
                    // Update exception counts
                    document.getElementById('late-count').textContent = data.late_employees.length;
                    document.getElementById('missing-count').textContent = data.missing_attendance.length;
                    document.getElementById('partial-count').textContent = data.partial_day_employees.length;
                    
                    // Update exception containers
                    updateExceptionContainer('late-exceptions-container', data.late_employees, 'late-exception', renderLateException);
                    updateExceptionContainer('missing-exceptions-container', data.missing_attendance, 'missing-exception', renderMissingException);
                    updateExceptionContainer('partial-exceptions-container', data.partial_day_employees, 'partial-exception', renderPartialException);
                })
                .catch(error => {
                    console.error("Error loading exception data:", error);
                    
                    // Show error in all containers
                    ['late-exceptions-container', 'missing-exceptions-container', 'partial-exceptions-container'].forEach(containerId => {
                        document.getElementById(containerId).innerHTML = `
                            <div class="alert alert-danger">
                                Error loading exception data. Please try refreshing the page.
                            </div>
                        `;
                    });
                });
        }
        
        function updateExceptionContainer(containerId, data, itemClass, renderFunction) {
            const container = document.getElementById(containerId);
            
            if (data.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                        <p>No exceptions found.</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            data.forEach(item => {
                html += renderFunction(item, itemClass);
            });
            
            container.innerHTML = html;
        }
        
        function renderLateException(employee, itemClass) {
            return `
                <div class="exception-item ${itemClass}">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>${employee.employee_name}</strong>
                            <span class="badge bg-warning">Late</span>
                        </div>
                        <div>
                            <span class="badge bg-danger">${employee.late_by_minutes} min late</span>
                        </div>
                    </div>
                    <div class="text-muted small mb-2">${employee.department}</div>
                    <div>
                        <i class="fas fa-clock text-muted"></i> Clock In: ${formatTime(employee.clock_in_time)} 
                        (Should be: ${employee.shift_start})
                    </div>
                    <div class="mt-2 text-end">
                        <a href="/attendance?employee_id=${employee.employee_id}&date=${getFilters().date}" class="btn btn-sm btn-outline-primary">View</a>
                    </div>
                </div>
            `;
        }
        
        function renderMissingException(employee, itemClass) {
            return `
                <div class="exception-item ${itemClass}">
                    <div>
                        <strong>${employee.employee_name}</strong>
                        <span class="badge bg-danger">No Record</span>
                    </div>
                    <div class="text-muted small mb-2">${employee.department} · ${employee.employee_code}</div>
                    <div class="mt-2 text-end">
                        <a href="/attendance/create?employee_id=${employee.employee_id}&date=${getFilters().date}" class="btn btn-sm btn-outline-primary">Add Record</a>
                    </div>
                </div>
            `;
        }
        
        function renderPartialException(employee, itemClass) {
            return `
                <div class="exception-item ${itemClass}">
                    <div>
                        <strong>${employee.employee_name}</strong>
                        <span class="badge bg-info">No Clock-Out</span>
                    </div>
                    <div class="text-muted small mb-2">${employee.department}</div>
                    <div>
                        <i class="fas fa-sign-in-alt text-success"></i> ${formatTime(employee.clock_in_time)} 
                        <span class="badge bg-warning">${employee.hours_since_in}+ hours ago</span>
                    </div>
                    <div class="mt-2 text-end">
                        <a href="/attendance/${employee.id}/edit" class="btn btn-sm btn-outline-primary">Update</a>
                    </div>
                </div>
            `;
        }
        
        function getStatusBadgeClass(status) {
            switch(status.toLowerCase()) {
                case 'present': return 'success';
                case 'late': return 'warning';
                case 'absent': return 'danger';
                case 'on leave': return 'secondary';
                default: return 'info';
            }
        }
        
        function formatTime(timeStr) {
            if (!timeStr) return '';
            return timeStr;
        }
        
        // Returns the current date in YYYY-MM-DD format
        function getCurrentDate() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            
            return `${year}-${month}-${day}`;
        }
        
        // Sets today's date as the default if no date is selected
        if (!document.getElementById('date-filter').value) {
            document.getElementById('date-filter').value = getCurrentDate();
        }
        
        // Handle key events for quicker filtering
        document.getElementById('date-filter').addEventListener('keyup', function(event) {
            if (event.key === 'Enter') {
                applyFilters();
            }
        });
        
        document.getElementById('department-filter').addEventListener('change', function() {
            applyFilters();
        });
        
        // Show a notification when new data arrives
        function showNotification(message) {
            // Check if browser notifications are supported and permitted
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('Attendance Dashboard', { 
                    body: message,
                    icon: '/favicon.ico'
                });
            }
        }
        
        // Request notification permissions on page load
        if ('Notification' in window && Notification.permission !== 'denied') {
            Notification.requestPermission();
        }
        
        // Add a manual refresh button
        document.querySelector('.card-header .badge').insertAdjacentHTML('beforebegin', `
            <button id="manual-refresh" class="btn btn-sm btn-outline-primary me-2">
                <i class="fas fa-sync"></i> Refresh
            </button>
        `);
        
        document.getElementById('manual-refresh').addEventListener('click', function() {
            loadLiveUpdates();
            loadExceptionData();
        });
        
        // Cleanup on page unload to prevent memory leaks
        window.addEventListener('beforeunload', function() {
            if (refreshInterval) clearInterval(refreshInterval);
            if (summaryRefreshInterval) clearInterval(summaryRefreshInterval);
            if (exceptionRefreshInterval) clearInterval(exceptionRefreshInterval);
        });
    });
</script>
@endsection