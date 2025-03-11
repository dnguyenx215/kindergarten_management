<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    // Lấy danh sách enrollment (học sinh chờ nhập học)
    public function index()
    {
        $enrollments = Enrollment::orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $enrollments], 200);
    }

    // Tạo mới enrollment
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'    => 'required|string|max:255',
            'birthday'     => 'nullable|date',
            'parent_phone' => 'nullable|string|max:20',
            'parent_email' => 'nullable|email|max:255',
            'status'       => 'nullable|in:pending,approved,rejected',
        ]);

        $enrollment = Enrollment::create($validated);
        return response()->json(['data' => $enrollment], 201);
    }

    // Hiển thị thông tin chi tiết của một enrollment
    public function show(Enrollment $enrollment)
    {
        return response()->json(['data' => $enrollment], 200);
    }

    // Cập nhật hồ sơ tuyển sinh (chỉnh sửa thông tin enrollment, không thay đổi status)
    public function update(Request $request, Enrollment $enrollment)
    {
        // Cho phép cập nhật các trường thông tin cơ bản, không thay đổi status, student_code hay approved_at
        $validated = $request->validate([
            'full_name'    => 'sometimes|required|string|max:255',
            'birthday'     => 'nullable|date',
            'parent_phone' => 'nullable|string|max:20',
            'parent_email' => 'nullable|email|max:255',
        ]);

        $enrollment->update($validated);
        return response()->json(['data' => $enrollment], 200);
    }

    // Cập nhật status để tuyển sinh (approve hoặc reject)
    public function decision(Request $request, Enrollment $enrollment)
    {
        // Validate: nếu status là approved, phải có class_id để phân lớp
        $validated = $request->validate([
            'status'   => 'required|in:approved,rejected',
            'class_id' => 'required_if:status,approved|exists:classes,id',
        ]);

        // Chỉ cho phép duyệt tuyển sinh nếu enrollment đang ở trạng thái pending
        if ($enrollment->status !== 'pending') {
            return response()->json([
                'message' => 'Chỉ enrollment có status pending mới có thể được duyệt tuyển sinh.'
            ], 422);
        }

        // Nếu status là rejected, chỉ cập nhật status
        if ($validated['status'] === 'rejected') {
            $enrollment->update(['status' => 'rejected']);
            return response()->json([
                'message' => 'Enrollment đã bị từ chối.',
                'data'    => $enrollment,
            ], 200);
        }

        // Nếu status là approved, thực hiện quy trình tuyển sinh
        // Tách full_name thành first_name và last_name (ví dụ đơn giản theo khoảng trắng)
        $fullName  = trim($enrollment->full_name);
        $nameParts = explode(' ', $fullName);
        $firstName = array_shift($nameParts);
        $lastName  = implode(' ', $nameParts);

        // Tạo mã học sinh tự động theo định dạng: HS{năm hiện tại}{số thứ tự 5 chữ số}
        $year = date('Y');
        $count = Student::where('student_code', 'like', "HS{$year}%")->count() + 1;
        $studentCode = "HS{$year}" . str_pad($count, 5, '0', STR_PAD_LEFT);

        // Tạo hồ sơ học sinh mới với thông tin từ enrollment và phân lớp theo class_id
        $studentData = [
            'student_code' => $studentCode,
            'first_name'   => $firstName,
            'last_name'    => $lastName,
            'birthday'     => $enrollment->birthday,
            'gender'       => null, // Có thể cập nhật sau nếu có thông tin
            'address'      => null, // Có thể cập nhật sau
            'parent_name'  => null, // Có thể cập nhật sau
            'parent_phone' => $enrollment->parent_phone,
            'parent_email' => $enrollment->parent_email,
            'class_id'     => $validated['class_id'],
        ];

        $newStudent = Student::create($studentData);

        // Cập nhật enrollment: chuyển status thành approved, lưu lại student_code và thời gian duyệt (approved_at)
        $enrollment->update([
            'status'       => 'approved',
            'student_code' => $newStudent->student_code,
            'approved_at'  => now(),
        ]);

        return response()->json([
            'message' => 'Enrollment được duyệt tuyển sinh và hồ sơ học sinh đã được tạo thành công.',
            'data'    => $enrollment,
        ], 200);
    }

    // Xóa enrollment
    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();
        return response()->json(['message' => 'Enrollment record deleted successfully'], 200);
    }
}
