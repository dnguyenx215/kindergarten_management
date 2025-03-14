<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title', 
        'content', 
        'type', 
        'target', 
        'start_date', 
        'end_date', 
        'is_published', 
        'created_by'
    ];

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}