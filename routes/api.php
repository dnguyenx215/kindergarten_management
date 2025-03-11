<?php
use \App\Http\Controllers;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\EnrollmentFeeController;
use App\Http\Controllers\ExtracurricularActivityController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffTaskController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\TuitionFeeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Đường dẫn lấy thông tin người dùng (nếu cần)
Route::get('/user', function (Request $request) {
    return $request->user();
});

// Routes cho học sinh
Route::apiResource('students', StudentController::class);
Route::post('students/{student}/assign-class', [StudentController::class, 'assignClass']);

// Routes cho lớp học (Admin)
// Các controller sẽ tự kiểm tra quyền dựa trên user_id gửi kèm theo request
Route::get('admin/classes', [ClassroomController::class, 'index']);
Route::get('admin/classes/{classroom}', [ClassroomController::class, 'show']);
Route::post('admin/classes', [ClassroomController::class, 'store']);
Route::put('admin/classes/{classroom}', [ClassroomController::class, 'update']);
Route::delete('admin/classes/{classroom}', [ClassroomController::class, 'destroy']);

// Routes cho lớp học (Giáo viên chủ nhiệm)
Route::get('teacher/my-class', [ClassroomController::class, 'myClass']);

// Routes cho tuyển sinh
Route::apiResource('enrollments', EnrollmentController::class);
Route::patch('enrollments/{enrollment}/decision', [EnrollmentController::class, 'decision']);

// Routes cho học phí tuyển sinh
Route::apiResource('enrollment-fees', EnrollmentFeeController::class);

// Routes cho điểm danh
Route::get('attendances', [AttendanceController::class, 'index']);
Route::post('attendances', [AttendanceController::class, 'store']);

// Extracurricular Activities routes (Admin)
Route::apiResource('extracurricular-activities', ExtracurricularActivityController::class);
// Route để gửi thông báo cho hoạt động ngoại khóa
Route::patch('extracurricular-activities/{activity}/notify', [ExtracurricularActivityController::class, 'notify']);

// Routes cho quản lý nhân sự (Staff)
Route::apiResource('staff', StaffController::class);

// Routes cho phân công nhiệm vụ (Staff Tasks)
Route::apiResource('staff-tasks', StaffTaskController::class);

// Routes cho quản lý thông báo (Notification)
Route::apiResource('notifications', NotificationController::class);
// Route để trigger gửi thông báo tự động (có thể gọi bởi cron job)
Route::patch('notifications/send-scheduled', [NotificationController::class, 'sendScheduled']);


// Routes cho xác thực (Auth)
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);

Route::post('/system/assign-role', [SystemController::class, 'assignRole']);
Route::post('/system/backup', [SystemController::class, 'backupDatabase']);
Route::post('/system/restore', [SystemController::class, 'restoreDatabase']);
Route::post('/system/settings', [SystemController::class, 'updateSystemSettings']);
Route::get('/system/settings', [SystemController::class, 'getSystemSettings']);

Route::patch('tuition-fees/{tuitionFee}/pay', [TuitionFeeController::class, 'pay']);

Route::patch('tuition-fees/vnpay-return', [TuitionFeeController::class, 'vnpayReturn']);
