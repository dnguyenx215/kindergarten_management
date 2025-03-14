<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Đảm bảo có dữ liệu trong bảng roles
        $this->seedRoles();
        
        // Tạo mapping của roles cũ sang ID mới
        $roleMapping = [
            'admin' => 1,   // ID của role admin
            'teacher' => 2, // ID của role teacher 
            'staff' => 3,   // ID của role staff
            'parent' => 4   // ID của role parent
        ];
        
        // Đảm bảo rằng chúng ta lưu liên kết vào bảng user_roles
        foreach ($roleMapping as $roleName => $roleId) {
            DB::table('users')
                ->where('role', $roleName)
                ->chunkById(100, function ($users) use ($roleId) {
                    foreach ($users as $user) {
                        DB::table('user_roles')->insert([
                            'user_id' => $user->id,
                            'role_id' => $roleId,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                });
        }
        
        // Sau khi đã tạo liên kết, chúng ta có thể xóa cột role
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        // Thêm lại cột role
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'teacher', 'staff', 'parent'])->after('password');
        });
        
        // Khôi phục dữ liệu từ bảng user_roles
        DB::table('users')->chunkById(100, function ($users) {
            foreach ($users as $user) {
                $userRole = DB::table('user_roles')
                    ->where('user_id', $user->id)
                    ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                    ->first();
                
                if ($userRole) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['role' => $userRole->name]);
                } else {
                    // Mặc định gán role là staff nếu không tìm thấy
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['role' => 'staff']);
                }
            }
        });
    }
    
    private function seedRoles() 
    {
        // Danh sách các roles mặc định
        $roles = [
            ['name' => 'admin', 'display_name' => 'Quản trị viên', 'description' => 'Quản lý toàn bộ hệ thống'],
            ['name' => 'teacher', 'display_name' => 'Giáo viên', 'description' => 'Giáo viên dạy học'],
            ['name' => 'staff', 'display_name' => 'Nhân viên', 'description' => 'Nhân viên hỗ trợ'],
            ['name' => 'parent', 'display_name' => 'Phụ huynh', 'description' => 'Phụ huynh học sinh']
        ];
        
        // Kiểm tra nếu bảng roles chưa có dữ liệu
        if (DB::table('roles')->count() === 0) {
            foreach ($roles as $role) {
                DB::table('roles')->insert(array_merge($role, [
                    'created_at' => now(),
                    'updated_at' => now()
                ]));
            }
        }
    }
};