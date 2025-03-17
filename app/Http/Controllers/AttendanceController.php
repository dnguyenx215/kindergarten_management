<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Lấy danh sách điểm danh cho một ngày và lớp.
     */
    public function index(Request $request)
    {
        $date = $request->query('date', Carbon::today()->toDateString());
        $classId = $request->query('class_id');

        $query = Attendance::where('date', $date);

        if ($classId) {
            // Nếu có class_id, lọc học sinh theo lớp
            $studentIds = Student::where('class_id', $classId)->pluck('id');
            $query->whereIn('student_id', $studentIds);
        }

        $attendances = $query->get();

        return response()->json(['data' => $attendances], 200);
    }

    /**
     * Ghi nhận hoặc cập nhật điểm danh.
     */
    /**
     * Ghi nhận hoặc cập nhật điểm danh.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent_excused,absent_unexcused',
            'absence_reason' => 'nullable|string',
        ]);

        // Tìm điểm danh hiện có
        $attendance = Attendance::where('student_id', $data['student_id'])
            ->where('date', $data['date'])
            ->first();

        if ($attendance) {
            // Cập nhật điểm danh hiện có
            $attendance->update([
                'status' => $data['status'],
                'absence_reason' => $data['absence_reason'],
            ]);
        } else {
            // Tạo mới điểm danh
            $attendance = Attendance::create($data);
        }

        // Biến đổi bản ghi để chuyển is_locked từ integer sang boolean 
        // trong response JSON
        $response = $attendance->toArray();
        $response['is_locked'] = (bool) $attendance->is_locked;

        return response()->json(['data' => $response], 200);
    }

    /**
     * Khóa điểm danh của một ngày cho lớp.
     */
    public function lockAttendance(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'class_id' => 'required|exists:classes,id',
            'is_locked' => 'required|boolean',
        ]);

        // Lấy danh sách học sinh trong lớp
        $studentIds = Student::where('class_id', $data['class_id'])->pluck('id');

        // Cập nhật trạng thái khóa cho tất cả điểm danh của lớp trong ngày
        Attendance::where('date', $data['date'])
            ->whereIn('student_id', $studentIds)
            ->update(['is_locked' => $data['is_locked']]);

        return response()->json(['message' => 'Đã khóa điểm danh thành công'], 200);
    }
}