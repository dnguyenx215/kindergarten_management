<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name', 
        'display_name', 
        'description'
    ];

    // Một role có nhiều permissions
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    // Một role có nhiều users
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }
}