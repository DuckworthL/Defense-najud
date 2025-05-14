<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    
    protected $table = 'attendance';

    protected $fillable = [
        'employee_id',
        'attendance_status_id', // This is the field name in your database
        'date',
        'clock_in_time',
        'clock_out_time',
        'is_clock_in_reset',
        'clock_in_reset_by',
        'clock_in_reset_reason',
        'is_clock_out_reset',
        'clock_out_reset_by',
        'clock_out_reset_reason',
        'verified_by',
        'verification_time',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in_time' => 'datetime',
        'clock_out_time' => 'datetime',
        'verification_time' => 'datetime',
        'is_clock_in_reset' => 'boolean',
        'is_clock_out_reset' => 'boolean',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendanceStatus()
    {
        return $this->belongsTo(AttendanceStatus::class, 'attendance_status_id'); // Fix the foreign key
    }

    public function clockInResetBy()
    {
        return $this->belongsTo(Employee::class, 'clock_in_reset_by');
    }

    public function clockOutResetBy()
    {
        return $this->belongsTo(Employee::class, 'clock_out_reset_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(Employee::class, 'verified_by');
    }
    
    // Calculate work hours
    public function getWorkHoursAttribute()
    {
        if ($this->clock_in_time && $this->clock_out_time) {
            return $this->clock_in_time->diffInHours($this->clock_out_time);
        }
        
        return null;
    }
}