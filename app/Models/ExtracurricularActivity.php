<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtracurricularActivity extends Model
{
    protected $fillable = [
        'name', 
        'description', 
        'date', 
        'class_id'
    ];

    // Một hoạt động ngoại khóa thuộc về một lớp
    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }
}