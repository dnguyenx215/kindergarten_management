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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên lớp
            $table->integer('capacity'); // Sĩ số tối đa
            $table->unsignedBigInteger('homeroom_teacher_id')->nullable(); // ID của giáo viên chủ nhiệm
            $table->timestamps();
            $table->foreign('homeroom_teacher_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
