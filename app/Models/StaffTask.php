<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffTask extends Model
{
    protected $fillable = [
        'staff_id', 
        'title', 
        'description', 
        'due_date'
    ];

    // Một nhiệm vụ thuộc về một nhân viên
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}