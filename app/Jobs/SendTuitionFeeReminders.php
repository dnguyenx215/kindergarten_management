<?php

namespace App\Jobs;

use App\Models\TuitionFee;
use App\Models\Student;
use App\Notifications\TuitionFeeReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendTuitionFeeReminders implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Xác định thời gian nhắc nhở trước ngày đến hạn (3 ngày trước)
        $dueThreshold = Carbon::now()->addDays(3);

        // Lấy danh sách học phí chưa thanh toán và có ngày đến hạn trong vòng 3 ngày tới
        $fees = TuitionFee::where('paid', false)
            ->whereDate('due_date', '<=', $dueThreshold)
            ->get();

        if ($fees->isEmpty()) {
            Log::info('Không có học phí nào cần nhắc nhở tại thời điểm này.');
            return;
        }

        foreach ($fees as $fee) {
            // Lấy thông tin học sinh
            $student = $fee->student;

            if (!$student) {
                Log::warning("Không tìm thấy học sinh cho học phí ID: {$fee->id}");
                continue;
            }

            // Kiểm tra nếu học sinh có email hoặc số điện thoại để nhận thông báo
            if (!$student->email && !$student->phone) {
                Log::warning("Học sinh {$student->id} không có email hoặc số điện thoại để nhận thông báo.");
                continue;
            }

            // Gửi thông báo nhắc nhở học phí qua email và SMS
            $student->notify(new TuitionFeeReminder($fee));

            Log::info("Đã gửi nhắc nhở học phí cho học sinh ID: {$student->id}, Email: {$student->email}");
        }
    }
}
