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
        Schema::create('student_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('activity_date');
            $table->string('activity_type')->nullable(); // loại hoạt động: vui chơi, học tập, nghệ thuật...
            $table->text('teacher_comment')->nullable();
            $table->foreignId('recorded_by')->nullable()->references('id')->on('users');
            $table->timestamps();
            
            $table->index(['student_id', 'activity_date']);
            $table->index(['class_id', 'activity_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_activities');
    }
};
