<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color_code',
        'is_active',
    ];

    // Relationship with attendance
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'attendance_status_id'); // Fix the foreign key
    }
}