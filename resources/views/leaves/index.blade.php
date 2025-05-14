@extends('layouts.app')

@section('title', 'Leave Requests')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .filters {
        background-color: #f8f9fc;
        padding: 1rem;
        border-radius: 0.35rem;
        margin-bottom: 1rem;
    }
    .badge-without-pay {
        background-color: #f6c23e;
        color: #fff;
    }
    .leave-details {
        font-size: 0.85rem;
    }
    .badge-pending {
        background-color: #f6c23e;
        color: #212529;
    }
    .badge-approved {
        background-color: #1cc88a;
        color: #fff;
    }
    .badge-rejected {
        background-color: #e74a3b;
        color: #fff;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Leave Requests</h1>
        <a href="{{ route('leaves.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Request Leave
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="filters">
        <form action="{{ route('leaves.index') }}" method="GET">
            <div class="row">
                @if(auth()->user()->isAdmin() || auth()->user()->isHR())
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="employee_id">Employee</label>
                        <select class="form-control" id="employee_id" name="employee_id">
                            <option value="">All Employees</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->first_name }} {{ $employee->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="leave_type_id">Leave Type</label>
                        <select class="form-control" id="leave_type_id" name="leave_type_id">
                            <option value="">All Types</option>
                            @foreach($leaveTypes as $leaveType)
                                <option value="{{ $leaveType->id }}" {{ request('leave_type_id') == $leaveType->id ? 'selected' : '' }}>
                                    {{ $leaveType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="start_date">From Date</label>
                        <input type="text" class="form-control datepicker" id="start_date" name="start_date" placeholder="From" value="{{ request('start_date') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="end_date">To Date</label>
                        <input type="text" class="form-control datepicker" id="end_date" name="end_date" placeholder="To" value="{{ request('end_date') }}">
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group" style="margin-top: 32px;">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Leave Requests</h6>
            @if(request()->has('employee_id') || request()->has('leave_type_id') || request()->has('status') || request()->has('start_date') || request()->has('end_date'))
                <a href="{{ route('leaves.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times"></i> Clear Filters
                </a>
            @endif
        </div>
        <div class="card-body">
            @if(count($leaves) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaves as $leave)
                                <tr>
                                    <td>
                                        {{ $leave->employee->first_name }} {{ $leave->employee->last_name }}
                                        <small class="d-block text-muted">{{ $leave->employee->employee_id }}</small>
                                    </td>
                                    <td>
                                        {{ $leave->leaveType->name }}
                                        @if($leave->is_without_pay)
                                            <span class="badge badge-without-pay">Without Pay</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $leave->start_date->format('M d, Y') }} - {{ $leave->end_date->format('M d, Y') }}</div>
                                        <small class="text-muted">{{ $leave->days_count }} day(s)</small>
                                    </td>
                                    <td>
                                        @if($leave->status === 'pending')
                                            <span class="badge badge-pending">Pending</span>
                                        @elseif($leave->status === 'approved')
                                            <span class="badge badge-approved">Approved</span>
                                        @else
                                            <span class="badge badge-rejected">Rejected</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('leaves.show', $leave) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        
                                        @if($leave->status === 'pending')
                                            @if(auth()->user()->isAdmin() || auth()->user()->isHR())
                                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $leave->id }}">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $leave->id }}">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            @endif
                                            
                                            @if(auth()->id() === $leave->employee_id || auth()->user()->isAdmin() || auth()->user()->isHR())
                                                <form action="{{ route('leaves.destroy', $leave) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('Are you sure you want to cancel this leave request?')">
                                                        <i class="fas fa-ban"></i> Cancel
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                                
                                <!-- Approve Modal -->
                                <div class="modal fade" id="approveModal{{ $leave->id }}" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel{{ $leave->id }}" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="approveModalLabel{{ $leave->id }}">Approve Leave Request</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('leaves.approve', $leave) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <p>Are you sure you want to approve this leave request?</p>
                                                    
                                                    <div class="leave-details mb-3">
                                                        <div><strong>Employee:</strong> {{ $leave->employee->first_name }} {{ $leave->employee->last_name }}</div>
                                                        <div><strong>Leave Type:</strong> {{ $leave->leaveType->name }}</div>
                                                        <div><strong>Duration:</strong> {{ $leave->start_date->format('M d, Y') }} - {{ $leave->end_date->format('M d, Y') }} ({{ $leave->days_count }} day(s))</div>
                                                        @if($leave->is_without_pay)
                                                            <div class="text-warning"><strong>Note:</strong> This leave will be processed without pay due to insufficient credits.</div>
                                                        @endif
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
                                <div class="modal fade" id="rejectModal{{ $leave->id }}" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel{{ $leave->id }}" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="rejectModalLabel{{ $leave->id }}">Reject Leave Request</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('leaves.reject', $leave) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="leave-details mb-3">
                                                        <div><strong>Employee:</strong> {{ $leave->employee->first_name }} {{ $leave->employee->last_name }}</div>
                                                        <div><strong>Leave Type:</strong> {{ $leave->leaveType->name }}</div>
                                                        <div><strong>Duration:</strong> {{ $leave->start_date->format('M d, Y') }} - {{ $leave->end_date->format('M d, Y') }} ({{ $leave->days_count }} day(s))</div>
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
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    {{ $leaves->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No leave requests found.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize date pickers
        flatpickr(".datepicker", {
            dateFormat: "Y-m-d",
            allowInput: true
        });
        
        // Auto-submit form on change (optional)
        document.querySelectorAll('.filters select').forEach(function(select) {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
    });
</script>
@endsection