@extends('layouts.app')

@section('title', 'Edit Leave Request')

@section('styles')
<style>
    .form-section {
        background-color: #f8f9fc;
        padding: 1.5rem;
        border-radius: 0.35rem;
        margin-bottom: 2rem;
    }
    .form-section-title {
        border-bottom: 1px solid #e3e6f0;
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
    }
    .required-field::after {
        content: "*";
        color: red;
        margin-left: 4px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Leave Request</h1>
        <a href="{{ route('leaves.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Leaves
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('leaves.update', $leave->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Leave Request Information</h6>
            </div>
            <div class="card-body">
                <div class="form-section">
                    <h5 class="form-section-title">Basic Information</h5>
                    <div class="row">
                        <!-- Employee selection (only for Admin/HR) -->
                        @if(auth()->user()->isAdmin() || auth()->user()->isHR())
                            <div class="col-md-6 mb-3">
                                <label for="employee_id" class="form-label required-field">Employee</label>
                                <select class="form-select" id="employee_id" name="employee_id" required {{ $leave->status !== 'pending' ? 'disabled' : '' }}>
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ $leave->employee_id == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_id }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="employee_id" value="{{ auth()->id() }}">
                        @endif
                        
                        <!-- Leave type -->
                        <div class="col-md-6 mb-3">
                            <label for="leave_type" class="form-label required-field">Leave Type</label>
                            <select class="form-select" id="leave_type" name="leave_type" required {{ $leave->status !== 'pending' ? 'disabled' : '' }}>
                                <option value="">Select Leave Type</option>
                                <option value="annual" {{ $leave->leave_type === 'annual' ? 'selected' : '' }}>Annual Leave</option>
                                <option value="sick" {{ $leave->leave_type === 'sick' ? 'selected' : '' }}>Sick Leave</option>
                                <option value="maternity" {{ $leave->leave_type === 'maternity' ? 'selected' : '' }}>Maternity Leave</option>
                                <option value="paternity" {{ $leave->leave_type === 'paternity' ? 'selected' : '' }}>Paternity Leave</option>
                                <option value="bereavement" {{ $leave->leave_type === 'bereavement' ? 'selected' : '' }}>Bereavement Leave</option>
                                <option value="unpaid" {{ $leave->leave_type === 'unpaid' ? 'selected' : '' }}>Unpaid Leave</option>
                                <option value="other" {{ $leave->leave_type === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Start date -->
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label required-field">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $leave->start_date->format('Y-m-d') }}" required {{ $leave->status !== 'pending' ? 'disabled' : '' }}>
                        </div>
                        
                        <!-- End date -->
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label required-field">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $leave->end_date->format('Y-m-d') }}" required {{ $leave->status !== 'pending' ? 'disabled' : '' }}>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h5 class="form-section-title">Leave Details</h5>
                    <div class="row">
                        <!-- Reason -->
                        <div class="col-md-12 mb-3">
                            <label for="reason" class="form-label required-field">Reason for Leave</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" required {{ $leave->status !== 'pending' ? 'disabled' : '' }}>{{ $leave->reason }}</textarea>
                        </div>
                    </div>
                    
                    <!-- Additional documents -->
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <label class="form-label">Supporting Documents</label>
                            @if($leave->documents)
                                <div class="mb-3">
                                    <p class="mb-1">Current document: 
                                        <a href="{{ asset('storage/' . $leave->documents) }}" target="_blank">
                                            <i class="fas fa-file-alt"></i> View Document
                                        </a>
                                    </p>
                                </div>
                            @endif

                            @if($leave->status === 'pending')
                                <div class="input-group mb-3">
                                    <input type="file" class="form-control" id="new_document" name="new_document">
                                    <label class="input-group-text" for="new_document">Upload</label>
                                </div>
                                <p class="text-muted small">Optional. Upload supporting documents (medical certificate, etc).</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                @if(auth()->user()->isAdmin() || auth()->user()->isHR())
                    <div class="form-section">
                        <h5 class="form-section-title">Leave Status</h5>
                        <div class="row">
                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label required-field">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="pending" {{ $leave->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ $leave->status === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ $leave->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            
                            <!-- Admin remarks -->
                            <div class="col-md-6 mb-3">
                                <label for="remarks" class="form-label">Remarks</label>
                                <textarea class="form-control" id="remarks" name="remarks" rows="1">{{ $leave->remarks }}</textarea>
                            </div>
                        </div>
                    </div>
                @endif
                
                <div class="mt-4 text-end">
                    <a href="{{ route('leaves.index') }}" class="btn btn-secondary">Cancel</a>
                    @if($leave->status === 'pending' || auth()->user()->isAdmin() || auth()->user()->isHR())
                        <button type="submit" class="btn btn-primary">Update Leave Request</button>
                    @endif
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Date validation
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        
        function validateDates() {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
            
            if (endDate < startDate) {
                endDateInput.setCustomValidity('End date must be after or equal to start date');
            } else {
                endDateInput.setCustomValidity('');
            }
        }
        
        startDateInput.addEventListener('change', validateDates);
        endDateInput.addEventListener('change', validateDates);
        
        // For initial validation
        if (startDateInput.value && endDateInput.value) {
            validateDates();
        }
    });
</script>
@endsection