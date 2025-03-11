<?php

namespace App\Http\Controllers;

use App\Models\ExtracurricularActivity;
use App\Models\Notification;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;

class ExtracurricularActivityController extends Controller
{
    /**
     * Kiểm tra quyền: chỉ cho phép người dùng có role "admin".
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function checkAdmin(Request $request)
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);
        return $user && strtolower($user->role) === 'admin';
    }

    /**
     * Lấy danh sách hoạt động ngoại khóa.
     */
    public function index(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $activities = ExtracurricularActivity::with('classroom')
            ->orderBy('date', 'desc')
            ->get();
        return response()->json(['data' => $activities], 200);
    }

    /**
     * Tạo mới một hoạt động ngoại khóa.
     */
    public function store(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'date'        => 'required|date',
            'class_id'    => 'required|exists:classes,id',
        ]);

        $activity = ExtracurricularActivity::create($validated);
        return response()->json(['data' => $activity], 201);
    }

    /**
     * Hiển thị chi tiết một hoạt động ngoại khóa.
     */
    public function show(Request $request, ExtracurricularActivity $activity)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $activity->load('classroom');
        return response()->json(['data' => $activity], 200);
    }

    /**
     * Cập nhật một hoạt động ngoại khóa.
     */
    public function update(Request $request, ExtracurricularActivity $activity)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'date'        => 'sometimes|required|date',
            'class_id'    => 'sometimes|required|exists:classes,id',
        ]);

        $activity->update($validated);
        return response()->json(['data' => $activity], 200);
    }

    /**
     * Xoá một hoạt động ngoại khóa.
     */
    public function destroy(Request $request, ExtracurricularActivity $activity)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $activity->delete();
        return response()->json(['message' => 'Hoạt động ngoại khóa đã được xoá thành công'], 200);
    }

    /**
     * Gửi thông báo hoạt động ngoại khóa đến giáo viên (và có thể phụ huynh).
     *
     * Phương thức này tìm tất cả các nhân viên (staff) có role là "teacher" trong bảng staff
     * và tạo một thông báo cho mỗi người với tiêu đề và nội dung dựa trên thông tin hoạt động ngoại khóa.
     */
    public function notify(Request $request, ExtracurricularActivity $activity)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        // Lấy danh sách các nhân viên có vai trò "teacher"
        $teachers = Staff::where('role', 'teacher')->get();
        if ($teachers->isEmpty()) {
            return response()->json(['message' => 'Không tìm thấy giáo viên để thông báo.'], 404);
        }

        $notifications = [];
        foreach ($teachers as $teacher) {
            $notification = Notification::create([
                'title'    => 'Thông báo hoạt động ngoại khóa: ' . $activity->name,
                'message'  => "Hoạt động: {$activity->name}\nNgày tổ chức: {$activity->date}\nMô tả: {$activity->description}",
                'staff_id' => $teacher->id,
            ]);
            $notifications[] = $notification;
        }

        return response()->json([
            'message' => 'Thông báo hoạt động ngoại khóa đã được gửi đến giáo viên.',
            'data'    => $notifications,
        ], 200);
    }
}
