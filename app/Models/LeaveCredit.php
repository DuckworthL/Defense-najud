<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveCredit extends Model
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
        'fiscal_year',
        'allocated_days',
        'used_days',
        'remaining_days',
        'expiry_date',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fiscal_year' => 'integer',
        'allocated_days' => 'decimal:2',
        'used_days' => 'decimal:2',
        'remaining_days' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    /**
     * Get the employee that owns the leave credit.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the leave type that owns the leave credit.
     */
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Get the employee who created the leave credit.
     */
    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    /**
     * Get the employee who last updated the leave credit.
     */
    public function updater()
    {
        return $this->belongsTo(Employee::class, 'updated_by');
    }
}