@extends('layouts.app')

@section('title', 'Attendance Counter Terminal')

@section('styles')
<style>
    .time-display {
        font-size: 1.5rem;
        font-weight: bold;
    }
    
    .search-card {
        transition: all 0.3s ease;
        border-left: 4px solid #4e73df;
    }
    
    .quick-action-btn {
        margin-bottom: 10px;
        padding: 15px;
        text-align: left;
        font-weight: 500;
    }
    
    .activity-list {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .clock-card {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .date-time {
        font-size: 28px;
        font-weight: bold;
        letter-spacing: 1px;
    }
    
    .date {
        font-size: 18px;
        opacity: 0.9;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Attendance Counter Terminal</h1>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="clock-card mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <h4><i class="fas fa-clock"></i> Current Time</h4>
                        <div class="date-time" id="current-time">{{ now()->format('h:i:s A') }}</div>
                        <div class="date">{{ now()->format('l, F d, Y') }}</div>
                    </div>
                    <div class="col-md-6 text-end d-none d-md-block">
                        <i class="far fa-clock fa-4x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Search Employee</h6>
                </div>
                <div class="card-body search-card">
                    <form action="{{ route('counter.search') }}" method="GET" id="search-form">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control form-control-lg" id="search-input" name="query" placeholder="Search by Employee ID, First Name, or Last Name" autofocus>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Search Tips:</strong>
                        <ul class="mb-0">
                            <li>Enter an Employee ID (e.g., <strong>EMP001</strong>) for exact matches</li>
                            <li>Enter a first name, last name, or full name for name-based searches</li>
                            <li>At least 2 characters required for search</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('counter.search') }}" class="btn btn-primary btn-block quick-action-btn">
                        <i class="fas fa-search fa-fw"></i> Advanced Employee Search
                    </a>
                    <a href="{{ route('attendance.create') }}" class="btn btn-info btn-block quick-action-btn">
                        <i class="fas fa-plus fa-fw"></i> Manual Attendance Entry
                    </a>
                    <a href="{{ route('attendance.index') }}" class="btn btn-secondary btn-block quick-action-btn">
                        <i class="fas fa-list fa-fw"></i> View All Attendance Records
                    </a>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body activity-list">
                    @if(count($recentAttendance) > 0)
                        <ul class="list-group">
                            @foreach($recentAttendance as $attendance)
                                <li class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $attendance->employee->full_name ?? $attendance->employee->first_name . ' ' . $attendance->employee->last_name }}</h6>
                                        <small>
                                            @if($attendance->clock_in_time && !$attendance->clock_out_time)
                                                <span class="badge bg-success">Clocked In</span>
                                            @elseif($attendance->clock_in_time && $attendance->clock_out_time)
                                                <span class="badge bg-info">Clocked Out</span>
                                            @endif
                                        </small>
                                    </div>
                                    <p class="mb-1">{{ $attendance->employee->employee_id }} - {{ $attendance->employee->department->name ?? 'No Department' }}</p>
                                    <small>
                                        @if($attendance->clock_in_time)
                                            In: {{ $attendance->clock_in_time->format('h:i A') }}
                                        @endif
                                        @if($attendance->clock_out_time)
                                            , Out: {{ $attendance->clock_out_time->format('h:i A') }}
                                        @endif
                                    </small>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i> No recent attendance activities today.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Update the clock every second with Philippines timezone (UTC+8)
    setInterval(function() {
        const options = {
            timeZone: 'Asia/Manila',
            hour12: true,
            hour: 'numeric',
            minute: 'numeric',
            second: 'numeric'
        };
        
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', options);
        document.getElementById('current-time').textContent = timeString;
    }, 1000);
</script>
@endsection