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

    /**
     * Quan hệ với users (nhiều-nhiều).
     * Một role có thể thuộc về nhiều users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    /**
     * Quan hệ với permissions (nhiều-nhiều).
     * Một role có thể có nhiều permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }
}