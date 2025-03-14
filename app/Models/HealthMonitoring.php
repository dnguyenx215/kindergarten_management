<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthMonitoring extends Model
{
    protected $fillable = [
        'student_id', 
        'date', 
        'height', 
        'weight', 
        'health_note', 
        'is_sick', 
        'sickness_description', 
        'recorded_by'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function recordedByUser()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}