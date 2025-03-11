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
        Schema::create('tuition_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade'); // Khóa ngoại đến bảng students
            $table->decimal('amount', 10, 2); // Số tiền học phí
            $table->date('due_date'); // Ngày đến hạn
            $table->boolean('paid')->default(false); // Trạng thái thanh toán
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tuition_fees');
    }
};
