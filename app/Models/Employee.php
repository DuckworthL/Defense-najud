<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'role_id',
        'department_id',
        'shift_id',
        'phone',
        'address',
        'profile_picture',
        'date_hired',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_hired' => 'date',
    ];

    /**
     * Get the department that the employee belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the role that the employee has.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the shift that the employee is assigned to.
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get all attendances for the employee.
     */
    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get all attendances for the employee (alias for attendance()).
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get all leaves for the employee.
     */
    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    /**
     * Get the employee's full name.
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Check if the employee is an admin.
     * 
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role_id === 1; // Assuming role_id 1 is Admin
    }

    /**
     * Check if the employee is an HR.
     * 
     * @return bool
     */
    public function isHR()
    {
        return $this->role_id === 2; // Assuming role_id 2 is HR
    }

    /**
     * Get the leave credits for the employee.
     */
    public function leaveCredits()
    {
        return $this->hasMany(LeaveCredit::class);
    }
}