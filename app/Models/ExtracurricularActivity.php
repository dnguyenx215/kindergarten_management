<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtracurricularActivity extends Model
{
    protected $fillable = ['name', 'description', 'date', 'class_id'];

    // Quan hệ với lớp học (Classroom)
    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }
}
