@extends('layouts.app')

@section('title', 'Request Leave')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .credit-badge {
        font-size: 0.85rem;
        padding: 0.35rem 0.5rem;
        margin-bottom: 0.5rem;
        display: inline-block;
    }
    .remaining-days {
        font-weight: bold;
        font-size: 1.1rem;
    }
    .low-balance {
        color: #e74a3b;
    }
    .date-range-info {
        background-color: #f8f9fc;
        border-radius: 0.35rem;
        padding: 1rem;
        margin-top: 1rem;
        display: none;
    }
    .split-payment {
        background-color: #fffaf0;
        border-left: 4px solid #f6c23e;
        border-radius: 0.35rem;
        padding: 1rem;
        margin-top: 1rem;
        display: none;
    }
    .credit-usage-breakdown {
        margin-top: 1rem;
    }
    .credit-usage-item {
        padding: 0.75rem;
        border-radius: 0.35rem;
        margin-bottom: 0.5rem;
    }
    .with-pay {
        background-color: #e8f5e9;
    }
    .without-pay {
        background-color: #fff3e0;
    }
    .credit-adjust-btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    .slider-container {
        padding: 0 1rem;
    }
    .custom-range {
        width: 100%;
    }
    .slider-labels {
        display: flex;
        justify-content: space-between;
        margin-top: 0.25rem;
        font-size: 0.8rem;
        color: #666;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Request Leave</h1>
        <a href="{{ route('leaves.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Leaves
        </a>
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

    <div class="row">
        <!-- Leave Credits Summary -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Your Leave Credits</h6>
                </div>
                <div class="card-body">
                    @if(count($leaveCredits) > 0)
                        <div class="credits-container">
                            @foreach($leaveCredits as $credit)
                                <div class="credit-item mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="font-weight-bold">{{ $credit->leaveType->name }}</span>
                                        <span class="remaining-days {{ $credit->remaining_days <= 2 ? 'low-balance' : '' }}">
                                            {{ number_format($credit->remaining_days, 2) }} days
                                        </span>
                                    </div>
                                    <div class="progress mt-1" style="height: 10px;">
                                        @php
                                            $percentUsed = ($credit->used_days / $credit->allocated_days) * 100;
                                            $remainingPercent = 100 - $percentUsed;
                                        @endphp
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $remainingPercent }}%;" 
                                            aria-valuenow="{{ $remainingPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted">Used: {{ number_format($credit->used_days, 2) }}</small>
                                        <small class="text-muted">Total: {{ number_format($credit->allocated_days, 2) }}</small>
                                    </div>
                                    
                                    @if($credit->expiry_date)
                                        <small class="text-danger d-block mt-1">
                                            Expires: {{ $credit->expiry_date->format('M d, Y') }}
                                        </small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i> You don't have any leave credits allocated for the current year.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Leave Request Form -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Leave Request Form</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('leaves.store') }}" method="POST" id="leaveForm">
                        @csrf
                        
                        @if(auth()->user()->isAdmin() || auth()->user()->isHR())
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
                        @else
                            <input type="hidden" name="employee_id" value="{{ auth()->id() }}">
                        @endif
                        
                        <div class="form-group">
                            <label for="leave_type_id">Leave Type <span class="text-danger">*</span></label>
                            <select class="form-control" id="leave_type_id" name="leave_type_id" required>
                                <option value="">-- Select Leave Type --</option>
                                @foreach($leaveTypes as $leaveType)
                                    <option value="{{ $leaveType->id }}" 
                                        data-is-paid="{{ $leaveType->is_paid ? 'true' : 'false' }}"
                                        {{ old('leave_type_id') == $leaveType->id ? 'selected' : '' }}>
                                        {{ $leaveType->name }} {{ $leaveType->is_paid ? '(Paid)' : '(Unpaid)' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control datepicker" id="start_date" name="start_date" placeholder="Select start date" value="{{ old('start_date') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">End Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control datepicker" id="end_date" name="end_date" placeholder="Select end date" value="{{ old('end_date') }}" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="date-range-info" id="dateRangeInfo">
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="mb-0">
                                    <i class="far fa-calendar-alt"></i> <span id="totalDays">0</span> day(s)
                                </p>
                                <div>
                                    <span class="badge bg-info text-white" id="availableCreditBadge">
                                        Available Credit: <span id="availableCredit">0</span> days
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="split-payment" id="splitPaymentSection">
                            <h6 class="font-weight-bold text-warning mb-3">
                                <i class="fas fa-exclamation-triangle"></i> Split Payment Required
                            </h6>
                            
                            <p>Your leave credit is insufficient for the entire leave period. You can adjust how many days to take with pay and without pay.</p>
                            
                            <div class="slider-container mb-3 mt-4">
                                <input type="range" class="custom-range" id="splitPaymentSlider" min="0" max="100" value="0">
                                <div class="slider-labels">
                                    <span>All Without Pay</span>
                                    <span>Mixed</span>
                                    <span>All With Pay</span>
                                </div>
                            </div>
                            
                            <div class="credit-usage-breakdown">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="credit-usage-item with-pay">
                                            <div class="d-flex justify-content-between">
                                                <span>With Pay:</span>
                                                <strong><span id="withPayDays">0</span> day(s)</strong>
                                            </div>
                                            <input type="hidden" name="with_pay_days" id="withPayDaysInput" value="0">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="credit-usage-item without-pay">
                                            <div class="d-flex justify-content-between">
                                                <span>Without Pay:</span>
                                                <strong><span id="withoutPayDays">0</span> day(s)</strong>
                                            </div>
                                            <input type="hidden" name="without_pay_days" id="withoutPayDaysInput" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mt-4">
                            <label for="reason">Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" required>{{ old('reason') }}</textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane mr-1"></i> Submit Request
                        </button>
                        <a href="{{ route('leaves.index') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </a>
                    </form>
                </div>
            </div>
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
            minDate: "today",
            allowInput: true
        });
        
        // Store leave credits data
        const leaveCredits = {
            @foreach($leaveCredits as $credit)
                "{{ $credit->leave_type_id }}": {
                    remaining: {{ $credit->remaining_days }},
                    allocated: {{ $credit->allocated_days }},
                    used: {{ $credit->used_days }},
                    leaveType: "{{ $credit->leaveType->name }}"
                },
            @endforeach
        };
        
        // DOM elements
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const leaveTypeSelect = document.getElementById('leave_type_id');
        const dateRangeInfo = document.getElementById('dateRangeInfo');
        const totalDaysSpan = document.getElementById('totalDays');
        const availableCreditSpan = document.getElementById('availableCredit');
        const availableCreditBadge = document.getElementById('availableCreditBadge');
        const splitPaymentSection = document.getElementById('splitPaymentSection');
        const splitPaymentSlider = document.getElementById('splitPaymentSlider');
        const withPayDaysSpan = document.getElementById('withPayDays');
        const withoutPayDaysSpan = document.getElementById('withoutPayDays');
        const withPayDaysInput = document.getElementById('withPayDaysInput');
        const withoutPayDaysInput = document.getElementById('withoutPayDaysInput');
        
        let currentTotalDays = 0;
        let currentAvailableCredit = 0;
        let isPaidLeave = false;
        let requiresSplitPayment = false;
        let currentWithPayDays = 0;
        let currentWithoutPayDays = 0;
        
        function calculateDays() {
            if (startDateInput.value && endDateInput.value) {
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);
                
                // Check if dates are valid
                if (!isNaN(startDate.getTime()) && !isNaN(endDate.getTime())) {
                    // Calculate days difference
                    const timeDiff = endDate.getTime() - startDate.getTime();
                    const daysDiff = Math.floor(timeDiff / (1000 * 3600 * 24)) + 1; // Include both start and end days
                    
                    if (daysDiff > 0) {
                        currentTotalDays = daysDiff;
                        totalDaysSpan.textContent = daysDiff;
                        dateRangeInfo.style.display = 'block';
                        
                        // Check credit if a leave type is selected
                        checkLeaveCredits();
                    }
                }
            }
        }
        
        function checkLeaveCredits() {
            // Check if we have both dates and leave type
            if (!startDateInput.value || !endDateInput.value || !leaveTypeSelect.value) {
                return;
            }
            
            const selectedLeaveType = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
            isPaidLeave = selectedLeaveType.dataset.isPaid === 'true';
            
            // If not paid leave, hide credit info
            if (!isPaidLeave) {
                availableCreditBadge.style.display = 'none';
                splitPaymentSection.style.display = 'none';
                currentWithPayDays = 0;
                currentWithoutPayDays = currentTotalDays;
                updateHiddenInputs();
                return;
            }
            
            // Call API to check credits
            const employeeId = document.getElementById('employee_id')?.value || '{{ auth()->id() }}';
            const leaveTypeId = leaveTypeSelect.value;
            
            fetch(`/leaves/check-credits?employee_id=${employeeId}&leave_type_id=${leaveTypeId}&start_date=${startDateInput.value}&end_date=${endDateInput.value}`)
                .then(response => response.json())
                .then(data => {
                    if (data.credit_details) {
                        const details = data.credit_details;
                        currentAvailableCredit = parseFloat(details.available_credit);
                        availableCreditSpan.textContent = currentAvailableCredit.toFixed(2);
                        availableCreditBadge.style.display = 'inline-block';
                        
                        // Check if we need split payment
                        requiresSplitPayment = details.requires_split_payment;
                        
                        if (requiresSplitPayment || details.available_credit < details.total_days) {
                            // Show split payment options
                            splitPaymentSection.style.display = 'block';
                            
                            // Set initial values
                            currentWithPayDays = parseFloat(details.with_pay_days);
                            currentWithoutPayDays = parseFloat(details.without_pay_days);
                            
                            // Update slider value
                            const sliderValue = (currentWithPayDays / currentTotalDays) * 100;
                            splitPaymentSlider.value = sliderValue;
                            
                            // Update displays
                            updateSplitPaymentDisplay();
                        } else if (details.is_without_pay) {
                            // All without pay
                            splitPaymentSection.style.display = 'block';
                            currentWithPayDays = 0;
                            currentWithoutPayDays = currentTotalDays;
                            splitPaymentSlider.value = 0;
                            updateSplitPaymentDisplay();
                        } else {
                            // All with pay, no split needed
                            splitPaymentSection.style.display = 'none';
                            currentWithPayDays = currentTotalDays;
                            currentWithoutPayDays = 0;
                            updateHiddenInputs();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error checking leave credits:', error);
                    splitPaymentSection.style.display = 'none';
                });
        }
        
        // Update split payment display
        function updateSplitPaymentDisplay() {
            withPayDaysSpan.textContent = currentWithPayDays.toFixed(2);
            withoutPayDaysSpan.textContent = currentWithoutPayDays.toFixed(2);
            updateHiddenInputs();
        }
        
        // Update hidden inputs
        function updateHiddenInputs() {
            withPayDaysInput.value = currentWithPayDays.toFixed(2);
            withoutPayDaysInput.value = currentWithoutPayDays.toFixed(2);
        }
        
        // Handle slider changes
        splitPaymentSlider.addEventListener('input', function() {
            const sliderValue = parseInt(this.value);
            const maxAllowedWithPay = Math.min(currentAvailableCredit, currentTotalDays);
            
            // Calculate with pay days based on slider value
            currentWithPayDays = parseFloat(((sliderValue / 100) * maxAllowedWithPay).toFixed(2));
            currentWithoutPayDays = parseFloat((currentTotalDays - currentWithPayDays).toFixed(2));
            
            updateSplitPaymentDisplay();
        });
        
        // Event listeners
        startDateInput.addEventListener('change', calculateDays);
        endDateInput.addEventListener('change', calculateDays);
        leaveTypeSelect.addEventListener('change', calculateDays);
        
        // For admin/HR - fetch leave credits when employee changes
        const employeeSelect = document.getElementById('employee_id');
        if (employeeSelect) {
            employeeSelect.addEventListener('change', function() {
                if (this.value) {
                    fetch(`/leaves/check-credits?employee_id=${this.value}`)
                        .then(response => response.json())
                        .then(data => {
                            // Update the leave credits data
                            Object.keys(leaveCredits).forEach(key => delete leaveCredits[key]);
                            
                            if (data.leave_credits && data.leave_credits.length > 0) {
                                data.leave_credits.forEach(credit => {
                                    leaveCredits[credit.leave_type_id] = {
                                        remaining: credit.remaining_days,
                                        allocated: credit.allocated_days,
                                        used: credit.used_days,
                                        leaveType: credit.leave_type.name
                                    };
                                });
                            }
                            
                            // Recalculate days to update info
                            calculateDays();
                        })
                        .catch(error => console.error('Error fetching leave credits:', error));
                }
            });
        }
    });
</script>
@endsection