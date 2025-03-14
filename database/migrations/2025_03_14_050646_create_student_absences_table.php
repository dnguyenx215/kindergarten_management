<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->date('start_date'); // Ngày bắt đầu nghỉ
            $table->date('end_date');   // Ngày kết thúc nghỉ (có thể = start_date)
            $table->text('reason')->nullable(); // Lý do nghỉ
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('note')->nullable(); // Ghi chú từ giáo viên
            $table->foreignId('approved_by')->nullable()->references('id')->on('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            // Index theo học sinh và ngày
            $table->index(['student_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_absences');
    }
};