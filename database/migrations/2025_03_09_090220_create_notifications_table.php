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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Tiêu đề thông báo
            $table->text('message'); // Nội dung thông báo
            $table->unsignedBigInteger('sender_id')->nullable(); // ID người gửi (ví dụ: admin)
            $table->timestamp('schedule_at')->nullable(); // Thời gian dự kiến gửi
            $table->timestamp('sent_at')->nullable(); // Thời gian thực tế gửi
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending'); // Trạng thái thông báo
            $table->timestamps();
        });
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
