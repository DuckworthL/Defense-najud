@extends('layouts.app')

@section('title', 'Leave Credits')

@section('styles')
<style>
    .credit-card {
        border-left: 4px solid #4e73df;
    }
    .credit-card.low-balance {
        border-left-color: #e74a3b;
    }
    .credit-card .remaining {
        font-size: 1.5rem;
        font-weight: bold;
    }
    .credit-card .used {
        font-size: 0.9rem;
        color: #858796;
    }
    .filters {
        background-color: #f8f9fc;
        padding: 1rem;
        border-radius: 0.35rem;
        margin-bottom: 1rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Leave Credits</h1>
        <div>
            <a href="{{ route('leave-credits.bulk-allocate') }}" class="btn btn-sm btn-info shadow-sm mr-2">
                <i class="fas fa-layer-group fa-sm text-white-50"></i> Bulk Allocate
            </a>
            <a href="{{ route('leave-credits.create') }}" class="btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Add Leave Credit
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="filters">
        <form action="{{ route('leave-credits.index') }}" method="GET">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="employee_id">Employee</label>
                        <select class="form-control" id="employee_id" name="employee_id">
                            <option value="">All Employees</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
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
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="fiscal_year">Fiscal Year</label>
                        <select class="form-control" id="fiscal_year" name="fiscal_year">
                            @php
                                $currentYear = date('Y');
                                $startYear = $currentYear - 2;
                                $endYear = $currentYear + 2;
                            @endphp
                            @for($year = $startYear; $year <= $endYear; $year++)
                                <option value="{{ $year }}" {{ $fiscalYear == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group" style="margin-top: 32px;">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Leave Credits for Fiscal Year: {{ $fiscalYear }}</h6>
            @if(request('employee_id') || request('leave_type_id'))
                <a href="{{ route('leave-credits.index', ['fiscal_year' => $fiscalYear]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times"></i> Clear Filters
                </a>
            @endif
        </div>
        <div class="card-body">
            @if(count($leaveCredits) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>Allocated</th>
                                <th>Used</th>
                                <th>Remaining</th>
                                <th>Expiry Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaveCredits as $credit)
                                <tr>
                                    <td>
                                        {{ $credit->employee->first_name }} {{ $credit->employee->last_name }}
                                        <small class="d-block text-muted">{{ $credit->employee->employee_id }}</small>
                                    </td>
                                    <td>{{ $credit->leaveType->name }}</td>
                                    <td>{{ number_format($credit->allocated_days, 2) }} days</td>
                                    <td>{{ number_format($credit->used_days, 2) }} days</td>
                                    <td>
                                        <span class="{{ $credit->remaining_days <= 2 ? 'text-danger font-weight-bold' : '' }}">
                                            {{ number_format($credit->remaining_days, 2) }} days
                                        </span>
                                    </td>
                                    <td>
                                        {{ $credit->expiry_date ? $credit->expiry_date->format('M d, Y') : 'No expiry' }}
                                    </td>
                                    <td>
                                        <a href="{{ route('leave-credits.edit', $credit) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('leave-credits.destroy', $credit) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this leave credit?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $leaveCredits->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No leave credits found.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-submit form on change
        document.getElementById('employee_id').addEventListener('change', function() {
            this.form.submit();
        });
        document.getElementById('leave_type_id').addEventListener('change', function() {
            this.form.submit();
        });
        document.getElementById('fiscal_year').addEventListener('change', function() {
            this.form.submit();
        });
    });
</script>
@endsection