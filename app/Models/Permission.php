<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'name', 
        'display_name', 
        'description'
    ];

    // Một permission thuộc về nhiều roles
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }
}