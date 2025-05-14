<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        h1 {
            font-size: 18px;
            text-align: center;
            margin-bottom: 10px;
        }
        h2 {
            font-size: 16px;
            margin-top: 15px;
            margin-bottom: 5px;
        }
        p.report-meta {
            text-align: center;
            margin-bottom: 20px;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 5px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .employee-info {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .page-break {
            page-break-after: always;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #777;
            padding: 10px 0;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p class="report-meta">
        Period: {{ $start_date }} to {{ $end_date }}<br>
        Generated on: {{ date('Y-m-d H:i:s') }}
    </p>

    <table class="summary-table">
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
                    <td>{{ $data['employee']->full_name }} ({{ $data['employee']->employee_id }})</td>
                    <td>{{ $data['employee']->department->name }}</td>
                    <td>{{ $data['employee']->position ?? 'N/A' }}</td>
                    <td>{{ $data['count'] }}</td>
                    <td>{{ $avgMinutes }} minutes</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @foreach($employeeSummary as $employeeId => $data)
        <div class="employee-info">
            <h2>{{ $data['employee']->full_name }} (ID: {{ $data['employee']->employee_id }})</h2>
            <table>
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
        
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    <div class="footer">
        Employee Attendance Management System &copy; {{ date('Y') }}
    </div>
</body>
</html>