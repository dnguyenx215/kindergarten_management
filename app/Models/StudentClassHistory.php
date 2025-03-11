<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentClassHistory extends Model
{
    protected $table = "student_class_history";
    protected $fillable = [
        'student_id', 'class_id', 'start_date', 'end_date'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function class()
    {
        return $this->belongsTo(Classroom::class);
    }
}
