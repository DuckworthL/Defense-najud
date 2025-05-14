@extends('layouts.app')

@section('title', 'My Leave Credits')

@section('styles')
<style>
    .credit-card {
        border-left: 4px solid #4e73df;
        border-radius: 0.35rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        margin-bottom: 1.5rem;
    }
    .credit-card .card-body {
        padding: 1.25rem;
    }
    .credit-card.low-balance {
        border-left-color: #e74a3b;
    }
    .credit-card .leave-type {
        font-size: 0.8rem;
        text-transform: uppercase;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .credit-card .days-count {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 0.25rem;
    }
    .credit-card .progress {
        height: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .credit-card .details {
        display: flex;
        justify-content: space-between;
        color: #858796;
        font-size: 0.8rem;
    }
    .credit-card .expiry {
        color: #e74a3b;
        font-size: 0.8rem;
        margin-top: 0.5rem;
    }
    .stats-card {
        padding: 1.5rem;
        border-radius: 0.35rem;
        margin-bottom: 1.5rem;
    }
    .stats-card h5 {
        font-size: 1rem;
        margin-bottom: 1rem;
    }
    .leave-history {
        font-size: 0.85rem;
    }
    .leave-history .status {
        text-transform: capitalize;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Leave Credits</h1>
        <a href="{{ route('leaves.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Request Leave
        </a>
    </div>

    <div class="row">
        <!-- Leave Credits Cards -->
        <div class="col-lg-8">
            <div class="row">
                @forelse($leaveCredits as $credit)
                    <div class="col-md-6">
                        <div class="credit-card {{ $credit->remaining_days <= 2 ? 'low-balance' : '' }}">
                            <div class="card-body">
                                <div class="leave-type">{{ $credit->leaveType->name }}</div>
                                <div class="days-count">{{ number_format($credit->remaining_days, 2) }}</div>
                                <div class="text-xs text-muted">Days Remaining</div>
                                
                                <div class="progress mt-3">
                                    @php
                                        $percentUsed = ($credit->used_days / $credit->allocated_days) * 100;
                                        $remainingPercent = 100 - $percentUsed;
                                    @endphp
                                    <div class="progress-bar" role="progressbar" style="width: {{ $remainingPercent }}%;" 
                                        aria-valuenow="{{ $remainingPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                
                                <div class="details">
                                    <div>Used: {{ number_format($credit->used_days, 2) }}</div>
                                    <div>Total: {{ number_format($credit->allocated_days, 2) }}</div>
                                </div>
                                
                                @if($credit->expiry_date)
                                    <div class="expiry">
                                        <i class="fas fa-exclamation-circle"></i> Expires on {{ $credit->expiry_date->format('M d, Y') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> You don't have any leave credits allocated for the current fiscal year.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
        
        <!-- Leave Stats and History -->
        <div class="col-lg-4">
            <div class="stats-card bg-white shadow">
                <h5 class="text-primary"><i class="fas fa-calendar-check"></i> Leave Summary ({{ date('Y') }})</h5>
                
                <div class="stats-item mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <div>Total Leave Days Taken</div>
                        <div class="font-weight-bold">{{ number_format($totalLeaveDays, 2) }}</div>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 100%;" 
                            aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                
                <div class="stats-item mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <div>Pending Requests</div>
                        <div class="font-weight-bold">{{ $pendingLeaves }}</div>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 100%;" 
                            aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                
                <div class="stats-item">
                    <div class="d-flex justify-content-between mb-1">
                        <div>Without Pay Leave</div>
                        <div class="font-weight-bold">{{ number_format($withoutPayLeaveDays, 2) }}</div>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: 100%;" 
                            aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Leave History</h6>
                </div>
                <div class="card-body">
                    @if(count($recentLeaves) > 0)
                        <div class="leave-history">
                            @foreach($recentLeaves as $leave)
                                <div class="d-flex justify-content-between mb-3">
                                    <div>
                                        <div class="font-weight-bold">{{ $leave->leaveType->name }}</div>
                                        <div class="text-muted">
                                            {{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }}
                                            ({{ $leave->days_count }} day{{ $leave->days_count > 1 ? 's' : '' }})
                                        </div>
                                    </div>
                                    <div>
                                        @if($leave->status === 'pending')
                                            <span class="badge badge-warning status">{{ $leave->status }}</span>
                                        @elseif($leave->status === 'approved')
                                            <span class="badge badge-success status">{{ $leave->status }}</span>
                                        @else
                                            <span class="badge badge-danger status">{{ $leave->status }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            
                            @if(count($recentLeaves) >= 5)
                                <div class="text-center mt-3">
                                    <a href="{{ route('leaves.index') }}" class="btn btn-sm btn-light">View All Leave Requests</a>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-muted mb-0">No recent leave history found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection