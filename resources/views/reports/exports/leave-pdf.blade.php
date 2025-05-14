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
            padding: 6px 5px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .status-pending {
            background-color: #fff3cd;
        }
        .status-approved {
            background-color: #d4edda;
        }
        .status-rejected {
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
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Duration</th>
                <th>Status</th>
                <th>With Pay</th>
                <th>Without Pay</th>
                <th>Approved By</th>
                <th>Reason</th>
            </tr>
        </thead>
        <tbody>
            @foreach($leaves as $leave)
                <tr class="status-{{ $leave->status }}">
                    <td>{{ $leave->employee->full_name }} ({{ $leave->employee->employee_id }})</td>
                    <td>{{ $leave->employee->department->name }}</td>
                    <td>{{ $leave->leave_type }}</td>
                    <td>{{ $leave->start_date->format('Y-m-d') }}</td>
                    <td>{{ $leave->end_date->format('Y-m-d') }}</td>
                    <td>{{ $leave->duration }} day(s)</td>
                    <td>{{ ucfirst($leave->status) }}</td>
                    <td>{{ $leave->with_pay_days ?? '-' }}</td>
                    <td>{{ $leave->without_pay_days ?? '-' }}</td>
                    <td>{{ $leave->approver ? $leave->approver->name : '-' }}</td>
                    <td>{{ $leave->reason }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Employee Attendance Management System &copy; {{ date('Y') }}
    </div>
</body>
</html>