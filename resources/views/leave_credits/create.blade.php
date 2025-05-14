@extends('layouts.app')

@section('title', 'Allocate Leave Credit')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Allocate Leave Credit</h1>
        <a href="{{ route('leave-credits.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Leave Credits
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Leave Credit Details</h6>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form action="{{ route('leave-credits.store') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="employee_id">Employee <span class="text-danger">*</span></label>
                    <select class="form-control" id="employee_id" name="employee_id" required>
                        <option value="">-- Select Employee --</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_id }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="leave_type_id">Leave Type <span class="text-danger">*</span></label>
                    <select class="form-control" id="leave_type_id" name="leave_type_id" required>
                        <option value="">-- Select Leave Type --</option>
                        @foreach($leaveTypes as $leaveType)
                            <option value="{{ $leaveType->id }}" {{ old('leave_type_id') == $leaveType->id ? 'selected' : '' }}>
                                {{ $leaveType->name }} {{ $leaveType->is_paid ? '(Paid)' : '(Unpaid)' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="fiscal_year">Fiscal Year <span class="text-danger">*</span></label>
                    <select class="form-control" id="fiscal_year" name="fiscal_year" required>
                        @php
                            $currentYear = date('Y');
                            $startYear = $currentYear - 1;
                            $endYear = $currentYear + 2;
                        @endphp
                        @for($year = $startYear; $year <= $endYear; $year++)
                            <option value="{{ $year }}" {{ old('fiscal_year', $defaultYear) == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="allocated_days">Allocated Days <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="allocated_days" name="allocated_days" value="{{ old('allocated_days') }}" step="0.01" min="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="expiry_date">Expiry Date</label>
                    <input type="date" class="form-control" id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}">
                    <small class="text-muted">Leave blank if there is no expiry date.</small>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Allocate Credit
                </button>
                <a href="{{ route('leave-credits.index') }}" class="btn btn-secondary ml-2">
                    <i class="fas fa-times mr-1"></i> Cancel
                </a>
            </form>
        </div>
    </div>
</div>
@endsection