@extends('layouts.app')

@section('title', 'Edit Attendance Record')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Attendance Record</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                @if($attendance->employee->profile_picture)
                                <img src="{{ asset('storage/' . $attendance->employee->profile_picture) }}" class="rounded-circle" width="60" height="60" alt="{{ $attendance->employee->full_name }}">
                                @else
                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    {{ strtoupper(substr($attendance->employee->first_name, 0, 1) . substr($attendance->employee->last_name, 0, 1)) }}
                                </div>
                                @endif
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $attendance->employee->full_name }}</h5>
                                <p class="mb-0">{{ $attendance->employee->employee_id }} | {{ $attendance->employee->department->name }}</p>
                            </div>
                        </div>
                        <p class="mb-0"><strong>Date:</strong> {{ $attendance->date->format('l, F d, Y') }}</p>
                    </div>

                    <form action="{{ route('attendance.update', $attendance) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="attendance_status_id" class="form-label">Status</label>
                            <select class="form-select" id="attendance_status_id" name="attendance_status_id" required>
                                @foreach($statuses as $status)
                                <option value="{{ $status->id }}" {{ $attendance->attendance_status_id == $status->id ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="clock_in_time" class="form-label">Clock In Time</label>
                                <input type="datetime-local" class="form-control" id="clock_in_time" name="clock_in_time" 
                                    value="{{ $attendance->clock_in_time ? $attendance->clock_in_time->format('Y-m-d\TH:i') : '' }}">
                                
                                @if($attendance->clock_in_time && $attendance->clock_in_time != request('clock_in_time'))
                                <div class="mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_clock_in_reset" name="is_clock_in_reset" checked>
                                        <label class="form-check-label" for="is_clock_in_reset">
                                            Record this change
                                        </label>
                                    </div>
                                    <div class="mt-2">
                                        <label for="clock_in_reset_reason" class="form-label">Reason for change</label>
                                        <textarea class="form-control" id="clock_in_reset_reason" name="clock_in_reset_reason" rows="2" required></textarea>
                                    </div>
                                </div>
                                @endif
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="clock_out_time" class="form-label">Clock Out Time</label>
                                <input type="datetime-local" class="form-control" id="clock_out_time" name="clock_out_time" 
                                    value="{{ $attendance->clock_out_time ? $attendance->clock_out_time->format('Y-m-d\TH:i') : '' }}">
                                
                                @if($attendance->clock_out_time && $attendance->clock_out_time != request('clock_out_time'))
                                <div class="mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_clock_out_reset" name="is_clock_out_reset" checked>
                                        <label class="form-check-label" for="is_clock_out_reset">
                                            Record this change
                                        </label>
                                    </div>
                                    <div class="mt-2">
                                        <label for="clock_out_reset_reason" class="form-label">Reason for change</label>
                                        <textarea class="form-control" id="clock_out_reset_reason" name="clock_out_reset_reason" rows="2" required></textarea>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="3">{{ $attendance->remarks }}</textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Show/hide reason fields based on checkbox state
    document.addEventListener('DOMContentLoaded', function() {
        const clockInTime = document.getElementById('clock_in_time');
        const clockOutTime = document.getElementById('clock_out_time');
        const initialClockInTime = clockInTime.value;
        const initialClockOutTime = clockOutTime.value;
        
        clockInTime.addEventListener('change', function() {
            const resetReasonDiv = document.getElementById('clock_in_reset_reason')?.closest('.mt-2');
            if (resetReasonDiv) {
                if (this.value !== initialClockInTime) {
                    resetReasonDiv.style.display = 'block';
                } else {
                    resetReasonDiv.style.display = 'none';
                }
            }
        });
        
        clockOutTime.addEventListener('change', function() {
            const resetReasonDiv = document.getElementById('clock_out_reset_reason')?.closest('.mt-2');
            if (resetReasonDiv) {
                if (this.value !== initialClockOutTime) {
                    resetReasonDiv.style.display = 'block';
                } else {
                    resetReasonDiv.style.display = 'none';
                }
            }
        });
    });
</script>
@endsection