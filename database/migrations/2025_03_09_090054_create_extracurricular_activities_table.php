<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('extracurricular_activities', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên hoạt động
            $table->text('description'); // Mô tả hoạt động
            $table->date('date'); // Ngày tổ chức
            $table->foreignId('class_id')->constrained()->onDelete('cascade'); // Khóa ngoại đến bảng classes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extracurricular_activities');
    }
};
