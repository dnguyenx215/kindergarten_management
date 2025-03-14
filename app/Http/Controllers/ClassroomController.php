<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    /**
     * Admin: Lấy danh sách lớp học kèm theo số lượng học sinh.
     */
    public function index(Request $request)
    {
        // Lấy user_id từ request (ví dụ: query parameter)
        $userId = $request->input('user_id');
        
        // Sử dụng eager loading để lấy thông tin user kèm roles
        $user = User::with('roles')->find($userId);

        // Kiểm tra quyền truy cập dựa trên roles
        if (!$this->hasAdminPermission($user)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $classrooms = Classroom::withCount('students')->orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $classrooms], 200);
    }

    /**
     * Admin: Tạo mới lớp học (có thể bao gồm gán giáo viên chủ nhiệm nếu có).
     */
    public function store(Request $request)
    {
        $userId = $request->input('user_id');
        
        // Sử dụng eager loading để lấy thông tin user kèm roles
        $user = User::with('roles')->find($userId);

        // Kiểm tra quyền truy cập dựa trên roles
        if (!$this->hasAdminPermission($user)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'capacity'            => 'required|integer|min:0',
            'homeroom_teacher_id' => 'nullable|exists:users,id',
        ]);

        $classroom = Classroom::create($validated);
        return response()->json(['data' => $classroom], 201);
    }

    /**
     * Admin: Hiển thị thông tin chi tiết của 1 lớp học,
     * bao gồm danh sách học sinh và thông tin giáo viên chủ nhiệm.
     */
    public function show(Request $request, Classroom $classroom)
    {
        $userId = $request->input('user_id');
        
        // Sử dụng eager loading để lấy thông tin user kèm roles
        $user = User::with('roles')->find($userId);

        // Kiểm tra quyền truy cập dựa trên roles
        if (!$this->hasAdminPermission($user)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $classroom->load('students', 'homeroomTeacher');
        return response()->json(['data' => $classroom], 200);
    }

    /**
     * Admin: Cập nhật thông tin lớp học.
     */
    public function update(Request $request, Classroom $classroom)
    {
        $userId = $request->input('user_id');
        
        // Sử dụng eager loading để lấy thông tin user kèm roles
        $user = User::with('roles')->find($userId);

        // Kiểm tra quyền truy cập dựa trên roles
        if (!$this->hasAdminPermission($user)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'name'                => 'sometimes|required|string|max:255',
            'capacity'            => 'nullable|integer|min:0',
            'homeroom_teacher_id' => 'nullable|exists:users,id',
        ]);

        $classroom->update($validated);
        return response()->json(['data' => $classroom], 200);
    }

    /**
     * Admin: Xoá lớp học.
     */
    public function destroy(Request $request, Classroom $classroom)
    {
        $userId = $request->input('user_id');
        
        // Sử dụng eager loading để lấy thông tin user kèm roles
        $user = User::with('roles')->find($userId);

        // Kiểm tra quyền truy cập dựa trên roles
        if (!$this->hasAdminPermission($user)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $classroom->delete();
        return response()->json(['message' => 'Lớp học đã được xoá thành công'], 200);
    }

    /**
     * GVCN: Hiển thị lớp phụ trách của giáo viên chủ nhiệm cùng danh sách học sinh.
     */
    public function myClass(Request $request)
    {
        $userId = $request->input('user_id');
        
        // Sử dụng eager loading để lấy thông tin user kèm roles
        $user = User::with('roles')->find($userId);

        // Kiểm tra quyền truy cập dựa trên roles
        if (!$this->hasTeacherPermission($user)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $classroom = Classroom::with('students')
            ->where('homeroom_teacher_id', $userId)
            ->first();

        if (!$classroom) {
            return response()->json(['message' => 'Không tìm thấy lớp phụ trách của bạn.'], 404);
        }

        return response()->json(['data' => $classroom], 200);
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

    /**
     * Kiểm tra nếu người dùng có quyền giáo viên
     * 
     * @param User|null $user
     * @return bool
     */
    private function hasTeacherPermission($user)
    {
        if (!$user) {
            return false;
        }

        // Nếu vẫn còn trường role trong bảng users (backward compatibility)
        if (property_exists($user, 'role') && strtolower($user->role) === 'teacher') {
            return true;
        }

        // Kiểm tra qua bảng quan hệ roles
        foreach ($user->roles as $role) {
            if (strtolower($role->name) === 'teacher') {
                return true;
            }
        }

        return false;
    }
}