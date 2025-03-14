<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holiday_configurations', function (Blueprint $table) {
            $table->id();
            $table->date('holiday_date'); // Ngày nghỉ
            $table->string('holiday_name')->nullable(); // Tên ngày nghỉ (ví dụ: Tết, lễ 30/4, v.v.)
            $table->enum('holiday_type', ['weekend', 'national', 'school']); // Loại: cuối tuần, nghỉ lễ quốc gia, nghỉ trường
            $table->text('description')->nullable(); // Mô tả thêm
            $table->foreignId('created_by')->nullable()->references('id')->on('users');
            $table->timestamps();
            
            // Ngày nghỉ phải là duy nhất
            $table->unique('holiday_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holiday_configurations');
    }
};