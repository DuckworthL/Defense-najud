@extends('layouts.app')

@section('title', 'Leave Request Details')

@section('styles')
<style>
    .leave-header {
        background-color: #f8f9fc;
        border-radius: 0.35rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .leave-header h4 {
        margin-bottom: 0.5rem;
        font-weight: 700;
    }
    .leave-details .row {
        margin-bottom: 1.5rem;
    }
    .leave-details .label {
        font-weight: 600;
        color: #5a5c69;
    }
    .leave-details .value {
        color: #333;
    }
    .badge-large {
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
    }
    .timeline {
        position: relative;
        padding-left: 3rem;
        margin-bottom: 3rem;
    }
    .timeline:before {
        content: "";
        position: absolute;
        left: 0.85rem;
        top: 0;
        height: 100%;
        width: 2px;
        background-color: #e3e6f0;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
    }
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    .timeline-marker {
        position: absolute;
        left: -34px;
        top: 0;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background-color: #4e73df;
        border: 3px solid #fff;
    }
    .timeline-date {
        font-size: 0.8rem;
        color: #858796;
        margin-bottom: 0.25rem;
    }
    .timeline-content {
        padding-bottom: 1rem;
    }
    .timeline-title {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    .payment-breakdown {
        background-color: #f8f9fc;
        border-radius: 0.35rem;
        padding: 1rem;
        margin-top: 1rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Leave Request Details</h1>
        <a href="{{ route('leaves.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Leaves
        </a>
    </div>

    <div class="leave-header">
        <div class="row">
            <div class="col-md-8">
                <h4>{{ $leave->leaveType ? $leave->leaveType->name : 'Unknown' }} Leave Request</h4>
                <div>{{ $leave->start_date->format('M d, Y') }} - {{ $leave->end_date->format('M d, Y') }} ({{ $leave->days_count ?? 0 }} day{{ ($leave->days_count ?? 0) > 1 ? 's' : '' }})</div>
            </div>
            <div class="col-md-4 text-md-right">
                @if($leave->status === 'pending')
                    <span class="badge badge-warning badge-large">Pending</span>
                @elseif($leave->status === 'approved')
                    <span class="badge badge-success badge-large">Approved</span>
                @else
                    <span class="badge badge-danger badge-large">Rejected</span>
                @endif
                
                @if($leave->is_without_pay)
                    <span class="badge badge-warning badge-large mt-2">Without Pay</span>
                @elseif($leave->requires_split_payment)
                    <span class="badge badge-info badge-large mt-2">Split Payment</span>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Leave Details</h6>
                </div>
                <div class="card-body">
                    <div class="leave-details">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="label">Employee</div>
                                <div class="value">{{ $leave->employee ? $leave->employee->first_name . ' ' . $leave->employee->last_name : 'Unknown' }}</div>
                                <div class="text-muted small">{{ $leave->employee ? $leave->employee->employee_id : 'N/A' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="label">Department</div>
                                <div class="value">{{ $leave->employee && $leave->employee->department ? $leave->employee->department->name : 'Not Assigned' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="label">Position</div>
                                <div class="value">{{ $leave->employee && $leave->employee->role ? $leave->employee->role->name : 'Not Assigned' }}</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="label">Leave Type</div>
                                <div class="value">{{ $leave->leaveType ? $leave->leaveType->name : 'Unknown' }}</div>
                                <div class="text-muted small">{{ $leave->leaveType ? ($leave->leaveType->is_paid ? 'Paid Leave' : 'Unpaid Leave') : 'N/A' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="label">Start Date</div>
                                <div class="value">{{ $leave->start_date ? $leave->start_date->format('M d, Y') : 'N/A' }}</div>
                                <div class="text-muted small">{{ $leave->start_date ? $leave->start_date->format('l') : 'N/A' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="label">End Date</div>
                                <div class="value">{{ $leave->end_date ? $leave->end_date->format('M d, Y') : 'N/A' }}</div>
                                <div class="text-muted small">{{ $leave->end_date ? $leave->end_date->format('l') : 'N/A' }}</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="label">Reason</div>
                                <div class="value">{{ $leave->reason ?? 'No reason provided' }}</div>
                            </div>
                        </div>
                        
                        @if($leave->leaveType && $leave->leaveType->is_paid)
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="label">Payment Breakdown</div>
                                    <div class="value">
                                        @if($leave->requires_split_payment)
                                            <div class="alert alert-warning p-2 mb-2">
                                                <i class="fas fa-info-circle"></i> This leave uses split payment (partially paid)
                                            </div>
                                        @endif
                                        
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="mr-3">
                                                <span class="badge bg-success">With Pay:</span> {{ number_format($leave->with_pay_days ?? 0, 2) }} day(s)
                                            </div>
                                            <div>
                                                <span class="badge bg-warning">Without Pay:</span> {{ number_format($leave->without_pay_days ?? 0, 2) }} day(s)
                                            </div>
                                        </div>
                                        
                                        <div class="progress" style="height: 20px;">
                                            @php
                                                $withPayPercent = $leave->days_count > 0 ? (($leave->with_pay_days ?? 0) / $leave->days_count) * 100 : 0;
                                                $withoutPayPercent = 100 - $withPayPercent;
                                            @endphp
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                style="width: {{ $withPayPercent }}%;" 
                                                aria-valuenow="{{ $withPayPercent }}" 
                                                aria-valuemin="0" 
                                                aria-valuemax="100">
                                                {{ round($withPayPercent) }}%
                                            </div>
                                            <div class="progress-bar bg-warning" role="progressbar" 
                                                style="width: {{ $withoutPayPercent }}%;" 
                                                aria-valuenow="{{ $withoutPayPercent }}" 
                                                aria-valuemin="0" 
                                                aria-valuemax="100">
                                                {{ round($withoutPayPercent) }}%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @if($leave->status !== 'pending')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="label">Processed By</div>
                                    <div class="value">{{ $leave->approver ? $leave->approver->first_name . ' ' . $leave->approver->last_name : 'N/A' }}</div>
                                    <div class="text-muted small">{{ $leave->approved_at ? $leave->approved_at->format('M d, Y H:i') : '' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="label">Status</div>
                                    <div class="value">
                                        @if($leave->status === 'approved')
                                            <span class="text-success">Approved</span>
                                        @else
                                            <span class="text-danger">Rejected</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @if($leave->remarks)
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="label">Remarks</div>
                                    <div class="value">{{ $leave->remarks }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    @if($leave->status === 'pending' && (auth()->user()->isAdmin() || auth()->user()->isHR()))
                        <div class="mt-4">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                                <i class="fas fa-check"></i> Approve
                            </button>
                            <button type="button" class="btn btn-danger ml-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Leave Request Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-date">{{ $leave->created_at ? $leave->created_at->format('M d, Y H:i') : 'N/A' }}</div>
                                <div class="timeline-title">Leave Requested</div>
                                <div>{{ $leave->employee ? $leave->employee->first_name . ' ' . $leave->employee->last_name : 'Employee' }} requested {{ $leave->days_count ?? 0 }} day(s) of {{ $leave->leaveType ? $leave->leaveType->name : 'leave' }}.</div>
                            </div>
                        </div>
                        
                        @if($leave->status !== 'pending')
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <div class="timeline-date">{{ $leave->approved_at ? $leave->approved_at->format('M d, Y H:i') : 'N/A' }}</div>
                                    <div class="timeline-title">
                                        Leave Request {{ $leave->status === 'approved' ? 'Approved' : 'Rejected' }}
                                    </div>
                                    <div>
                                        {{ $leave->approver ? $leave->approver->first_name . ' ' . $leave->approver->last_name : 'Admin' }} 
                                        {{ $leave->status === 'approved' ? 'approved' : 'rejected' }} the leave request.
                                    </div>
                                    @if($leave->remarks)
                                        <div class="mt-2 text-muted">
                                            "{{ $leave->remarks }}"
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">Approve Leave Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('leaves.approve', $leave) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to approve this leave request?</p>
                    
                    <div class="leave-details mb-3">
                        <div><strong>Employee:</strong> {{ $leave->employee ? $leave->employee->first_name . ' ' . $leave->employee->last_name : 'Unknown' }}</div>
                        <div><strong>Leave Type:</strong> {{ $leave->leaveType ? $leave->leaveType->name : 'Unknown' }}</div>
                        <div><strong>Duration:</strong> {{ $leave->start_date ? $leave->start_date->format('M d, Y') : 'N/A' }} - {{ $leave->end_date ? $leave->end_date->format('M d, Y') : 'N/A' }} ({{ $leave->days_count ?? 0 }} day(s))</div>
                        
                        @if($leave->leaveType && $leave->leaveType->is_paid)
                            <div class="mt-3 mb-1"><strong>Payment Details:</strong></div>
                            <div class="card bg-light">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <span class="badge bg-success">With Pay:</span> 
                                                {{ number_format($leave->with_pay_days ?? 0, 2) }} day(s)
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <span class="badge bg-warning">Without Pay:</span> 
                                                {{ number_format($leave->without_pay_days ?? 0, 2) }} day(s)
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="progress" style="height: 15px;">
                                        @php
                                            $withPayPercent = $leave->days_count > 0 ? (($leave->with_pay_days ?? 0) / $leave->days_count) * 100 : 0;
                                            $withoutPayPercent = 100 - $withPayPercent;
                                        @endphp
                                        <div class="progress-bar bg-success" role="progressbar" 
                                            style="width: {{ $withPayPercent }}%;" 
                                            aria-valuenow="{{ $withPayPercent }}" 
                                            aria-valuemin="0" 
                                            aria-valuemax="100">
                                            {{ round($withPayPercent) }}%
                                        </div>
                                        <div class="progress-bar bg-warning" role="progressbar" 
                                            style="width: {{ $withoutPayPercent }}%;" 
                                            aria-valuenow="{{ $withoutPayPercent }}" 
                                            aria-valuemin="0" 
                                            aria-valuemax="100">
                                            {{ round($withoutPayPercent) }}%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Approving this request will automatically update leave credit balances where applicable.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Reject Leave Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('leaves.reject', $leave) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="leave-details mb-3">
                        <div><strong>Employee:</strong> {{ $leave->employee ? $leave->employee->first_name . ' ' . $leave->employee->last_name : 'Unknown' }}</div>
                        <div><strong>Leave Type:</strong> {{ $leave->leaveType ? $leave->leaveType->name : 'Unknown' }}</div>
                        <div><strong>Duration:</strong> {{ $leave->start_date ? $leave->start_date->format('M d, Y') : 'N/A' }} - {{ $leave->end_date ? $leave->end_date->format('M d, Y') : 'N/A' }} ({{ $leave->days_count ?? 0 }} day(s))</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="remarks">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection