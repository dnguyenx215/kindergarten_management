<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('classes')->insert([
            [
                'name' => 'Class A',
                'capacity' => 30,
                'homeroom_teacher_id' => 2, // ID của giáo viên chủ nhiệm (cần chắc chắn tồn tại trong bảng users)
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Class B',
                'capacity' => 25,
                'homeroom_teacher_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Class C',
                'capacity' => 35,
                'homeroom_teacher_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
