<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'description',
        'is_active',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the employees assigned to this shift.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}