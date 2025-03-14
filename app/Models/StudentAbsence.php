<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAbsence extends Model
{
    protected $fillable = [
        'student_id', 
        'start_date', 
        'end_date', 
        'reason', 
        'status', 
        'note', 
        'approved_by', 
        'approved_at'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}