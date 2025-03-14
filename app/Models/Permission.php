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

    /**
     * Quan hệ với roles (nhiều-nhiều).
     * Một permission có thể thuộc về nhiều roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }
}