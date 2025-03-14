<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Tắt các ràng buộc khóa ngoại tạm thời
        Schema::disableForeignKeyConstraints();
        
        // Chạy các seeders
        $this->call([
            RolesTableSeeder::class,
            UsersTableSeeder::class,
            UserRolesTableSeeder::class,
        ]);
        
        // Bật lại các ràng buộc khóa ngoại
        Schema::enableForeignKeyConstraints();
    }
}