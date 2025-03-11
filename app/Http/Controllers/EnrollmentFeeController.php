<?php

namespace App\Http\Controllers;

use App\Models\EnrollmentFee;
use Illuminate\Http\Request;

class EnrollmentFeeController extends Controller
{
    // Lấy danh sách enrollment fees
    public function index()
    {
        $fees = EnrollmentFee::orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $fees], 200);
    }

    // Tạo mới enrollment fee
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount'     => 'required|numeric|min:0',
            'status'     => 'nullable|in:unpaid,paid',
        ]);

        $fee = EnrollmentFee::create($validated);
        return response()->json(['data' => $fee], 201);
    }

    // Hiển thị chi tiết 1 enrollment fee
    public function show(EnrollmentFee $enrollmentFee)
    {
        return response()->json(['data' => $enrollmentFee], 200);
    }

    // Cập nhật enrollment fee (thay đổi số tiền, trạng thái thanh toán,...)
    public function update(Request $request, EnrollmentFee $enrollmentFee)
    {
        $validated = $request->validate([
            'student_id' => 'sometimes|required|exists:students,id',
            'amount'     => 'sometimes|required|numeric|min:0',
            'status'     => 'sometimes|required|in:unpaid,paid',
        ]);

        $enrollmentFee->update($validated);
        return response()->json(['data' => $enrollmentFee], 200);
    }

    // Xóa enrollment fee
    public function destroy(EnrollmentFee $enrollmentFee)
    {
        $enrollmentFee->delete();
        return response()->json(['message' => 'Enrollment fee record deleted successfully'], 200);
    }
}
