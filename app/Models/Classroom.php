<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'name', 'capacity', 'homeroom_teacher_id', 'grade_block_id'
    ];

    // Một lớp có nhiều học sinh
    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    // Lấy thông tin giáo viên chủ nhiệm (homeroom teacher)
    public function homeroomTeacher()
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }

}
