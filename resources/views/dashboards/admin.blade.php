@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Quick Action Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100 quick-action-card">
                <div class="card-body">
                    <div class="d-flex flex-column justify-content-between h-100">
                        <div>
                            <h5 class="card-title">Present</h5>
                            <p class="mb-0">View present employees</p>
                        </div>
                        <div class="text-end mt-3">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="{{ route('attendance.index', ['attendance_status_id' => 1]) }}" class="btn btn-light btn-sm w-100">
                        <i class="fas fa-eye me-1"></i> View Details
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white h-100 quick-action-card">
                <div class="card-body">
                    <div class="d-flex flex-column justify-content-between h-100">
                        <div>
                            <h5 class="card-title">Late</h5>
                            <p class="mb-0">Manage late arrivals</p>
                        </div>
                        <div class="text-end mt-3">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="{{ route('attendance.index', ['attendance_status_id' => 2]) }}" class="btn btn-light btn-sm w-100">
                        <i class="fas fa-eye me-1"></i> View Details
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-danger text-white h-100 quick-action-card">
                <div class="card-body">
                    <div class="d-flex flex-column justify-content-between h-100">
                        <div>
                            <h5 class="card-title">Absent</h5>
                            <p class="mb-0">Track absences</p>
                        </div>
                        <div class="text-end mt-3">
                            <i class="fas fa-user-times"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="{{ route('attendance.index', ['attendance_status_id' => 3]) }}" class="btn btn-light btn-sm w-100">
                        <i class="fas fa-eye me-1"></i> View Details
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-secondary text-white h-100 quick-action-card">
                <div class="card-body">
                    <div class="d-flex flex-column justify-content-between h-100">
                        <div>
                            <h5 class="card-title">On Leave</h5>
                            <p class="mb-0">View leave records</p>
                        </div>
                        <div class="text-end mt-3">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="{{ route('leaves.index', ['status' => 'approved']) }}" class="btn btn-light btn-sm w-100">
                        <i class="fas fa-eye me-1"></i> View Details
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Realtime Dashboard Charts -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Department Attendance Overview</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary refresh-btn" id="refresh-dept">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div id="department-container" class="position-relative" style="min-height: 300px;">
                        <canvas id="departmentChart" height="300"></canvas>
                        <div class="position-absolute top-0 start-0 end-0 bottom-0 d-flex justify-content-center align-items-center bg-white bg-opacity-75 d-none" id="dept-loading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Attendance Distribution</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary refresh-btn" id="refresh-dist">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div class="position-relative" style="min-height: 300px;">
                        <canvas id="attendancePieChart" height="300"></canvas>
                        <div class="position-absolute top-0 start-0 end-0 bottom-0 d-flex justify-content-center align-items-center bg-white bg-opacity-75 d-none" id="pie-loading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Attendance Trend Chart -->
        <div class="col-12">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Weekly Attendance Trends</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary refresh-btn" id="refresh-trend">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div class="position-relative" style="min-height: 300px;">
                        <canvas id="weeklyTrendChart" height="250"></canvas>
                        <div class="position-absolute top-0 start-0 end-0 bottom-0 d-flex justify-content-center align-items-center bg-white bg-opacity-75 d-none" id="trend-loading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Activities -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Attendance Activities</h5>
                    <div>
                        <span class="badge bg-success pulse" id="live-indicator">LIVE</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="activity-feed" id="activity-feed">
                        @forelse($recentAttendance as $attendance)
                        <div class="activity-item {{ $attendance->attendanceStatus->name == 'Present' ? 'present-status' : ($attendance->attendanceStatus->name == 'Late' ? 'late-status' : ($attendance->attendanceStatus->name == 'Absent' ? 'absent-status' : 'leave-status')) }}">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $attendance->employee->full_name }}</strong>
                                    <span class="badge bg-{{ $attendance->attendanceStatus->name == 'Present' ? 'success' : ($attendance->attendanceStatus->name == 'Late' ? 'warning' : ($attendance->attendanceStatus->name == 'Absent' ? 'danger' : 'secondary')) }}">
                                        {{ $attendance->attendanceStatus->name }}
                                    </span>
                                </div>
                                <small class="text-muted">{{ $attendance->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="text-muted small">{{ $attendance->employee->department->name ?? 'No Department' }}</div>
                            <div class="mt-2">
                                @if($attendance->clock_in_time)
                                <i class="fas fa-sign-in-alt text-success"></i> {{ $attendance->clock_in_time->format('h:i A') }}
                                @endif
                                @if($attendance->clock_out_time)
                                <i class="fas fa-sign-out-alt text-danger ms-3"></i> {{ $attendance->clock_out_time->format('h:i A') }}
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <i class="fas fa-info-circle fa-2x text-info mb-3"></i>
                            <p>No recent activity found.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('attendance.index') }}" class="btn btn-primary">
                        <i class="fas fa-list me-1"></i> View All Records
                    </a>
                </div>
            </div>
        </div>

        <!-- Attendance Exceptions -->
