<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->date('date'); // Ngày báo ăn
            $table->integer('breakfast_count')->default(0); // Số bữa sáng
            $table->integer('lunch_count')->default(0); // Số bữa trưa
            $table->integer('dinner_count')->default(0); // Số bữa tối nếu có
            $table->foreignId('registered_by')->nullable()->references('id')->on('users');
            $table->timestamps();
            
            // Tạo index để tìm kiếm nhanh
            $table->index(['class_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_registrations');
    }
};