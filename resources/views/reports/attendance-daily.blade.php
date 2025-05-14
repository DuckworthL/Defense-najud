@extends('layouts.app')

@section('title', 'Daily Attendance Report')

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
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    @foreach ($period as $date)
                                        <th>{{ $date->format('M d') }}<br><small>{{ $date->format('D') }}</small></th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($attendanceData as $employeeId => $data)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    @if($data['employee']->profile_picture)
                                                    <img src="{{ asset('storage/' . $data['employee']->profile_picture) }}" class="rounded-circle" width="30" height="30" alt="{{ $data['employee']->full_name }}">
                                                    @else
                                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 12px;">
                                                        {{ strtoupper(substr($data['employee']->first_name, 0, 1) . substr($data['employee']->last_name, 0, 1)) }}
                                                    </div>
                                                    @endif
                                                </div>
                                                {{ $data['employee']->full_name }}
                                            </div>
                                        </td>
                                        <td>{{ $data['employee']->department->name }}</td>
                                        @foreach ($period as $date)
                                            <td style="background-color: {{ $data['attendance'][$date->format('Y-m-d')]['color'] ?? '#ffffff' }}29;">
                                                <div class="text-center">
                                                    <div>{{ $data['attendance'][$date->format('Y-m-d')]['status'] ?? '-' }}</div>
                                                    <small>
                                                        @if($data['attendance'][$date->format('Y-m-d')]['clock_in'] != '-')
                                                            {{ $data['attendance'][$date->format('Y-m-d')]['clock_in'] }} - 
                                                            {{ $data['attendance'][$date->format('Y-m-d')]['clock_out'] }}
                                                        @endif
                                                    </small>
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
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