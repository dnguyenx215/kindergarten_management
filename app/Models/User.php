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
        'roles',
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

    /**
     * Quan hệ với roles (nhiều-nhiều).
     * Một user có thể có nhiều roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withTimestamps();
    }

    /**
     * Kiểm tra xem user có role nào đó không.
     *
     * @param string|array $roles Tên role hoặc mảng tên role cần kiểm tra
     * @return bool
     */
    public function hasRole($roles)
    {
        $roles = is_array($roles) ? $roles : [$roles];
        
        // Kiểm tra qua trường role trực tiếp (backward compatibility)
        if (!empty($this->role) && in_array($this->role, $roles)) {
            return true;
        }
        
        // Kiểm tra qua bảng quan hệ roles
        foreach ($this->roles as $role) {
            if (in_array($role->name, $roles)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Kiểm tra xem user có permission nào đó không
     * (thông qua các roles của user).
     *
     * @param string|array $permissions Tên permission hoặc mảng tên permission
     * @return bool
     */
    public function hasPermission($permissions)
    {
        $permissions = is_array($permissions) ? $permissions : [$permissions];
        
        foreach ($this->roles as $role) {
            foreach ($role->permissions as $permission) {
                if (in_array($permission->name, $permissions)) {
                    return true;
                }
            }
        }
        
        return false;
    }
}