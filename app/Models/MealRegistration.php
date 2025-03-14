<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealRegistration extends Model
{
    protected $fillable = [
        'class_id', 
        'date', 
        'breakfast_count', 
        'lunch_count', 
        'dinner_count', 
        'registered_by'
    ];

    public function class()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function registeredByUser()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}