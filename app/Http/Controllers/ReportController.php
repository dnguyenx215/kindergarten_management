<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\TuitionFee;
use App\Models\Student;
use App\Models\Classroom;
use Carbon\Carbon;

class ReportController extends Controller
{
    // Kiểm tra quyền admin trước khi thực hiện
    private function checkAdmin(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }
    }

    // Báo cáo điểm danh theo tháng
    public function attendanceReport(Request $request, $month, $year)
    {
        $this->checkAdmin($request);
        
        $attendances = Attendance::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();
        
        return response()->json(['data' => $attendances], 200);
    }

    // Báo cáo thu học phí theo tháng hoặc quý
    public function tuitionReport(Request $request, $period, $year)
    {
        $this->checkAdmin($request);

        $query = TuitionFee::whereYear('due_date', $year);
        
        if ($period === 'monthly') {
            $query->whereMonth('due_date', Carbon::now()->month);
        } elseif ($period === 'quarterly') {
            $query->whereBetween('due_date', [
                Carbon::now()->firstOfQuarter(),
                Carbon::now()->lastOfQuarter()
            ]);
        }
        
        $tuitionFees = $query->get();
        return response()->json(['data' => $tuitionFees], 200);
    }

    // Báo cáo học tập theo từng kỳ học
    public function academicReport(Request $request, $semester, $year)
    {
        $this->checkAdmin($request);
        
        $students = Student::where('academic_year', $year)
            ->where('semester', $semester)
            ->with('grades')
            ->get();
        
        return response()->json(['data' => $students], 200);
    }

    // Danh sách học sinh theo lớp, năm học
    public function studentListReport(Request $request, $classId, $year)
    {
        $this->checkAdmin($request);
        
        $students = Student::where('class_id', $classId)
            ->where('academic_year', $year)
            ->get();
        
        return response()->json(['data' => $students], 200);
    }
}
