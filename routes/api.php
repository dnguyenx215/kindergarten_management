<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\EnrollmentFeeController;
use App\Http\Controllers\ExtracurricularActivityController;
use App\Http\Controllers\GradeBlockController;
use App\Http\Controllers\HolidayConfigurationController;
use App\Http\Controllers\MealRegistrationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffTaskController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentMonitoringController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\TuitionFeeController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Đường dẫn lấy thông tin người dùng (nếu cần)
Route::get('/user', function (Request $request) {
    return $request->user();
});

// Lấy danh sách người dùng theo role
Route::get('/users', function (Request $request) {
    $role = $request->query('role');
    
    // Nếu role được chỉ định, lọc theo role đó
    if ($role) {
        $users = User::where('role', $role)->get();
    } else {
        // Nếu không có role được chỉ định, lấy tất cả người dùng
        $users = User::all();
    }
    
    return response()->json(['data' => $users], 200);
});

// Routes cho khối học
Route::apiResource('grade-blocks', GradeBlockController::class);

// Routes cho cấu hình ngày nghỉ
Route::prefix('holidays')->group(function () {
    Route::get('/', [HolidayConfigurationController::class, 'index']);
    Route::post('/', [HolidayConfigurationController::class, 'store']);
    Route::put('/{holiday}', [HolidayConfigurationController::class, 'update']);
    Route::delete('/{holiday}', [HolidayConfigurationController::class, 'destroy']);
    Route::post('/check', [HolidayConfigurationController::class, 'checkHoliday']);
    Route::post('/create-weekend', [HolidayConfigurationController::class, 'createWeekendHolidays']);
});

// Routes cho báo ăn
Route::prefix('meal-registration')->group(function () {
    Route::post('/', [MealRegistrationController::class, 'registerMeals']);
    Route::get('/', [MealRegistrationController::class, 'getMealRegistration']);
    Route::get('/report', [MealRegistrationController::class, 'getMealReport']);
    Route::delete('/', [MealRegistrationController::class, 'cancelMealRegistration']);
});

// Routes cho theo dõi học sinh
Route::prefix('student-monitoring')->group(function () {
    Route::post('/health', [StudentMonitoringController::class, 'monitorHealth']);
    Route::post('/absence', [StudentMonitoringController::class, 'reportAbsence']);
    Route::post('/health-check', [StudentMonitoringController::class, 'sendHealthCheck']);
    Route::get('/attendance-report', [StudentMonitoringController::class, 'getAttendanceReport']);
    Route::post('/daily-report', [StudentMonitoringController::class, 'createDailyReport']);
});

// Giữ nguyên các routes cũ từ phiên bản trước
// (Các routes như students, classes, attendances, etc. vẫn được giữ nguyên)

// Routes cho học sinh
Route::apiResource('students', StudentController::class);
Route::post('students/{student}/assign-class', [StudentController::class, 'assignClass']);

// Routes cho lớp học (Admin)
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
Route::patch('extracurricular-activities/{activity}/notify', [ExtracurricularActivityController::class, 'notify']);

// Routes cho quản lý nhân sự (Staff)
Route::apiResource('staff', StaffController::class);

// Routes cho phân công nhiệm vụ (Staff Tasks)
Route::apiResource('staff-tasks', StaffTaskController::class);

// Routes cho quản lý thông báo (Notification)
Route::apiResource('notifications', NotificationController::class);
Route::patch('notifications/send-scheduled', [NotificationController::class, 'sendScheduled']);

// Routes cho xác thực (Auth)
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);

// Routes cho hệ thống
Route::post('/system/assign-role', [SystemController::class, 'assignRole']);
Route::post('/system/backup', [SystemController::class, 'backupDatabase']);
Route::post('/system/restore', [SystemController::class, 'restoreDatabase']);
Route::post('/system/settings', [SystemController::class, 'updateSystemSettings']);
Route::get('/system/settings', [SystemController::class, 'getSystemSettings']);

// Routes cho thanh toán học phí
Route::patch('tuition-fees/{tuitionFee}/pay', [TuitionFeeController::class, 'pay']);
Route::patch('tuition-fees/vnpay-return', [TuitionFeeController::class, 'vnpayReturn']);
