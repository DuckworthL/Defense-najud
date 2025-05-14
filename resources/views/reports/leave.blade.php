@extends('layouts.app')

@section('title', 'Leave Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $title }}</h5>
                    <div class="btn-group">
                        <a href="{{ request()->fullUrlWithQuery(['export_format' => 'pdf']) }}" class="btn btn-sm btn-danger">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['export_format' => 'excel']) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-file-excel"></i> Excel
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['export_format' => 'csv']) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-file-csv"></i> CSV
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6>Report Parameters</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Start Date:</strong> {{ $start_date }}
                            </div>
                            <div class="col-md-3">
                                <strong>End Date:</strong> {{ $end_date }}
                            </div>
                            <div class="col-md-3">
                                <strong>Department:</strong> {{ request('department_id') ? \App\Models\Department::find(request('department_id'))->name : 'All Departments' }}
                            </div>
                            <div class="col-md-3">
                                <strong>Status:</strong> {{ request('status') ? ucfirst(request('status')) : 'All Statuses' }}
                            </div>
                        </div>
                    </div>
                    
                    @if($leaves->count() > 0)
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title">Leave Summary</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <tbody>
                                                    <tr>
                                                        <td><strong>Total Leave Requests:</strong></td>
                                                        <td>{{ $leaves->count() }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Approved Leaves:</strong></td>
                                                        <td>{{ $leaves->where('status', 'approved')->count() }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Pending Leaves:</strong></td>
                                                        <td>{{ $leaves->where('status', 'pending')->count() }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Rejected Leaves:</strong></td>
                                                        <td>{{ $leaves->where('status', 'rejected')->count() }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Total Days:</strong></td>
                                                        <td>{{ $leaves->sum(function($leave) { return $leave->start_date->diffInDays($leave->end_date) + 1; }) }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <canvas id="leaveStatusChart" height="200"></canvas>
                            </div>
                        </div>
                        
                        <h5 class="mb-3">Leave Details</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Department</th>
                                        <th>Leave Period</th>
                                        <th>Days</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Approved By</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($leaves as $leave)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2">
                                                        @if($leave->employee->profile_picture)
                                                        <img src="{{ asset('storage/' . $leave->employee->profile_picture) }}" class="rounded-circle" width="40" height="40" alt="{{ $leave->employee->full_name }}">
                                                        @else
                                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                            {{ strtoupper(substr($leave->employee->first_name, 0, 1) . substr($leave->employee->last_name, 0, 1)) }}
                                                        </div>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        {{ $leave->employee->full_name }}<br>
                                                        <small class="text-muted">{{ $leave->employee->employee_id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $leave->employee->department->name }}</td>
                                            <td>{{ $leave->start_date->format('M d, Y') }} - {{ $leave->end_date->format('M d, Y') }}</td>
                                            <td class="text-center">{{ $leave->start_date->diffInDays($leave->end_date) + 1 }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit($leave->reason, 30) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $leave->status == 'approved' ? 'success' : ($leave->status == 'pending' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($leave->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $leave->approver->full_name ?? 'N/A' }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit($leave->remarks, 30) ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No leave requests found in the selected date range.
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if($leaves->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Leave Status Chart
        const leaveStatusCtx = document.getElementById('leaveStatusChart').getContext('2d');
        const leaveStatusChart = new Chart(leaveStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'Pending', 'Rejected'],
                datasets: [{
                    data: [
                        {{ $leaves->where('status', 'approved')->count() }},
                        {{ $leaves->where('status', 'pending')->count() }},
                        {{ $leaves->where('status', 'rejected')->count() }}
                    ],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(220, 53, 69, 0.7)'
                    ],
                    borderColor: [
                        'rgb(40, 167, 69)',
                        'rgb(255, 193, 7)',
                        'rgb(220, 53, 69)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    title: {
                        display: true,
                        text: 'Leave Status Distribution'
                    }
                }
            }
        });
    });
</script>
@endif
@endsection