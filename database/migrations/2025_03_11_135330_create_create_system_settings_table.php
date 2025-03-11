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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_year')->nullable(); // Năm học
            $table->decimal('tuition_fee', 10, 2)->nullable(); // Mức học phí chung
            $table->text('school_info')->nullable(); // Thông tin trường học
            $table->json('holidays')->nullable(); // Danh sách ngày nghỉ (Lưu dưới dạng JSON)
            $table->timestamps();
        });

        DB::table('system_settings')->insert([
            'school_year' => '2024-2025',
            'tuition_fee' => 1000000, // Ví ụ: 1 triệu VND
            'school_info' => 'Trường Tiểu học ABC, Quận 1, TP.HCM',
            'holidays'    => json_encode(['2024-09-02', '2025-01-01']), // Ngày lễ mặc định
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('create_system_settings');
    }
};
