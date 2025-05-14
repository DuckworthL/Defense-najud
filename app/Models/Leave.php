<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Leave extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'reason',
        'status',
        'is_without_pay',
        'with_pay_days',
        'without_pay_days',
        'approved_by',
        'approved_at',
        'remarks',
        'user_agent'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'is_without_pay' => 'boolean',
        'with_pay_days' => 'decimal:2',
        'without_pay_days' => 'decimal:2',
    ];

    /**
     * Get the employee that owns the leave.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the leave type that owns the leave.
     */
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Get the employee who approved the leave.
     */
    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    /**
     * Calculate the number of days for this leave request.
     * 
     * @return float
     */
    public function getDaysCountAttribute()
    {
        if ($this->start_date && $this->end_date) {
            return Carbon::parse($this->start_date)->startOfDay()->diffInDays(Carbon::parse($this->end_date)->startOfDay()) + 1;
        }
        return 0;
    }
    
    /**
     * Determine if this leave requires split payment.
     * 
     * @return bool
     */
    public function getRequiresSplitPaymentAttribute()
    {
        return $this->with_pay_days > 0 && $this->without_pay_days > 0;
    }
    
    /**
     * Get the formatted percentage of days with pay.
     * 
     * @return string
     */
    public function getWithPayPercentageAttribute()
    {
        if ($this->days_count == 0) return '0%';
        return round(($this->with_pay_days / $this->days_count) * 100) . '%';
    }
}