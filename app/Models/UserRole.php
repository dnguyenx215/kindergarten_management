<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserRole extends Pivot
{
    protected $table = 'user_roles';

    protected $fillable = [
        'user_id',
        'role_id'
    ];

    // Pivot model cho quan hệ nhiều-nhiều giữa User và Role
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}