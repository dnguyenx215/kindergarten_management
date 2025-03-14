<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tuition_fees', function (Blueprint $table) {
            // Kiểm tra và thêm cột chỉ khi chưa tồn tại
            if (!Schema::hasColumn('tuition_fees', 'invoice_number')) {
                $table->string('invoice_number')->after('id')->nullable();
            }
            
            if (!Schema::hasColumn('tuition_fees', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('paid')
                    ->constrained('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('tuition_fees', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('created_by')
                    ->constrained('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('tuition_fees', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            
            if (!Schema::hasColumn('tuition_fees', 'payment_method')) {
                $table->enum('payment_method', ['cash', 'transfer', 'other'])
                    ->default('cash')->after('paid');
            }
            
            if (!Schema::hasColumn('tuition_fees', 'payment_note')) {
                $table->string('payment_note')->nullable()->after('payment_method');
            }
            
            if (!Schema::hasColumn('tuition_fees', 'semester_id')) {
                $table->foreignId('semester_id')->nullable()->after('student_id')
                    ->constrained('semesters')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tuition_fees', function (Blueprint $table) {
            // Chỉ xóa các cột nếu tồn tại
            if (Schema::hasColumn('tuition_fees', 'created_by')) {
                $table->dropForeignIdFor(\App\Models\User::class, 'created_by');
            }
            
            if (Schema::hasColumn('tuition_fees', 'approved_by')) {
                $table->dropForeignIdFor(\App\Models\User::class, 'approved_by');
            }
            
            if (Schema::hasColumn('tuition_fees', 'semester_id')) {
                $table->dropForeignIdFor(\App\Models\Semester::class, 'semester_id');
            }
            
            $table->dropColumns([
                'invoice_number',
                'created_by',
                'approved_by',
                'approved_at',
                'payment_method',
                'payment_note',
                'semester_id'
            ]);
        });
    }
};