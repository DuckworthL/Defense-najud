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
            padding: 5px;
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
        .status-present {
            background-color: #d4edda;
        }
        .status-late {
            background-color: #fff3cd;
        }
        .status-absent {
            background-color: #f8d7da;
        }
        .status-leave {
            background-color: #d1ecf1;
        }
        .status-earlydeparture {
            background-color: #ffe5d9;
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

    @foreach($attendanceData as $employeeId => $data)
        <div class="employee-info">
            <h2>{{ $data['employee']->full_name }} (ID: {{ $data['employee']->employee_id }})</h2>
            <p>
                <strong>Department:</strong> {{ $data['employee']->department->name }}<br>
                <strong>Shift:</strong> {{ $data['employee']->shift->name }} 
                ({{ $data['employee']->shift->start_time }} - {{ $data['employee']->shift->end_time }})
            </p>
            
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Work Hours</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($period as $date)
                        @php 
                            $dateString = $date->format('Y-m-d');
                            $statusClass = strtolower(str_replace(' ', '', $data['attendance'][$dateString]['status']));
                        @endphp
                        <tr class="status-{{ $statusClass }}">
                            <td>{{ $dateString }}</td>
                            <td>{{ $data['attendance'][$dateString]['status'] }}</td>
                            <td>{{ $data['attendance'][$dateString]['clock_in'] }}</td>
                            <td>{{ $data['attendance'][$dateString]['clock_out'] }}</td>
                            <td>{{ $data['attendance'][$dateString]['work_hours'] }}</td>
                            <td>{{ $data['attendance'][$dateString]['remarks'] }}</td>
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