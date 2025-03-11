<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'title',
        'message',
        'sender_id',
        'schedule_at',
        'sent_at',
        'status'
    ];

    public function receivers()
    {
        return $this->belongsToMany(\App\Models\User::class, 'notification_receivers', 'notification_id', 'receiver_id')
                    ->withPivot('read', 'read_at')
                    ->withTimestamps();
    }
}
