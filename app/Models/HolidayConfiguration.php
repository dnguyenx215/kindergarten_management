<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HolidayConfiguration extends Model
{
    protected $fillable = [
        'holiday_date', 
        'holiday_name', 
        'holiday_type', 
        'description', 
        'created_by'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}