<div class="col-lg-4 mb-4">
    <div class="card h-100">
        <div class="card-header">
            <h5 class="mb-0">Attendance Exceptions</h5>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs mb-3" id="exceptions-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="late-tab" data-bs-toggle="tab" data-bs-target="#late" type="button" role="tab" aria-selected="true">
                        Late <span class="badge bg-warning text-white" id="late-count">{{ isset($lateEmployees) ? $lateEmployees->count() : 0 }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="missing-tab" data-bs-toggle="tab" data-bs-target="#missing" type="button" role="tab" aria-selected="false">
                        Missing <span class="badge bg-danger text-white" id="missing-count">{{ isset($missingEmployees) ? $missingEmployees->count() : 0 }}</span>
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="exceptions-tab-content">
            <div class="tab-pane fade show active" id="late" role="tabpanel" aria-labelledby="late-tab">
    <div id="late-exceptions-container">
        @if(isset($lateEmployees) && $lateEmployees->count() > 0)
            @foreach($lateEmployees as $employee)
            <div class="exception-item late-exception">
                <div class="d-flex justify-content-between">
                    <div>
                        <strong>{{ $employee->first_name }} {{ $employee->last_name }}</strong>
                        <span class="badge bg-warning">Late</span>
                    </div>
                    <div>
                        <span class="badge bg-danger">{{ $employee->late_by_minutes > 0 ? $employee->late_by_minutes : 0 }} min late</span>
                    </div>
                </div>
                <div class="text-muted small mb-2">{{ $employee->department_name ?? 'No Department' }}</div>
            </div>
            @endforeach
        @else
            <div class="text-center py-4">
                <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                <p>No late arrivals today.</p>
            </div>
        @endif
    </div>
</div>
<div class="tab-pane fade" id="missing" role="tabpanel" aria-labelledby="missing-tab">
    <div id="missing-exceptions-container">
        @if(isset($missingEmployees) && $missingEmployees->count() > 0)
            @foreach($missingEmployees as $employee)
            <div class="exception-item missing-exception">
                <div>
                    <strong>{{ $employee->first_name }} {{ $employee->last_name }}</strong>
                    <span class="badge bg-danger">No Record</span>
                </div>
                <div class="text-muted small mb-2">{{ optional($employee->department)->name ?? 'No Department' }}</div>
            </div>
            @endforeach
        @else
            <div class="text-center py-4">
                <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                <p>All employees have checked in today.</p>
            </div>
        @endif
    </div>
</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    /* Activity Feed Styles */
    .activity-feed {
        max-height: 500px;
        overflow-y: auto;
    }
    
    .activity-item {
        border-left: 3px solid #eee;
        padding: 10px 15px;
        margin-bottom: 10px;
        background-color: #fff;
        transition: all 0.2s;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    .activity-item:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transform: translateY(-3px);
    }
    
    .activity-item.present-status {
        border-left-color: #4cc9f0;
    }
    
    .activity-item.late-status {
        border-left-color: #f72585;
    }
    
    .activity-item.absent-status {
        border-left-color: #e63946;
    }
    
    .activity-item.leave-status {
        border-left-color: #6c757d;
    }
    
    /* Exception Item Styles */
    .exception-item {
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 5px;
        background-color: #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        transition: all 0.2s;
    }
    
    .exception-item:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .exception-item.late-exception {
        border-left: 3px solid #f72585;
    }
    
    .exception-item.missing-exception {
        border-left: 3px solid #e63946;
    }
    
    /* Live Indicator */
    .pulse {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
    }
    
    /* Refresh Button */
    .refresh-btn {
        transition: all 0.3s ease;
    }
    
    .refresh-btn.loading {
        pointer-events: none;
        opacity: 0.7;
    }
    
    .refresh-btn.loading i {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let departmentChart;
        let weeklyTrendChart;
        let attendancePieChart;
        
        // Initialize the charts
        initCharts();
        
        // Set up refresh buttons
        document.getElementById('refresh-dept').addEventListener('click', function() {
            refreshDepartmentChart(this);
        });
        
        document.getElementById('refresh-trend').addEventListener('click', function() {
            refreshTrendChart(this);
        });
        
        document.getElementById('refresh-dist').addEventListener('click', function() {
            refreshPieChart(this);
        });
        
        // Function to initialize all charts
        function initCharts() {
            initDepartmentChart();
            initTrendChart();
            initPieChart();
        }
        
        // Department-wise attendance chart
        function initDepartmentChart() {
            const departmentCtx = document.getElementById('departmentChart').getContext('2d');
            departmentChart = new Chart(departmentCtx, {
                type: 'bar',
                data: {
                    labels: [
                        @foreach($departmentAttendance as $dept)
                        '{{ $dept->name }}',
                        @endforeach
                    ],
                    datasets: [{
                        label: 'Present Employees',
                        data: [
                            @foreach($departmentAttendance as $dept)
                            {{ $dept->count }},
                            @endforeach
                        ],
                        backgroundColor: 'rgba(76, 201, 240, 0.7)',
                        borderColor: 'rgb(76, 201, 240)',
                        borderWidth: 1
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    maintainAspectRatio: false
                }
            });
        }
        
        // Weekly trend chart
        function initTrendChart() {
            const weeklyTrendCtx = document.getElementById('weeklyTrendChart').getContext('2d');
            weeklyTrendChart = new Chart(weeklyTrendCtx, {
                type: 'line',
                data: {
                    labels: [
                        @foreach($weeklyTrend as $day)
                        '{{ $day['day'] }}',
                        @endforeach
                    ],
                    datasets: [
                        {
                            label: 'Present',
                            data: [
                                @foreach($weeklyTrend as $day)
                                {{ $day['present'] }},
                                @endforeach
                            ],
                            borderColor: '#4cc9f0',
                            backgroundColor: 'rgba(76, 201, 240, 0.1)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Late',
                            data: [
                                @foreach($weeklyTrend as $day)
                                {{ $day['late'] }},
                                @endforeach
                            ],
                            borderColor: '#f72585',
                            backgroundColor: 'rgba(247, 37, 133, 0.1)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    maintainAspectRatio: false
                }
            });
        }
        
        // Attendance distribution pie chart
        function initPieChart() {
            const pieCtx = document.getElementById('attendancePieChart').getContext('2d');
            attendancePieChart = new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Present', 'Late', 'Absent', 'On Leave'],
                    datasets: [{
                        data: [{{ $presentCount }}, {{ $lateCount }}, {{ $absentCount }}, {{ $onLeaveCount }}],
                        backgroundColor: ['#4cc9f0', '#f72585', '#e63946', '#6c757d'],
                        borderWidth: 1
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    maintainAspectRatio: false,
                    cutout: '60%'
                }
            });
        }
        
        // Function to refresh department chart
        function refreshDepartmentChart(button) {
            showLoading(button, 'dept-loading');
            
            // Simulate data refresh (in a real app, fetch from server)
            setTimeout(() => {
                hideLoading(button, 'dept-loading');
                // In a real app, you would update the chart with new data
            }, 1500);
        }
        
        // Function to refresh trend chart
        function refreshTrendChart(button) {
            showLoading(button, 'trend-loading');
            
            // Simulate data refresh
            setTimeout(() => {
                hideLoading(button, 'trend-loading');
                // In a real app, you would update the chart with new data
            }, 1500);
        }
        
        // Function to refresh pie chart
        function refreshPieChart(button) {
            showLoading(button, 'pie-loading');
            
            // Simulate data refresh
            setTimeout(() => {
                hideLoading(button, 'pie-loading');
                // In a real app, you would update the chart with new data
            }, 1500);
        }
        
        // Helper function to show loading indicators
        function showLoading(button, loaderId) {
            button.classList.add('loading');
            button.querySelector('i').classList.remove('fa-sync-alt');
            button.querySelector('i').classList.add('fa-spinner');
            document.getElementById(loaderId).classList.remove('d-none');
        }
        
        // Helper function to hide loading indicators
        function hideLoading(button, loaderId) {
            button.classList.remove('loading');
            button.querySelector('i').classList.remove('fa-spinner');
            button.querySelector('i').classList.add('fa-sync-alt');
            document.getElementById(loaderId).classList.add('d-none');
        }
        
        // Simulate live data updates
        setInterval(() => {
            const indicator = document.getElementById('live-indicator');
            if (indicator) {
                indicator.classList.toggle('pulse');
            }
        }, 3000);
    });
</script>
@endsection