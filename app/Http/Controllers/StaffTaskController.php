<?php

namespace App\Http\Controllers;

use App\Models\StaffTask;
use App\Models\User;
use Illuminate\Http\Request;

class StaffTaskController extends Controller
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
     * Lấy danh sách nhiệm vụ. Nếu có truyền staff_id thì lọc theo staff_id.
     */
    public function index(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        if ($request->has('staff_id')) {
            $tasks = StaffTask::where('staff_id', $request->input('staff_id'))
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $tasks = StaffTask::orderBy('created_at', 'desc')->get();
        }
        return response()->json(['data' => $tasks], 200);
    }
    
    /**
     * Tạo mới nhiệm vụ và phân công cho một nhân sự.
     */
    public function store(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'staff_id'   => 'required|exists:staff,id',
            'title'      => 'required|string|max:255',
            'description'=> 'nullable|string',
            'due_date'   => 'nullable|date_format:Y-m-d H:i:s',
        ]);
        
        $task = StaffTask::create($validated);
        return response()->json(['data' => $task], 201);
    }
    
    /**
     * Hiển thị chi tiết của một nhiệm vụ.
     */
    public function show(Request $request, StaffTask $staffTask)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }
        return response()->json(['data' => $staffTask], 200);
    }
    
    /**
     * Cập nhật nhiệm vụ.
     */
    public function update(Request $request, StaffTask $staffTask)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }
        
        $validated = $request->validate([
            'staff_id'   => 'sometimes|required|exists:staff,id',
            'title'      => 'sometimes|required|string|max:255',
            'description'=> 'nullable|string',
            'due_date'   => 'nullable|date_format:Y-m-d H:i:s',
        ]);
        
        $staffTask->update($validated);
        return response()->json(['data' => $staffTask], 200);
    }
    
    /**
     * Xóa nhiệm vụ.
     */
    public function destroy(Request $request, StaffTask $staffTask)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập.'], 403);
        }
        
        $staffTask->delete();
        return response()->json(['message' => 'Nhiệm vụ đã được xoá thành công'], 200);
    }
}
