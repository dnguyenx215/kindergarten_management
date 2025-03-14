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
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->date('report_date');
            $table->text('activities')->nullable(); // Các hoạt động tham gia
            $table->text('meals')->nullable(); // Tình hình ăn uống
            $table->text('nap')->nullable(); // Tình hình ngủ trưa
            $table->text('mood')->nullable(); // Tâm trạng
            $table->text('health_notes')->nullable(); // Ghi chú sức khỏe
            $table->text('teacher_notes')->nullable(); // Ghi chú của giáo viên
            $table->foreignId('created_by')->nullable()->references('id')->on('users');
            $table->timestamps();
            
            $table->unique(['student_id', 'report_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
