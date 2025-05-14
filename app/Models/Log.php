<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
    ];
    
    // Disable updated_at column
    const UPDATED_AT = null;

    // Relationship
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}