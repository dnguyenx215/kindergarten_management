<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnrollmentFee extends Model
{
    protected $fillable = [
        'student_id', 'amount', 'status'
    ];

    public function student()
    {
        return $this->belongsTo(\App\Models\Student::class);
    }
}
