<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Lấy danh sách thông báo (theo lịch sử gửi).
     */
    public function index()
    {
        $notifications = Notification::with('receivers')->orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $notifications], 200);
    }

    /**
     * Tạo mới thông báo.
     * Yêu cầu truyền:
     * - title: string
     * - message: string
     * - sender_id: ID người gửi (ví dụ: admin)
     * - receiver_ids: mảng các user_id nhận thông báo
     * - schedule_at (optional): thời gian dự kiến gửi
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'message'      => 'required|string',
            'sender_id'    => 'required|exists:users,id',
            'receiver_ids' => 'required|array', // mảng các ID
            'receiver_ids.*' => 'exists:users,id',
            'schedule_at'  => 'nullable|date',
        ]);

        // Tạo thông báo mới với trạng thái pending
        $notification = Notification::create([
            'title'       => $validated['title'],
            'message'     => $validated['message'],
            'sender_id'   => $validated['sender_id'],
            'schedule_at' => $validated['schedule_at'] ?? null,
            'status'      => 'pending',
        ]);

        // Gán danh sách receiver_ids vào bảng pivot
        $notification->receivers()->attach($validated['receiver_ids']);

        return response()->json(['data' => $notification->load('receivers')], 201);
    }

    /**
     * Hiển thị chi tiết một thông báo.
     */
    public function show(Notification $notification)
    {
        $notification->load('receivers');
        return response()->json(['data' => $notification], 200);
    }

    /**
     * Cập nhật thông báo.
     */
    public function update(Request $request, Notification $notification)
    {
        $validated = $request->validate([
            'title'        => 'sometimes|required|string|max:255',
            'message'      => 'sometimes|required|string',
            'schedule_at'  => 'sometimes|nullable|date',
            'receiver_ids' => 'sometimes|required|array',
            'receiver_ids.*' => 'exists:users,id',
        ]);

        $notification->update($validated);

        // Nếu receiver_ids được gửi, cập nhật bảng pivot (xóa các receivers cũ và gán mới)
        if (isset($validated['receiver_ids'])) {
            $notification->receivers()->sync($validated['receiver_ids']);
        }

        return response()->json(['data' => $notification->load('receivers')], 200);
    }

    /**
     * Xóa thông báo.
     */
    public function destroy(Notification $notification)
    {
        $notification->delete();
        return response()->json(['message' => 'Thông báo đã được xoá thành công'], 200);
    }

    /**
     * Gửi thông báo tự động.
     *
     * Endpoint này tìm tất cả các thông báo có trạng thái "pending" và schedule_at đã đến,
     * giả lập quá trình gửi thông báo và cập nhật trạng thái thành "sent" cùng thời gian gửi.
     *
     * Phương thức này có thể được gọi bởi một scheduler (cron job).
     */
    public function sendScheduled()
    {
        $now = Carbon::now();
        $notifications = Notification::where('status', 'pending')
            ->whereNotNull('schedule_at')
            ->where('schedule_at', '<=', $now)
            ->get();

        foreach ($notifications as $notification) {
            // Giả lập quá trình gửi thông báo (ví dụ: gọi API gửi push notification qua app)
            // Sau khi gửi thành công, cập nhật trạng thái và thời gian gửi.
            $notification->update([
                'status'  => 'sent',
                'sent_at' => $now,
            ]);
        }

        return response()->json([
            'message' => 'Đã xử lý gửi thông báo tự động.',
            'data'    => $notifications
        ], 200);
    }
}
