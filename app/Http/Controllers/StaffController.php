<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    /**
     * Kiểm tra quyền: chỉ cho phép user có role "admin".
     */
    protected function checkAdmin(Request $request)
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);
        return $user && strtolower($user->role) === 'admin';
    }

    /**
     * Lấy danh sách nhân sự.
     */
    public function index(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }
        $staff = Staff::orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $staff], 200);
    }
    
    /**
     * Tạo mới nhân sự.
     */
    public function store(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'role'  => 'required|string', // ví dụ: "teacher", "staff"
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:staff,email',
        ]);
        
        $staff = Staff::create($validated);
        return response()->json(['data' => $staff], 201);
    }
    
    /**
     * Hiển thị thông tin chi tiết của một nhân sự.
     */
    public function show(Request $request, Staff $staff)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }
        return response()->json(['data' => $staff], 200);
    }
    
    /**
     * Cập nhật thông tin của một nhân sự.
     */
    public function update(Request $request, Staff $staff)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }
        
        $validated = $request->validate([
            'name'  => 'sometimes|required|string|max:255',
            'role'  => 'sometimes|required|string',
            'phone' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|required|email|unique:staff,email,' . $staff->id,
        ]);
        
        $staff->update($validated);
        return response()->json(['data' => $staff], 200);
    }
    
    /**
     * Xóa nhân sự.
     */
    public function destroy(Request $request, Staff $staff)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }
        $staff->delete();
        return response()->json(['message' => 'Staff đã được xoá thành công'], 200);
    }
}
