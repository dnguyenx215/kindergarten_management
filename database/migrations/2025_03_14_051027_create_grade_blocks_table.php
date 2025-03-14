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
        Schema::create('grade_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Mã khối: NT (Nhà trẻ), MG (Mẫu giáo)
            $table->string('name'); // Tên khối: Nhà trẻ, Mẫu giáo
            $table->text('description')->nullable();
            $table->timestamps();
        });
        
        // Thêm trường grade_block_id vào bảng classes
        Schema::table('classes', function (Blueprint $table) {
            $table->foreignId('grade_block_id')->nullable()->after('name')
                ->references('id')->on('grade_blocks')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_blocks');
    }
};
