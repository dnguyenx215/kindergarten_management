<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',

    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function homeroomClasses()
    {
        return $this->hasMany(Classroom::class, 'homeroom_teacher_id');
    }

    public function notifications()
    {
        return $this->belongsToMany(Notification::class, 'notification_receivers', 'receiver_id', 'notification_id')
            ->withPivot('read', 'read_at')
            ->withTimestamps();
    }


}
