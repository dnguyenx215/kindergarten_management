<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_monitorings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->decimal('height', 5, 2)->nullable(); // Chiều cao (cm)
            $table->decimal('weight', 5, 2)->nullable(); // Cân nặng (kg)
            $table->text('health_note')->nullable(); // Ghi chú sức khỏe
            $table->boolean('is_sick')->default(false); // Báo ốm
            $table->text('sickness_description')->nullable(); // Mô tả bệnh tình
            $table->foreignId('recorded_by')->nullable()->references('id')->on('users');
            $table->timestamps();
            
            // Index theo học sinh và ngày
            $table->index(['student_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_monitorings');
    }
};