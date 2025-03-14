<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        // Xác định các cột tồn tại trong bảng users
        $columns = Schema::getColumnListing('users');
        
        // Dữ liệu cơ bản cho mỗi user
        $usersData = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Teacher User',
                'email' => 'teacher@example.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Staff User',
                'email' => 'staff@example.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Parent User',
                'email' => 'parent@example.com',
                'password' => Hash::make('password123'),
            ],
        ];
        
        // Thêm timestamps nếu cần
        if (in_array('created_at', $columns)) {
            foreach ($usersData as &$user) {
                $user['created_at'] = now();
            }
        }
        
        if (in_array('updated_at', $columns)) {
            foreach ($usersData as &$user) {
                $user['updated_at'] = now();
            }
        }
        
        // Thêm dữ liệu vào bảng
        foreach ($usersData as $userData) {
            // Lọc chỉ các cột tồn tại
            $filteredData = array_intersect_key($userData, array_flip($columns));
            DB::table('users')->insert($filteredData);
        }
    }
}