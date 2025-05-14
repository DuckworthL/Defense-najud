@extends('layouts.app')

@section('title', 'My Attendance')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Attendance Records</h5>
                    <div>
                        <form action="{{ route('my-attendance') }}" method="GET" class="form-inline">
                            <div class="input-group">
                                <input type="month" name="month" id="month" class="form-control" value="{{ request('month', date('Y-m')) }}">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Status</th>
                                    <th>Clock In</th>
                                    <th>Clock Out</th>
                                    <th>Working Hours</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $attendance)
                                <tr>
                                    <td>{{ $attendance->date->format('M d, Y') }}</td>
                                    <td>{{ $attendance->date->format('l') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $attendance->attendanceStatus->name == 'Present' ? 'success' : ($attendance->attendanceStatus->name == 'Late' ? 'warning' : ($attendance->attendanceStatus->name == 'Absent' ? 'danger' : 'secondary')) }}">
                                            {{ $attendance->attendanceStatus->name }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($attendance->clock_in_time)
                                            {{ $attendance->clock_in_time->format('h:i A') }}
                                            @if($attendance->is_clock_in_reset)
                                                <span class="badge bg-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset by {{ $attendance->clockInResetBy->full_name ?? 'Admin' }}">Modified</span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($attendance->clock_out_time)
                                            {{ $attendance->clock_out_time->format('h:i A') }}
                                            @if($attendance->is_clock_out_reset)
                                                <span class="badge bg-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset by {{ $attendance->clockOutResetBy->full_name ?? 'Admin' }}">Modified</span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($attendance->clock_in_time && $attendance->clock_out_time)
                                            {{ $attendance->work_hours }} hrs
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $attendance->remarks ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No attendance records found for the selected month.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Attendance Summary Card -->
    @if(count($attendances) > 0)
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Attendance Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title">Present Days</h6>
                                            <h3 class="mb-0">{{ $attendances->where('attendance_status.name', 'Present')->count() }}</h3>
                                        </div>
                                        <i class="fas fa-user-check fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title">Late Days</h6>
                                            <h3 class="mb-0">{{ $attendances->where('attendance_status.name', 'Late')->count() }}</h3>
                                        </div>
                                        <i class="fas fa-clock fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title">Absent Days</h6>
                                            <h3 class="mb-0">{{ $attendances->where('attendance_status.name', 'Absent')->count() }}</h3>
                                        </div>
                                        <i class="fas fa-user-times fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-secondary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title">Leave Days</h6>
                                            <h3 class="mb-0">{{ $attendances->where('attendance_status.name', 'On Leave')->count() }}</h3>
                                        </div>
                                        <i class="fas fa-calendar-alt fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Working Hours</h5>
                </div>
                <div class="card-body">
                    <canvas id="workingHoursChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
@if(count($attendances) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Working Hours Chart
        const workingHoursCtx = document.getElementById('workingHoursChart').getContext('2d');
        const workingHoursChart = new Chart(workingHoursCtx, {
            type: 'line',
            data: {
                labels: [
                    @foreach($attendances->sortBy('date') as $attendance)
                        '{{ $attendance->date->format('M d') }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'Working Hours',
                    data: [
                        @foreach($attendances->sortBy('date') as $attendance)
                            {{ $attendance->work_hours ?? 0 }},
                        @endforeach
                    ],
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 2
                        },
                        title: {
                            display: true,
                            text: 'Hours'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    });
</script>
@endif
@endsection