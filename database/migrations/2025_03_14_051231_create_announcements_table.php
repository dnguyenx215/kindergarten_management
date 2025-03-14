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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('type', ['general', 'event', 'holiday', 'important'])->default('general');
            $table->enum('target', ['all', 'teachers', 'parents', 'staff'])->default('all');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_published')->default(true);
            $table->foreignId('created_by')->nullable()->references('id')->on('users');
            $table->timestamps();
            
            $table->index(['type', 'target', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
