<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\SystemSetting;
use App\Models\User;
use Spatie\Permission\Models\Role;

class SystemController extends Controller
{
    /**
     * Phân quyền tài khoản
     */
    public function assignRole(Request $request)
    {
       
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role'    => 'required|string|in:admin,teacher,accountant,parent',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->syncRoles([$request->role]);

        return response()->json(['message' => "Đã gán quyền {$request->role} cho người dùng."], 200);
    }

    /**
     * Quản lý sao lưu dữ liệu (Backup DB)
     */
    public function backupDatabase()
    {
       

        Artisan::call('backup:run');

        return response()->json(['message' => 'Backup dữ liệu thành công.'], 200);
    }

    /**
     * Khôi phục dữ liệu từ bản backup gần nhất
     */
    public function restoreDatabase()
    {
       

        // Lệnh giả định, cần script thực tế để thực hiện restore
        Artisan::call('backup:restore');

        return response()->json(['message' => 'Khôi phục dữ liệu thành công.'], 200);
    }

    /**
     * Cấu hình hệ thống: Năm học, học phí, thông tin trường, ngày nghỉ
     */
    public function updateSystemSettings(Request $request)
    {
       

        $validated = $request->validate([
            'school_year'     => 'nullable|string|max:255',
            'tuition_fee'     => 'nullable|numeric',
            'school_info'     => 'nullable|string',
            'holidays'        => 'nullable|array', // Danh sách ngày nghỉ
        ]);

        $settings = SystemSetting::first();
        $settings->update($validated);

        return response()->json(['message' => 'Cập nhật cấu hình hệ thống thành công.', 'data' => $settings], 200);
    }

    /**
     * Lấy cấu hình hệ thống
     */
    public function getSystemSettings()
    {
        $settings = SystemSetting::first();
        return response()->json(['data' => $settings], 200);
    }
}
