<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentClassHistory;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    // Lấy danh sách học sinh
    public function index()
    {
        $students = Student::orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $students], 200);
    }

    // Tạo mới học sinh
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_code' => 'nullable|string|unique:students,student_code',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birthday' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female',
            'address' => 'nullable|string',
            'parent_name' => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string|max:20',
            'parent_email' => 'nullable|email|max:255',
            'class_id' => 'nullable|exists:classes,id',
        ]);

        // Tự động tạo student_code nếu không có
        if (empty($validated['student_code'])) {
            $year = date('Y');
            $count = Student::where('student_code', 'like', "HS{$year}%")->count() + 1;
            $validated['student_code'] = "HS{$year}" . str_pad($count, 5, '0', STR_PAD_LEFT);
        }

        $student = Student::create($validated);
        return response()->json(['data' => $student], 201);
    }

    // Lấy thông tin chi tiết 1 học sinh
    public function show(Student $student)
    {
        return response()->json(['data' => $student], 200);
    }

    // Cập nhật thông tin học sinh
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'student_code' => 'nullable|string|unique:students,student_code,' . $student->id,
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'birthday' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female',
            'address' => 'nullable|string',
            'parent_name' => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string|max:20',
            'parent_email' => 'nullable|email|max:255',
            'class_id' => 'nullable|exists:classes,id',
        ]);

        $student->update($validated);
        return response()->json(['data' => $student], 200);
    }

    // Xoá học sinh
    public function destroy(Student $student)
    {
        $student->delete();
        return response()->json(['message' => 'Học sinh đã được xoá thành công'], 200);
    }

    // Phân lớp thủ công cho học sinh (xếp lớp)
    public function assignClass(Request $request, Student $student)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        $newClassId = $validated['class_id'];

        // Nếu học sinh đã có lớp và đang cố gán lại lớp hiện tại thì trả về thông báo
        if ($student->class_id == $newClassId) {
            return response()->json(['message' => 'Học sinh đã thuộc lớp này.'], 200);
        }

        // Lấy thông tin lớp học và đếm số học sinh đã được gán vào lớp đó
        $classroom = Classroom::findOrFail($newClassId);
        $currentStudentCount = Student::where('class_id', $newClassId)->count();

        // Kiểm tra nếu số học sinh hiện tại đã đạt đến giới hạn capacity của lớp
        if ($currentStudentCount >= $classroom->capacity) {
            return response()->json([
                'message' => 'Lớp đã đầy, không thể phân lớp cho học sinh này.'
            ], 422);
        }

        // Nếu học sinh đã có lớp cũ, cập nhật lịch sử lớp hiện tại với end_date
        if ($student->class_id) {
            $currentHistory = StudentClassHistory::where('student_id', $student->id)
                ->whereNull('end_date')
                ->orderBy('start_date', 'desc')
                ->first();
            if ($currentHistory) {
                $currentHistory->update(['end_date' => now()]);
            }
        }

        // Cập nhật lớp hiện tại của học sinh
        $student->update(['class_id' => $newClassId]);

        // Tạo mới bản ghi lịch sử lớp với start_date là thời điểm hiện tại
        StudentClassHistory::create([
            'student_id' => $student->id,
            'class_id' => $newClassId,
            'start_date' => now(),
            'end_date' => null,
        ]);

        return response()->json([
            'message' => 'Phân lớp thủ công thành công và lịch sử lớp được cập nhật.',
            'data' => $student,
        ], 200);
    }


}
