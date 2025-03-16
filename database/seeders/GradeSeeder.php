<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('grade_blocks')->insert([
            [
                'code' => 'NT',
                'name' => 'Nhà trẻ',
                'description' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'MG',
                'name' => 'Mẫu giáo',
                'description' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ]
            
        ]);
    }
}
