<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Lấy danh sách điểm danh cho một ngày (không cần kiểm tra quyền).
     */
    public function index(Request $request)
    {
        // Lấy ngày điểm danh, mặc định là hôm nay
        $date = $request->query('date', Carbon::today()->toDateString());
        $attendances = Attendance::where('date', $date)->get();

        return response()->json(['data' => $attendances], 200);
    }

    /**
     * Ghi nhận điểm danh cho một học sinh trong ngày (chỉ cho phép giáo viên).
     */
    public function store(Request $request)
    {
        // Kiểm tra quyền từ user_id được gửi qua request
        $userId = $request->input('user_id');
        $user = User::find($userId);
        if (!$user || strtolower($user->role) !== 'teacher') {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        // Xác thực dữ liệu đầu vào
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'present'    => 'required|boolean',
        ]);

        // Sử dụng ngày hiện tại làm ngày điểm danh
        $attendance_date = Carbon::today()->toDateString();

        // Kiểm tra nếu học sinh đã được điểm danh trong ngày hôm nay
        $existing = Attendance::where('student_id', $data['student_id'])
            ->where('date', $attendance_date)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Học sinh này đã được điểm danh hôm nay.'
            ], 422);
        }

        // Tạo bản ghi điểm danh
        $attendance = Attendance::create([
            'student_id' => $data['student_id'],
            'date'       => $attendance_date,
            'present'    => $data['present'],
        ]);

        return response()->json(['data' => $attendance], 201);
    }
}
