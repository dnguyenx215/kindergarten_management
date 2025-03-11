<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $fillable = ['name', 'role', 'phone', 'email'];

    public function tasks()
    {
        return $this->hasMany(StaffTask::class, 'staff_id');
    }
}
