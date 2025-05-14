@extends('layouts.app')

@section('title', 'Verify Employee Attendance')

@section('styles')
<style>
    .profile-card {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
    }
    
    .profile-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        height: 120px;
        position: relative;
    }
    
    .profile-img {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        border: 5px solid #fff;
        position: absolute;
        bottom: -75px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #f8f9fc;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 60px;
        font-weight: bold;
        color: #4e73df;
    }
    
    .profile-body {
        padding-top: 85px;
        text-align: center;
    }
    
    .btn-clock {
        padding: 15px 30px;
        font-size: 18px;
        font-weight: 600;
    }
    
    .attendance-card {
        transition: all 0.3s ease;
    }
    
    .attendance-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .clock-display {
        font-size: 2.5rem;
        font-weight: bold;
        letter-spacing: 2px;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .confirmation-checkbox {
        margin-bottom: 20px;
        padding: 15px;
        border-radius: 10px;
        background-color: #e3f2fd;
        border: 1px solid #90caf9;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="m-0"><i class="fas fa-user-check"></i> Verify Employee Attendance</h4>
                    <div>
                        <span class="badge bg-light text-dark clock-display" id="current-time">{{ now()->format('h:i:s A') }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-5">
                            <div class="profile-card mb-4">
                                <div class="profile-header"></div>
                                <div class="profile-img">
                                    @if($employee->profile_picture)
                                        <img src="{{ asset('storage/' . $employee->profile_picture) }}" class="img-fluid rounded-circle" alt="{{ $employee->full_name ?? $employee->first_name . ' ' . $employee->last_name }}">
                                    @else
                                        {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                                    @endif
                                </div>
                                <div class="profile-body">
                                    <h3 class="mb-1">{{ $employee->full_name ?? $employee->first_name . ' ' . $employee->last_name }}</h3>
                                    <p class="text-muted mb-3">{{ $employee->employee_id }}</p>
                                    
                                    <div class="mb-3 py-2 px-3 bg-light rounded-pill">
                                        <i class="fas fa-building"></i> {{ $employee->department->name ?? 'No Department' }}
                                    </div>
                                    
                                    <div class="mb-3 py-2 px-3 bg-light rounded-pill">
                                        <i class="fas fa-user-tag"></i> {{ $employee->role->name ?? 'No Role' }}
                                    </div>
                                    
                                    <div class="mb-3 py-2 px-3 bg-light rounded-pill">
                                        <i class="fas fa-clock"></i> 
                                        {{ \Carbon\Carbon::parse($employee->shift->start_time)->format('h:i A') }} - 
                                        {{ \Carbon\Carbon::parse($employee->shift->end_time)->format('h:i A') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-7">
                            <div class="h-100">
                                <h4 class="mb-4">Attendance Actions</h4>
                                
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                <div class="card attendance-card mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Today's Status</h5>
                                    </div>
                                    <div class="card-body">
                                        @if($attendance)
                                            <div class="alert alert-info">
                                                <strong>Date:</strong> {{ \Carbon\Carbon::parse($attendance->date)->format('F d, Y (l)') }}
                                                <br>
                                                <strong>Status:</strong> 
                                                <span class="badge bg-{{ $attendance->attendanceStatus->name == 'Present' ? 'success' : ($attendance->attendanceStatus->name == 'Late' ? 'warning' : 'danger') }}">
                                                    {{ $attendance->attendanceStatus->name }}
                                                </span>
                                                <br>
                                                <strong>Clock In:</strong> 
                                                @if($attendance->clock_in_time)
                                                    {{ $attendance->clock_in_time->format('h:i:s A') }}
                                                @else
                                                    Not clocked in yet
                                                @endif
                                                <br>
                                                <strong>Clock Out:</strong> 
                                                @if($attendance->clock_out_time)
                                                    {{ $attendance->clock_out_time->format('h:i:s A') }}
                                                @else
                                                    Not clocked out yet
                                                @endif
                                            </div>
                                        @else
                                            <div class="alert alert-warning">
                                                <i class="fas fa-info-circle"></i> No attendance record exists for today.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="confirmation-checkbox">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="confirmIdentity">
                                        <label class="form-check-label" for="confirmIdentity">
                                            <strong>I confirm that I have visually verified this employee's identity</strong>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <button id="clockInBtn" class="btn btn-success btn-clock w-100 {{ ($attendance && $attendance->clock_in_time) ? 'disabled' : '' }}" {{ ($attendance && $attendance->clock_in_time) ? 'disabled' : '' }}>
                                            <i class="fas fa-sign-in-alt"></i> Clock In
                                        </button>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <button id="clockOutBtn" class="btn btn-warning btn-clock w-100 {{ ($attendance && !$attendance->clock_in_time) || !$attendance ? 'disabled' : '' }} {{ ($attendance && $attendance->clock_out_time) ? 'disabled' : '' }}">
                                            <i class="fas fa-sign-out-alt"></i> Clock Out
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <a href="{{ route('counter.search') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Search
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Result Modal -->
    <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resultModalLabel">Attendance Result</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="resultModalBody">
                    <!-- Content will be inserted dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="{{ route('counter.search') }}" class="btn btn-primary" id="searchNewBtn">
                        Search Another Employee
                    </a>
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
    
    // Clock in/out functionality
    document.addEventListener('DOMContentLoaded', function() {
        const clockInBtn = document.getElementById('clockInBtn');
        const clockOutBtn = document.getElementById('clockOutBtn');
        const confirmCheckbox = document.getElementById('confirmIdentity');
        
        // Function to check if confirmation checkbox is checked
        function isConfirmed() {
            if (!confirmCheckbox.checked) {
                alert('Please confirm that you have verified the employee\'s identity.');
                return false;
            }
            return true;
        }
        
        // Clock In
        if (clockInBtn && !clockInBtn.classList.contains('disabled')) {
            clockInBtn.addEventListener('click', function() {
                if (!isConfirmed()) return;
                
                processAttendance('clock_in');
            });
        }
        
        // Clock Out
        if (clockOutBtn && !clockOutBtn.classList.contains('disabled')) {
            clockOutBtn.addEventListener('click', function() {
                if (!isConfirmed()) return;
                
                processAttendance('clock_out');
            });
        }
        
        // Process attendance function
        function processAttendance(action) {
            // Show loading state
            const btn = action === 'clock_in' ? clockInBtn : clockOutBtn;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            btn.disabled = true;
            
            fetch('{{ route("counter.process-verification") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    employee_id: {{ $employee->id }},
                    verified_by: {{ auth()->id() }},
                    action: action
                })
            })
            .then(response => response.json())
            .then(data => {
                // Reset button
                btn.innerHTML = originalText;
                btn.disabled = false;
                
                // Show result modal
                const resultModalBody = document.getElementById('resultModalBody');
                
                if (data.success) {
                    resultModalBody.innerHTML = `
                        <div class="text-center">
                            <div class="mb-4">
                                <i class="fas fa-check-circle text-success" style="font-size: 64px;"></i>
                            </div>
                            <h4 class="text-success">Success!</h4>
                            <p>${data.message}</p>
                            <div class="alert alert-info">
                                <strong>Employee:</strong> {{ $employee->full_name ?? $employee->first_name . ' ' . $employee->last_name }}<br>
                                <strong>ID:</strong> {{ $employee->employee_id }}<br>
                                <strong>Time:</strong> ${new Date().toLocaleTimeString('en-US', {timeZone: 'Asia/Manila'})}
                            </div>
                        </div>
                    `;
                    
                    // Disable the button that was just used
                    btn.classList.add('disabled');
                    btn.disabled = true;
                    
                    // If clocked in, enable clock out button
                    if (action === 'clock_in') {
                        clockOutBtn.classList.remove('disabled');
                        clockOutBtn.disabled = false;
                    }
                } else {
                    resultModalBody.innerHTML = `
                        <div class="text-center">
                            <div class="mb-4">
                                <i class="fas fa-exclamation-circle text-danger" style="font-size: 64px;"></i>
                            </div>
                            <h4 class="text-danger">Error</h4>
                            <p>${data.message}</p>
                        </div>
                    `;
                }
                
                // Show modal
                new bootstrap.Modal(document.getElementById('resultModal')).show();
            })
            .catch(error => {
                // Reset button
                btn.innerHTML = originalText;
                btn.disabled = false;
                
                // Show error in modal
                const resultModalBody = document.getElementById('resultModalBody');
                resultModalBody.innerHTML = `
                    <div class="text-center">
                        <div class="mb-4">
                            <i class="fas fa-exclamation-triangle text-danger" style="font-size: 64px;"></i>
                        </div>
                        <h4 class="text-danger">System Error</h4>
                        <p>An unexpected error occurred. Please try again or contact system administrator.</p>
                        <div class="alert alert-danger">
                            ${error.message}
                        </div>
                    </div>
                `;
                
                // Show modal
                new bootstrap.Modal(document.getElementById('resultModal')).show();
                console.error('Error:', error);
            });
        }
    });
</script>
@endsection