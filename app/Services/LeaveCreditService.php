<?php

namespace App\Services;

use App\Models\Leave;
use App\Models\LeaveCredit;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LeaveCreditService
{
    /**
     * Calculate leave credit usage and split payment details
     *
     * @param int $employeeId
     * @param int $leaveTypeId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function calculateLeaveCredits($employeeId, $leaveTypeId, $startDate, $endDate)
    {
        // Parse dates
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // Calculate total days
        $totalDays = $this->calculateDaysBetween($start, $end);
        
        // Check if this is a paid leave type
        $leaveType = LeaveType::find($leaveTypeId);
        if (!$leaveType || !$leaveType->is_paid) {
            return [
                'total_days' => $totalDays,
                'with_pay_days' => 0,
                'without_pay_days' => $totalDays,
                'is_without_pay' => true,
                'available_credit' => 0,
                'requires_split_payment' => false,
            ];
        }
        
        // Get the available leave credit
        $leaveCredit = LeaveCredit::where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('fiscal_year', Carbon::now()->year)
            ->first();
            
        if (!$leaveCredit) {
            return [
                'total_days' => $totalDays,
                'with_pay_days' => 0,
                'without_pay_days' => $totalDays,
                'is_without_pay' => true,
                'available_credit' => 0,
                'requires_split_payment' => false,
            ];
        }
        
        // Calculate available days (formatted to 2 decimal places for consistency)
        $availableCredit = round($leaveCredit->allocated_days - $leaveCredit->used_days, 2);
        
        // If we have enough credits, all days are with pay
        if ($availableCredit >= $totalDays) {
            return [
                'total_days' => $totalDays,
                'with_pay_days' => $totalDays,
                'without_pay_days' => 0,
                'is_without_pay' => false,
                'available_credit' => $availableCredit,
                'requires_split_payment' => false,
            ];
        }
        
        // If we have some credits but not enough, split the payment
        $withPayDays = $availableCredit > 0 ? $availableCredit : 0;
        $withoutPayDays = $totalDays - $withPayDays;
        
        return [
            'total_days' => $totalDays,
            'with_pay_days' => round($withPayDays, 2),
            'without_pay_days' => round($withoutPayDays, 2),
            'is_without_pay' => $withoutPayDays > 0,
            'available_credit' => $availableCredit,
            'requires_split_payment' => $withPayDays > 0 && $withoutPayDays > 0,
        ];
    }
    
    /**
     * Calculate days between two dates including both start and end date
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return float
     */
    private function calculateDaysBetween(Carbon $startDate, Carbon $endDate)
    {
        // Ensure proper date format to avoid time-related calculation issues
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->startOfDay();
        
        // Calculate days difference including both start and end days
        return $start->diffInDays($end) + 1;
    }
    
    /**
     * Process leave credits when a leave is approved
     *
     * @param Leave $leave
     * @return bool
     */
    public function processLeaveCredits(Leave $leave)
    {
        // Only process if it's a paid leave type
        if (!$leave->leaveType || !$leave->leaveType->is_paid) {
            return true;
        }
        
        try {
            // Calculate the number of days
            $totalDays = $this->calculateDaysBetween(
                Carbon::parse($leave->start_date),
                Carbon::parse($leave->end_date)
            );
            
            // Get the leave credit record
            $leaveCredit = LeaveCredit::where('employee_id', $leave->employee_id)
                ->where('leave_type_id', $leave->leave_type_id)
                ->where('fiscal_year', Carbon::parse($leave->start_date)->year)
                ->first();
                
            if (!$leaveCredit) {
                // No credit record found, can't deduct
                Log::warning("No leave credit record found for leave {$leave->id}");
                return false;
            }
            
            // Get the with_pay days
            $withPayDays = $leave->with_pay_days ?? 0;
            
            // Ensure we don't deduct more than available
            $availableCredit = round($leaveCredit->allocated_days - $leaveCredit->used_days, 2);
            $daysToDeduct = min($withPayDays, $availableCredit);
            
            // Update the used days
            $leaveCredit->used_days = round($leaveCredit->used_days + $daysToDeduct, 2);
            $leaveCredit->save();
            
            return true;
        } catch (\Exception $e) {
            Log::error("Error processing leave credits: " . $e->getMessage());
            return false;
        }
    }
}