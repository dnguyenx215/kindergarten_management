<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRolesTableSeeder extends Seeder
{
    public function run()
    {
        // Xóa dữ liệu cũ nếu có
        DB::table('user_roles')->truncate();
        
        // Lấy danh sách users và roles đã tạo
        $admin = DB::table('users')->where('email', 'admin@example.com')->first();
        $teacher = DB::table('users')->where('email', 'teacher@example.com')->first();
        $staff = DB::table('users')->where('email', 'staff@example.com')->first();
        $parent = DB::table('users')->where('email', 'parent@example.com')->first();
        
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        $teacherRole = DB::table('roles')->where('name', 'teacher')->first();
        $staffRole = DB::table('roles')->where('name', 'staff')->first();
        $parentRole = DB::table('roles')->where('name', 'parent')->first();
        
        // Gán roles cho users
        DB::table('user_roles')->insert([
            [
                'user_id' => $admin->id,
                'role_id' => $adminRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $teacher->id,
                'role_id' => $teacherRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $staff->id,
                'role_id' => $staffRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $parent->id,
                'role_id' => $parentRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}