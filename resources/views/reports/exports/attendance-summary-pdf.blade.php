<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        h1 {
            font-size: 18px;
            text-align: center;
            margin-bottom: 10px;
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
            padding: 7px 5px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .attendance-high {
            background-color: #d4edda;
        }
        .attendance-medium {
            background-color: #fff3cd;
        }
        .attendance-low {
            background-color: #f8d7da;
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

    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Department</th>
                <th>Present</th>
                <th>Late</th>
                <th>Early Departure</th>
                <th>Absent</th>
                <th>Leave</th>
                <th>Total Days</th>
                <th>Attendance Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summaryData as $employeeId => $data)
                @php 
                    $rateClass = 'attendance-high';
                    if ($data['attendance_rate'] < 90) $rateClass = 'attendance-medium';
                    if ($data['attendance_rate'] < 75) $rateClass = 'attendance-low';
                @endphp
                <tr>
                    <td>{{ $data['employee']->full_name }} ({{ $data['employee']->employee_id }})</td>
                    <td>{{ $data['employee']->department->name }}</td>
                    <td>{{ $data['present'] }}</td>
                    <td>{{ $data['late'] }}</td>
                    <td>{{ $data['early_departure'] ?? 0 }}</td>
                    <td>{{ $data['absent'] }}</td>
                    <td>{{ $data['leave'] }}</td>
                    <td>{{ $data['total_days'] }}</td>
                    <td class="{{ $rateClass }}">{{ $data['attendance_rate'] }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Employee Attendance Management System &copy; {{ date('Y') }}
    </div>
</body>
</html>