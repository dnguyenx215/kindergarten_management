<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        // Xóa dữ liệu cũ nếu có
        DB::table('roles')->truncate();
        
        // Thêm các roles
        DB::table('roles')->insert([
            [
                'name' => 'admin',
                'display_name' => 'Quản trị viên',
                'description' => 'Quản lý toàn bộ hệ thống',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'teacher',
                'display_name' => 'Giáo viên',
                'description' => 'Giáo viên dạy học',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'staff',
                'display_name' => 'Nhân viên',
                'description' => 'Nhân viên hỗ trợ',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'parent',
                'display_name' => 'Phụ huynh',
                'description' => 'Phụ huynh học sinh',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}