<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_items', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên khoản thu (học phí, tiền ăn, đồng phục...)
            $table->decimal('amount', 12, 2); // Số tiền
            $table->text('description')->nullable(); // Mô tả
            $table->boolean('required')->default(true); // Bắt buộc hay không
            $table->boolean('active')->default(true); // Có áp dụng không
            $table->timestamps();
        });

        Schema::create('tuition_fee_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tuition_fee_id')->constrained()->onDelete('cascade');
            $table->foreignId('fee_item_id')->constrained('fee_items')->onDelete('cascade');
            $table->decimal('amount', 12, 2); // Số tiền cụ thể cho học sinh này
            $table->integer('quantity')->default(1); // Số lượng (nếu áp dụng)
            $table->timestamps();
            
            // Mỗi loại phí chỉ xuất hiện một lần trong một phiếu thu
            $table->unique(['tuition_fee_id', 'fee_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tuition_fee_items');
        Schema::dropIfExists('fee_items');
    }
};