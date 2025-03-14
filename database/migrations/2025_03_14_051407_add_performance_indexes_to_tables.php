<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Thêm index cho các trường tìm kiếm thường xuyên
        Schema::table('students', function (Blueprint $table) {
            $table->index('student_code');
            $table->index(['first_name', 'last_name']);
            $table->index('parent_phone');
            $table->index('parent_email');
        });

        Schema::table('attendance', function (Blueprint $table) {
            $table->index(['date', 'status']);
        });

        Schema::table('tuition_fees', function (Blueprint $table) {
            $table->index(['due_date', 'paid']);
            $table->index('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }
};
