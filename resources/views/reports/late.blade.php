@extends('layouts.app')

@section('title', 'Late Arrival Report')

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
                                <strong>Generated On:</strong> {{ date('Y-m-d H:i:s') }}
                            </div>
                        </div>
                    </div>
                    
                    @if(count($employeeSummary) > 0)
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title">Late Arrival Summary</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <tbody>
                                                    <tr>
                                                        <td><strong>Total Late Instances:</strong></td>
                                                        <td>{{ array_sum(array_map(function($employee) { return $employee['count']; }, $employeeSummary)) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Total Employees:</strong></td>
                                                        <td>{{ count($employeeSummary) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Period:</strong></td>
                                                        <td>{{ \Carbon\Carbon::parse($start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($end_date)->format('M d, Y') }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h5 class="mb-3">Late Arrivals by Employee</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Department</th>
                                        <th>Shift Schedule</th>
                                        <th>Number of Late Days</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employeeSummary as $employeeId => $data)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2">
                                                        @if($data['employee']->profile_picture)
                                                        <img src="{{ asset('storage/' . $data['employee']->profile_picture) }}" class="rounded-circle" width="40" height="40" alt="{{ $data['employee']->full_name }}">
                                                        @else
                                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                            {{ strtoupper(substr($data['employee']->first_name, 0, 1) . substr($data['employee']->last_name, 0, 1)) }}
                                                        </div>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        {{ $data['employee']->full_name }}<br>
                                                        <small class="text-muted">{{ $data['employee']->employee_id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $data['employee']->department->name }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($data['employee']->shift->start_time)->format('h:i A') }} - 
                                                {{ \Carbon\Carbon::parse($data['employee']->shift->end_time)->format('h:i A') }}
                                                <div class="small text-muted">(Grace: {{ $data['employee']->shift->grace_period_minutes }} min)</div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-warning" style="font-size: 1rem;">{{ $data['count'] }}</span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#lateDetailsModal{{ $employeeId }}">
                                                    View Details
                                                </button>
                                                
                                                <!-- Late Details Modal -->
                                                <div class="modal fade" id="lateDetailsModal{{ $employeeId }}" tabindex="-1" aria-labelledby="lateDetailsModalLabel{{ $employeeId }}" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="lateDetailsModalLabel{{ $employeeId }}">
                                                                    Late Details: {{ $data['employee']->full_name }}
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="table-responsive">
                                                                    <table class="table">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Date</th>
                                                                                <th>Clock In Time</th>
                                                                                <th>Expected Time</th>
                                                                                <th>Minutes Late</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach($data['dates'] as $lateDate)
                                                                                <tr>
                                                                                    <td>{{ \Carbon\Carbon::parse($lateDate['date'])->format('M d, Y (D)') }}</td>
                                                                                    <td>{{ $lateDate['clock_in'] }}</td>
                                                                                    <td>{{ \Carbon\Carbon::parse($data['employee']->shift->start_time)->format('h:i A') }}</td>
                                                                                    <td>{{ $lateDate['minutes_late'] }} min</td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No late arrivals found in the selected date range.
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