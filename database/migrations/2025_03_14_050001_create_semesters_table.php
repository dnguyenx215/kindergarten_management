<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên học kỳ (Học kỳ 1, Học kỳ 2)
            $table->string('school_year'); // Năm học (2023-2024)
            $table->date('start_date'); // Ngày bắt đầu
            $table->date('end_date'); // Ngày kết thúc
            $table->boolean('current')->default(false); // Học kỳ hiện tại
            $table->timestamps();
            
            // Index và unique
            $table->unique(['name', 'school_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};