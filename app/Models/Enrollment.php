<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $table = "enrollments";
    protected $fillable = [
        'full_name', 'birthday', 'parent_phone', 'parent_email', 'status'
    ];
}
