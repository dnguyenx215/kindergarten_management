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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_code')->unique()->nullable(); // Mã học sinh (nếu có)
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birthday')->nullable();
            $table->string('gender')->nullable(); // 'male', 'female', ...
            $table->string('address')->nullable();
            $table->string('parent_name')->nullable();  // tên phụ huynh
            $table->string('parent_phone')->nullable();
            $table->string('parent_email')->nullable();
            $table->unsignedBigInteger('class_id')->nullable(); 
            $table->timestamps();
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('set null');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
