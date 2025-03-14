<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentActivity extends Model
{
    protected $fillable = [
        'student_id', 
        'class_id', 
        'title', 
        'description', 
        'activity_date', 
        'activity_type', 
        'teacher_comment', 
        'recorded_by'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function recordedByUser()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}