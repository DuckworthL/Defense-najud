@extends('layouts.app')

@section('title', 'Employee Search')

@section('styles')
<style>
    .search-container {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    
    .search-result-item {
        transition: all 0.3s ease;
    }
    
    .search-result-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .clock-display {
        font-size: 2rem;
        font-weight: bold;
        letter-spacing: 2px;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .search-help {
        background-color: #e3f2fd;
        border-left: 4px solid #2196f3;
        padding: 10px 15px;
        margin-bottom: 20px;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-search"></i> Employee Search
                        </h4>
                        <div>
                            <span class="badge bg-light text-dark clock-display" id="current-time">{{ now()->format('h:i:s A') }}</span>
                            <span class="badge bg-light text-dark">{{ now()->format('l, F d, Y') }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body search-container">
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="search-help">
                                <h5><i class="fas fa-info-circle"></i> Search Instructions:</h5>
                                <p class="mb-0">You can search by:</p>
                                <ul class="mb-0">
                                    <li><strong>Employee ID</strong> - Enter the complete ID (e.g., EMP001)</li>
                                    <li><strong>First or Last Name</strong> - Enter at least 3 characters of the employee's name</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row justify-content-center">
                        <div class="col-md-10">
                            <form id="search-form" action="{{ route('counter.search') }}" method="GET">
                                <div class="input-group mb-3 input-group-lg">
                                    <input type="text" class="form-control form-control-lg" id="search-input" name="query" placeholder="Enter Employee ID or Name..." value="{{ $searchQuery ?? '' }}" autofocus>
                                    <button class="btn btn-primary btn-lg" type="submit" id="search-button">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                </div>
                                <div class="form-text text-center mb-3">
                                    Enter at least 3 characters to begin searching
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <!-- Direct search results display (server-side) -->
                            @if(isset($employees) && $searchQuery)
                                @if($employees->count() > 0)
                                    <div class="row" id="employee-list">
                                        @foreach($employees as $employee)
                                            <div class="col-md-6 mb-4">
                                                <div class="card search-result-item h-100">
                                                    <div class="card-body">
                                                        <div class="d-flex align-items-center">
                                                            <div class="me-3">
                                                                @if($employee->profile_picture)
                                                                    <img src="{{ asset('storage/' . $employee->profile_picture) }}" class="rounded-circle" width="80" height="80" alt="{{ $employee->full_name ?? $employee->first_name . ' ' . $employee->last_name }}">
                                                                @else
                                                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 32px;">
                                                                        {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div>
                                                                <h5 class="card-title mb-1">{{ $employee->first_name }} {{ $employee->last_name }}</h5>
                                                                <p class="card-text mb-1">ID: <strong>{{ $employee->employee_id }}</strong></p>
                                                                <p class="card-text text-muted mb-1">{{ $employee->department->name ?? 'No Department' }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer bg-light d-grid">
                                                        <a href="{{ route('counter.verify', $employee->id) }}" class="btn btn-primary">
                                                            <i class="fas fa-check-circle"></i> Select & Process Attendance
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-warning text-center" id="no-results">
                                        <i class="fas fa-exclamation-circle"></i> No employees found matching your search criteria.
                                        <div class="mt-3">
                                            <p><strong>Debugging Info:</strong></p>
                                            <p>Search Query: "{{ $searchQuery }}"</p>
                                            <p>Please try again with a different search term or contact system administrator.</p>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div id="search-results"></div>
                                <div id="no-results" class="alert alert-warning text-center d-none">
                                    <i class="fas fa-exclamation-circle"></i> No employees found matching your search criteria.
                                </div>
                                <div id="loading" class="text-center d-none">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Searching...</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('counter.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Counter
                            </a>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="{{ route('attendance.create') }}" class="btn btn-info">
                                <i class="fas fa-plus"></i> Manual Attendance
                            </a>
                        </div>
                    </div>
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