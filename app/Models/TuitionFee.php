<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TuitionFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'amount',
        'due_date',
        'paid',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid'     => 'boolean',
    ];

    /**
     * Quan hệ với bảng Students (học sinh).
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Lấy danh sách học phí theo tháng hoặc quý.
     */
    public static function reportByPeriod($period, $year)
    {
        $query = self::whereYear('due_date', $year);

        if ($period === 'month') {
            return $query->selectRaw('MONTH(due_date) as period, SUM(amount) as total, COUNT(*) as count')
                ->groupBy('period')
                ->orderBy('period')
                ->get();
        }

        if ($period === 'quarter') {
            return $query->selectRaw('QUARTER(due_date) as period, SUM(amount) as total, COUNT(*) as count')
                ->groupBy('period')
                ->orderBy('period')
                ->get();
        }

        return collect(); // Trả về collection rỗng nếu period không hợp lệ
    }
}
