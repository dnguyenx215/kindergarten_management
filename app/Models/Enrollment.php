<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'full_name', 
        'birthday', 
        'parent_phone', 
        'parent_email', 
        'student_code', 
        'status', 
        'approved_at'
    ];

    // Thuộc tính đặc biệt cho ngày tháng
    protected $dates = [
        'birthday', 
        'approved_at'
    ];

    // Các trạng thái có thể của enrollment
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    // Kiểm tra trạng thái
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }
}