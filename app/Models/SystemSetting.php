<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'school_year',
        'tuition_fee',
        'school_info',
        'holidays',
        'school_name',
        'school_address',
        'school_phone',
        'school_email',
        'principal_name',
        'school_logo',
        'working_hours',
        'payment_methods',
        'notification_settings'
    ];

    // Chuyển đổi các trường JSON thành mảng
    protected $casts = [
        'holidays' => 'array',
        'working_hours' => 'array',
        'payment_methods' => 'array',
        'notification_settings' => 'array'
    ];
}