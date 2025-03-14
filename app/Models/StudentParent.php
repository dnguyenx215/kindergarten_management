<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentParent extends Model
{
    protected $fillable = [
        'student_id', 
        'user_id', 
        'relationship', 
        'full_name', 
        'phone', 
        'email', 
        'occupation', 
        'address', 
        'is_primary_contact', 
        'can_pickup_student'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}