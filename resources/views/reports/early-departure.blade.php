@extends('layouts.app')

@section('title', 'Early Departure Report')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Early Departure Report</h5>
                    <div>
                        @if(isset($start_date) && isset($end_date))
                        <form action="{{ route('reports.early-departure') }}" method="GET" class="d-inline">
                            <input type="hidden" name="start_date" value="{{ $start_date }}">
                            <input type="hidden" name="end_date" value="{{ $end_date }}">
                            <input type="hidden" name="department_id" value="{{ request('department_id') }}">
                            <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                            <input type="hidden" name="export_format" value="pdf">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Reports
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($employeeSummary) && count($employeeSummary) > 0)
                        <div class="mb-4">
                            <p class="mb-1">
                                <strong>Report Period:</strong> {{ $start_date }} to {{ $end_date }}
                            </p>
                        </div>

                        <div class="mb-4">
                            <h6>Employee Summary</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Department</th>
                                            <th>Position</th>
                                            <th>Early Departure Count</th>
                                            <th>Average Minutes Early</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employeeSummary as $employeeId => $data)
                                            @php
                                                $totalMinutes = 0;
                                                $validCount = 0;
                                                foreach ($data['dates'] as $date) {
                                                    if ($date['minutes_early'] !== '-') {
                                                        $totalMinutes += $date['minutes_early'];
                                                        $validCount++;
                                                    }
                                                }
                                                $avgMinutes = $validCount > 0 ? round($totalMinutes / $validCount) : 0;
                                            @endphp
                                            <tr>
                                                <td>{{ $data['employee']->full_name }}</td>
                                                <td>{{ $data['employee']->department->name }}</td>
                                                <td>{{ $data['employee']->position ?? 'N/A' }}</td>
                                                <td>{{ $data['count'] }}</td>
                                                <td>{{ $avgMinutes }} minutes</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @foreach($employeeSummary as $employeeId => $data)
                            <div class="mb-4">
                                <h6>{{ $data['employee']->full_name }} (ID: {{ $data['employee']->employee_id }})</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Clock Out Time</th>
                                                <th>Expected End Time</th>
                                                <th>Minutes Early</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($data['dates'] as $date)
                                                <tr>
                                                    <td>{{ $date['date'] }}</td>
                                                    <td>{{ $date['clock_out'] }}</td>
                                                    <td>{{ $data['employee']->shift->end_time }}</td>
                                                    <td>{{ $date['minutes_early'] }}</td>
                                                    <td>{{ $date['remarks'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p>No early departures found for the selected criteria.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection