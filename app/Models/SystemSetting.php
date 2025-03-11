<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_year',
        'tuition_fee',
        'school_info',
        'holidays',
    ];

    protected $casts = [
        'holidays' => 'array', // Chuyển đổi JSON thành mảng
    ];
}
