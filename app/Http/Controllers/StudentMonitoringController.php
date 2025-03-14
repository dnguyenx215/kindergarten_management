<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Attendance;
use App\Models\HealthMonitoring;
use App\Models\StudentAbsence;
use App\Models\DailyReport;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Notification;
use App\Models\StudentParent;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StudentMonitoringController extends Controller
{
    /**
     * Theo dõi sức khỏe học sinh
     */
    public function monitorHealth(Request $request)
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);
        
        // Chỉ giáo viên hoặc admin mới được phép
        if (!$user || (strtolower($user->role) !== 'teacher' && strtolower($user->role) !== 'admin')) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'date' => 'required|date',
            'height' => 'nullable|numeric|min:0|max:250',
            'weight' => 'nullable|numeric|min:0|max:300',
            'health_note' => 'nullable|string',
            'is_sick' => 'nullable|boolean',
            'sickness_description' => 'nullable|string|required_if:is_sick,true'
        ]);

        // Tạo hoặc cập nhật theo dõi sức khỏe
        $healthMonitoring = HealthMonitoring::updateOrCreate(
            [
                'student_id' => $validated['student_id'],
                'date' => $validated['date']
            ],
            [
                'height' => $validated['height'] ?? null,
                'weight' => $validated['weight'] ?? null,
                'health_note' => $validated['health_note'] ?? null,
                'is_sick' => $validated['is_sick'] ?? false,
                'sickness_description' => $validated['sickness_description'] ?? null,
                'recorded_by' => $userId
            ]
        );

        return response()->json(['data' => $healthMonitoring], 200);
    }

    /**
     * Báo cáo nghỉ học
     */
    public function reportAbsence(Request $request)
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);
        
        // Chỉ giáo viên hoặc admin mới được phép
        if (!$user || (strtolower($user->role) !== 'teacher' && strtolower($user->role) !== 'admin')) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'note' => 'nullable|string'
        ]);

        // Tạo đơn nghỉ học
        $absence = StudentAbsence::create([
            'student_id' => $validated['student_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'note' => $validated['note'] ?? null,
            'status' => 'pending', // Mặc định là chờ xác nhận
            'approved_by' => null,
            'approved_at' => null
        ]);

        return response()->json(['data' => $absence], 201);
    }

    /**
     * Gửi tin nhắn hỏi thăm
     */
    public function sendHealthCheck(Request $request)
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);
        
        // Chỉ giáo viên hoặc admin mới được phép
        if (!$user || (strtolower($user->role) !== 'teacher' && strtolower($user->role) !== 'admin')) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'message' => 'required|string|max:1000'
        ]);

        // Lấy thông tin học sinh
        $student = Student::findOrFail($validated['student_id']);

        // TODO: Tích hợp gửi thông báo qua email/SMS cho phụ huynh
        // Hiện tại chỉ lưu vào hệ thống thông báo
        $notification = Notification::create([
            'title' => 'Hỏi thăm sức khỏe',
            'message' => $validated['message'],
            'sender_id' => $userId,
            'status' => 'pending'
        ]);

        // Gán người nhận (phụ huynh của học sinh)
        $studentParents = StudentParent::where('student_id', $student->id)->get();
        $receiverIds = $studentParents->pluck('user_id')->filter();
        
        if ($receiverIds->isNotEmpty()) {
            $notification->receivers()->attach($receiverIds);
        }

        return response()->json([
            'message' => 'Đã gửi tin nhắn hỏi thăm thành công',
            'data' => $notification
        ], 200);
    }

    /**
     * Lấy báo cáo điểm danh
     */
    public function getAttendanceReport(Request $request)
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);
        
        // Chỉ giáo viên hoặc admin mới được phép
        if (!$user || (strtolower($user->role) !== 'teacher' && strtolower($user->role) !== 'admin')) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        // Lấy báo cáo điểm danh trong khoảng thời gian
        $attendanceReport = Attendance::where('class_id', $validated['class_id'])
            ->whereBetween('date', [$validated['start_date'], $validated['end_date']])
            ->selectRaw('
                date, 
                status, 
                COUNT(*) as total_students,
                GROUP_CONCAT(student_id) as student_ids
            ')
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get();

        return response()->json([
            'data' => $attendanceReport,
            'metadata' => [
                'class_id' => $validated['class_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date']
            ]
        ], 200);
    }

    /**
     * Ghi nhận báo cáo hàng ngày về học sinh
     */
    public function createDailyReport(Request $request)
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);
        
        // Chỉ giáo viên mới được phép
        if (!$user || strtolower($user->role) !== 'teacher') {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'report_date' => 'required|date',
            'activities' => 'nullable|string',
            'meals' => 'nullable|string',
            'nap' => 'nullable|string',
            'mood' => 'nullable|string',
            'health_notes' => 'nullable|string',
            'teacher_notes' => 'nullable|string'
        ]);

        // Tạo hoặc cập nhật báo cáo ngày
        $dailyReport = DailyReport::updateOrCreate(
            [
                'student_id' => $validated['student_id'],
                'report_date' => $validated['report_date']
            ],
            [
                'activities' => $validated['activities'] ?? null,
                'meals' => $validated['meals'] ?? null,
                'nap' => $validated['nap'] ?? null,
                'mood' => $validated['mood'] ?? null,
                'health_notes' => $validated['health_notes'] ?? null,
                'teacher_notes' => $validated['teacher_notes'] ?? null,
                'created_by' => $userId
            ]
        );

        return response()->json(['data' => $dailyReport], 200);
    }
}