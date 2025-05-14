@extends('layouts.app')

@section('title', 'Edit Leave Credit')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Leave Credit</h1>
        <a href="{{ route('leave-credits.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Leave Credits
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Leave Credit Details</h6>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Employee:</strong> {{ $leaveCredit->employee->first_name }} {{ $leaveCredit->employee->last_name }}</p>
                        <p><strong>Employee ID:</strong> {{ $leaveCredit->employee->employee_id }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Leave Type:</strong> {{ $leaveCredit->leaveType->name }}</p>
                        <p><strong>Fiscal Year:</strong> {{ $leaveCredit->fiscal_year }}</p>
                    </div>
                </div>
            </div>
            
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form action="{{ route('leave-credits.update', $leaveCredit) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="allocated_days">Allocated Days <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="allocated_days" name="allocated_days" 
                           value="{{ old('allocated_days', $leaveCredit->allocated_days) }}" 
                           step="0.01" min="{{ $leaveCredit->used_days }}" required>
                    <small class="text-muted">Note: You cannot allocate fewer days than the employee has already used ({{ $leaveCredit->used_days }} days).</small>
                </div>
                
                <div class="form-group">
                    <label for="used_days">Used Days <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="used_days" name="used_days" 
                           value="{{ old('used_days', $leaveCredit->used_days) }}" 
                           step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="remaining_days">Remaining Days</label>
                    <input type="number" class="form-control" id="remaining_days" 
                           value="{{ $leaveCredit->remaining_days }}" readonly>
                    <small class="text-muted">This is calculated automatically (Allocated Days - Used Days).</small>
                </div>
                
                <div class="form-group">
                    <label for="expiry_date">Expiry Date</label>
                    <input type="date" class="form-control" id="expiry_date" name="expiry_date" 
                           value="{{ old('expiry_date', $leaveCredit->expiry_date ? $leaveCredit->expiry_date->format('Y-m-d') : '') }}">
                    <small class="text-muted">Leave blank if there is no expiry date.</small>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Update Credit
                </button>
                <a href="{{ route('leave-credits.index') }}" class="btn btn-secondary ml-2">
                    <i class="fas fa-times mr-1"></i> Cancel
                </a>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-calculate remaining days
        const allocatedDaysInput = document.getElementById('allocated_days');
        const usedDaysInput = document.getElementById('used_days');
        const remainingDaysInput = document.getElementById('remaining_days');
        
        function updateRemainingDays() {
            const allocated = parseFloat(allocatedDaysInput.value) || 0;
            const used = parseFloat(usedDaysInput.value) || 0;
            const remaining = allocated - used;
            remainingDaysInput.value = remaining.toFixed(2);
            
            // Change color if remaining is low
            if (remaining <= 2) {
                remainingDaysInput.classList.add('text-danger');
            } else {
                remainingDaysInput.classList.remove('text-danger');
            }
        }
        
        allocatedDaysInput.addEventListener('input', updateRemainingDays);
        usedDaysInput.addEventListener('input', updateRemainingDays);
        
        // Set initial remaining days
        updateRemainingDays();
    });
</script>
@endsection