<?php

namespace App\Http\Controllers;

use App\Models\HolidayConfiguration;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HolidayConfigurationController extends Controller
{
    /**
     * Lấy danh sách ngày nghỉ
     */
    public function index(Request $request)
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);
        
        if (!$user || strtolower($user->role) !== 'admin') {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        // Lọc theo năm học hiện tại nếu có
        $year = $request->input('year', date('Y'));
        $holidays = HolidayConfiguration::whereYear('holiday_date', $year)
            ->orderBy('holiday_date')
            ->get();

        return response()->json(['data' => $holidays], 200);
    }

    /**
     * Thêm mới ngày nghỉ
     */
    public function store(Request $request)
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);
        
        if (!$user || strtolower($user->role) !== 'admin') {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'holiday_date' => 'required|date|unique:holiday_configurations,holiday_date',
            'holiday_name' => 'nullable|string|max:255',
            'holiday_type' => 'required|in:weekend,national,school',
            'description' => 'nullable|string',
        ]);

        $validated['created_by'] = $userId;

        $holiday = HolidayConfiguration::create($validated);
        return response()->json(['data' => $holiday], 201);
    }

    /**
     * Cập nhật thông tin ngày nghỉ
     */
    public function update(Request $request, HolidayConfiguration $holiday)
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);
        
        if (!$user || strtolower($user->role) !== 'admin') {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'holiday_date' => 'sometimes|required|date|unique:holiday_configurations,holiday_date,' . $holiday->id,
            'holiday_name' => 'nullable|string|max:255',
            'holiday_type' => 'sometimes|required|in:weekend,national,school',
            'description' => 'nullable|string',
        ]);

        $holiday->update($validated);
        return response()->json(['data' => $holiday], 200);
    }

    /**
     * Xóa ngày nghỉ
     */
    public function destroy(Request $request, HolidayConfiguration $holiday)
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);
        
        if (!$user || strtolower($user->role) !== 'admin') {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $holiday->delete();
        return response()->json(['message' => 'Ngày nghỉ đã được xóa thành công'], 200);
    }

    /**
     * Kiểm tra xem một ngày có phải là ngày nghỉ không
     */
    public function checkHoliday(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
        ]);

        $date = Carbon::parse($validated['date']);
        
        // Kiểm tra ngày nghỉ quốc gia, cuối tuần hoặc ngày nghỉ của trường
        $isHoliday = HolidayConfiguration::where('holiday_date', $date->toDateString())
            ->orWhere(function($query) use ($date) {
                // Nếu là thứ 7 hoặc chủ nhật
                $query->when($date->isWeekend(), function($q) {
                    $q->whereIn('holiday_type', ['weekend', 'national', 'school']);
                });
            })
            ->exists();

        return response()->json([
            'date' => $date->toDateString(),
            'is_holiday' => $isHoliday
        ], 200);
    }

    /**
     * Tạo ngày nghỉ lặp lại hàng tuần (cuối tuần)
     */
    public function createWeekendHolidays(Request $request)
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);
        
        if (!$user || strtolower($user->role) !== 'admin') {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
        ]);

        $year = $validated['year'];
        $weekendHolidays = [];

        // Tạo ngày nghỉ cuối tuần cho năm
        $startDate = Carbon::create($year, 1, 1);
        $endDate = Carbon::create($year, 12, 31);

        while ($startDate <= $endDate) {
            if ($startDate->isWeekend()) {
                $existingHoliday = HolidayConfiguration::where('holiday_date', $startDate->toDateString())
                    ->where('holiday_type', 'weekend')
                    ->first();

                if (!$existingHoliday) {
                    $holiday = HolidayConfiguration::create([
                        'holiday_date' => $startDate->toDateString(),
                        'holiday_name' => 'Cuối tuần',
                        'holiday_type' => 'weekend',
                        'created_by' => $userId
                    ]);
                    $weekendHolidays[] = $holiday;
                }
            }
            $startDate->addDay();
        }

        return response()->json([
            'message' => 'Đã tạo ngày nghỉ cuối tuần thành công',
            'data' => $weekendHolidays
        ], 201);
    }
}