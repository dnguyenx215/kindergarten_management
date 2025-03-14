<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('students')->insert([
            [
                'student_code' => Str::random(10),
                'first_name' => 'Nguyen',
                'last_name' => 'Van A',
                'birthday' => '2010-05-15',
                'gender' => 'male',
                'address' => '123 Main St, City',
                'parent_name' => 'Nguyen Van B',
                'parent_phone' => '0123456789',
                'parent_email' => 'parentA@example.com',
                'class_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_code' => Str::random(10),
                'first_name' => 'Tran',
                'last_name' => 'Thi B',
                'birthday' => '2011-08-21',
                'gender' => 'female',
                'address' => '456 Side St, City',
                'parent_name' => 'Tran Van C',
                'parent_phone' => '0987654321',
                'parent_email' => 'parentB@example.com',
                'class_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_code' => Str::random(10),
                'first_name' => 'Le',
                'last_name' => 'Van C',
                'birthday' => '2012-03-10',
                'gender' => 'male',
                'address' => '789 West St, City',
                'parent_name' => 'Le Van D',
                'parent_phone' => '0345678901',
                'parent_email' => 'parentC@example.com',
                'class_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_code' => Str::random(10),
                'first_name' => 'Pham',
                'last_name' => 'Thi D',
                'birthday' => '2013-07-19',
                'gender' => 'female',
                'address' => '101 North St, City',
                'parent_name' => 'Pham Van E',
                'parent_phone' => '0456789012',
                'parent_email' => 'parentD@example.com',
                'class_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_code' => Str::random(10),
                'first_name' => 'Hoang',
                'last_name' => 'Van E',
                'birthday' => '2014-11-25',
                'gender' => 'male',
                'address' => '202 South St, City',
                'parent_name' => 'Hoang Van F',
                'parent_phone' => '0567890123',
                'parent_email' => 'parentE@example.com',
                'class_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
