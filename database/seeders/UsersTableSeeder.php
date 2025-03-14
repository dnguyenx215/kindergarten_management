<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'John',
                'email' => 'teacher@example.com',
                'password' => Hash::make('password123'),
                'role' => 'teacher',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jone',
                'email' => 'staff@example.com',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lisa',
                'email' => 'parent@example.com',
                'password' => Hash::make('password123'),
                'role' => 'parent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
