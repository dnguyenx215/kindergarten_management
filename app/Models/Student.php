<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Student extends Model
{
    use Notifiable;
    protected $fillable = [
        'student_code', 'first_name', 'last_name', 'birthday',
        'gender', 'address', 'parent_name', 'parent_phone',
        'parent_email', 'class_id'
    ];

    // Nếu lưu class_id ở bảng students
    public function class()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }
    

    // Nếu có bảng lịch sử lớp
    public function classHistories()
    {
        return $this->hasMany(StudentClassHistory::class);
    }
}
