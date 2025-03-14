<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeBlock extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description'
    ];

    // Một khối có nhiều lớp
    public function classes()
    {
        return $this->hasMany(Classroom::class, 'grade_block_id');
    }
}