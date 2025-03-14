<?php

namespace App\Http\Controllers;

use App\Models\MealRegistration;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MealRegistrationController extends Controller
{
    /**
     * Báo số lượng ăn cho một lớp trong một ngày
     */
    public function registerMeals(Request $request)
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);
        
        if (!$user || (strtolower($user->role) !== 'teacher' && strtolower($user->role) !== 'admin')) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'breakfast_count' => 'nullable|integer|min:0',
            'lunch_count' => 'nullable|integer|min:0',
            'dinner_count' => 'nullable|integer|min:0'
        ]);

        // Kiểm tra xem đã đăng ký cho lớp này trong ngày chưa
        $existingRegistration = MealRegistration::where('class_id', $validated['class_id'])
            ->where('date', $validated['date'])
            ->first();

        if ($existingRegistration) {
            // Cập nhật đăng ký cũ
            $existingRegistration->update([
                'breakfast_count' => $validated['breakfast_count'] ?? $existingRegistration->breakfast_count,
                'lunch_count' => $validated['lunch_count'] ?? $existingRegistration->lunch_count,
                'dinner_count' => $validated['dinner_count'] ?? $existingRegistration->dinner_count,
                'registered_by' => $userId
            ]);
            $registration = $existingRegistration;
        } else {
            // Tạo đăng ký mới
            $registration = MealRegistration::create([
                'class_id' => $validated['class_id'],
                'date' => $validated['date'],
                'breakfast_count' => $validated['breakfast_count'] ?? 0,
                'lunch_count' => $validated['lunch_count'] ?? 0,
                'dinner_count' => $validated['dinner_count'] ?? 0,
                'registered_by' => $userId
            ]);
        }

        return response()->json(['data' => $registration], 200);
    }

    /**
     * Lấy thông tin báo ăn của một lớp
     */
    public function getMealRegistration(Request $request)
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);
        
        // Validate user permission (teacher or admin)
        if (!$user || (strtolower($user->role) !== 'teacher' && strtolower($user->role) !== 'admin')) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
        ]);

        // Find meal registration for the specific class and date
        $registration = MealRegistration::where('class_id', $validated['class_id'])
            ->where('date', $validated['date'])
            ->with('class', 'registeredByUser')
            ->first();

        if (!$registration) {
            return response()->json(['message' => 'Không tìm thấy đăng ký ăn'], 404);
        }

        return response()->json(['data' => $registration], 200);
    }

    /**
     * Lấy báo cáo tổng hợp báo ăn
     */
    public function getMealReport(Request $request)
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);
        
        // Validate user permission (admin)
        if (!$user || strtolower($user->role) !== 'admin') {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'class_id' => 'nullable|exists:classes,id'
        ]);

        // Xây dựng query báo cáo
        $query = MealRegistration::whereBetween('date', [
            $validated['start_date'], 
            $validated['end_date']
        ]);

        // Lọc theo lớp nếu có
        if (!empty($validated['class_id'])) {
            $query->where('class_id', $validated['class_id']);
        }

        // Tổng hợp báo cáo
        $report = $query->selectRaw('
            date, 
            SUM(breakfast_count) as total_breakfast, 
            SUM(lunch_count) as total_lunch, 
            SUM(dinner_count) as total_dinner,
            COUNT(DISTINCT class_id) as total_classes
        ')
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        return response()->json([
            'data' => $report,
            'metadata' => [
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date']
            ]
        ], 200);
    }

    /**
     * Hủy đăng ký ăn
     */
    public function cancelMealRegistration(Request $request)
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);
        
        // Validate user permission (teacher or admin)
        if (!$user || (strtolower($user->role) !== 'teacher' && strtolower($user->role) !== 'admin')) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
        ]);

        // Tìm và xóa đăng ký ăn
        $registration = MealRegistration::where('class_id', $validated['class_id'])
            ->where('date', $validated['date'])
            ->first();

        if (!$registration) {
            return response()->json(['message' => 'Không tìm thấy đăng ký ăn'], 404);
        }

        $registration->delete();

        return response()->json([
            'message' => 'Hủy đăng ký ăn thành công',
            'data' => $registration
        ], 200);
    }
}