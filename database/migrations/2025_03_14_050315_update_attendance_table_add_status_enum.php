<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            // Thêm cột status_new
            $table->enum('status_new', ['present', 'absent_excused', 'absent_unexcused'])
                ->nullable()
                ->after('date');
        });

        // Thực hiện update dữ liệu sau khi cột đã được tạo
        DB::statement('UPDATE attendance SET status_new = CASE WHEN present = 1 THEN "present" ELSE "absent_unexcused" END');

        Schema::table('attendance', function (Blueprint $table) {
            // Xóa cột present
            $table->dropColumn('present');
            
            // Đổi tên cột status_new thành status
            $table->renameColumn('status_new', 'status');
            
            // Thêm cột reason
            $table->text('absence_reason')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            // Xóa cột absence_reason
            $table->dropColumn('absence_reason');
            
            // Đổi tên status về status_new
            $table->renameColumn('status', 'status_new');
            
            // Thêm lại cột present
            $table->boolean('present')->default(false)->after('date');
        });

        // Cập nhật giá trị present từ status
        DB::statement('UPDATE attendance SET present = CASE WHEN status_new = "present" THEN 1 ELSE 0 END');

        Schema::table('attendance', function (Blueprint $table) {
            // Xóa cột status_new
            $table->dropColumn('status_new');
        });
    }
};