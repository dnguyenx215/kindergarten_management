<?php

namespace App\Http\Controllers;

use App\Models\GradeBlock;
use App\Models\User;
use Illuminate\Http\Request;

class GradeBlockController extends Controller
{
    /**
     * Lấy danh sách khối học
     */
    public function index(Request $request)
    {
        $userId = $request->input('user_id');
        $user = User::with('roles')->find($userId);

        if (!$this->hasAdminPermission($user)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $gradeBlocks = GradeBlock::all();
        return response()->json(['data' => $gradeBlocks], 200);
    }

    /**
     * Tạo mới khối học
     */
    public function store(Request $request)
    {
        $userId = $request->input('user_id');
        $user = User::with('roles')->find($userId);

        if (!$this->hasAdminPermission($user)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'code' => 'required|unique:grade_blocks,code|max:10',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $gradeBlock = GradeBlock::create($validated);
        return response()->json(['data' => $gradeBlock], 201);
    }

    /**
     * Cập nhật thông tin khối học
     */
    public function update(Request $request, GradeBlock $gradeBlock)
    {
        $userId = $request->input('user_id');
        $user = User::with('roles')->find($userId);

        if (!$this->hasAdminPermission($user)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'code' => 'sometimes|required|unique:grade_blocks,code,' . $gradeBlock->id . '|max:10',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $gradeBlock->update($validated);
        return response()->json(['data' => $gradeBlock], 200);
    }

    /**
     * Xóa khối học
     */
    public function destroy(Request $request, GradeBlock $gradeBlock)
    {
        $userId = $request->input('user_id');
        $user = User::with('roles')->find($userId);

        if (!$this->hasAdminPermission($user)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        if ($gradeBlock->classes()->exists()) {
            return response()->json([
                'message' => 'Không thể xóa khối học đang có lớp học.'
            ], 422);
        }

        $gradeBlock->delete();
        return response()->json(['message' => 'Khối học đã được xóa thành công'], 200);
    }

    /**
     * Chi tiết một khối học
     */
    public function show(Request $request, GradeBlock $gradeBlock)
    {
        $userId = $request->input('user_id');
        $user = User::with('roles')->find($userId);

        if (!$this->hasAdminPermission($user)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $gradeBlock->load('classes');
        return response()->json(['data' => $gradeBlock], 200);
    }

    /**
     * Kiểm tra nếu người dùng có quyền admin
     * 
     * @param User|null $user
     * @return bool
     */
    private function hasAdminPermission($user)
    {
        if (!$user) {
            return false;
        }

        // Nếu vẫn còn trường role trong bảng users (backward compatibility)
        if (property_exists($user, 'role') && strtolower($user->role) === 'admin') {
            return true;
        }

        // Kiểm tra qua bảng quan hệ roles
        foreach ($user->roles as $role) {
            if (strtolower($role->name) === 'admin') {
                return true;
            }
        }

        return false;
    }
}