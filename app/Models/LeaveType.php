<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'is_paid',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the leave credits for this leave type.
     */
    public function leaveCredits()
    {
        return $this->hasMany(LeaveCredit::class);
    }

    /**
     * Get the leaves for this leave type.
     */
    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }
}