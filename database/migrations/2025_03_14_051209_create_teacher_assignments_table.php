<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_assignments', function (Blueprint $table) {
            // Kiểm tra và thêm cột nếu chưa tồn tại
            if (!Schema::hasColumn('teacher_assignments', 'academic_year_id')) {
                $table->foreignId('academic_year_id')->nullable()
                    ->constrained('academic_years')->onDelete('set null');
            }

            // Thêm các ràng buộc duy nhất nếu chưa tồn tại
            if (!$this->constraintExists('teacher_homeroom_unique')) {
                $table->unique(['user_id', 'is_homeroom_teacher', 'academic_year_id'], 
                               'teacher_homeroom_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('teacher_assignments', function (Blueprint $table) {
            // Xóa khóa ngoại và ràng buộc duy nhất nếu tồn tại
            if (Schema::hasColumn('teacher_assignments', 'academic_year_id')) {
                $table->dropForeignIdFor(\App\Models\AcademicYear::class, 'academic_year_id');
            }

            if ($this->constraintExists('teacher_homeroom_unique')) {
                $table->dropUnique('teacher_homeroom_unique');
            }
        });
    }

    /**
     * Kiểm tra xem ràng buộc có tồn tại không
     */
    private function constraintExists($constraintName)
    {
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();
        $tableName = 'teacher_assignments';

        $check = $connection->select(
            "SELECT * FROM information_schema.TABLE_CONSTRAINTS 
             WHERE CONSTRAINT_SCHEMA = ? 
             AND TABLE_NAME = ? 
             AND CONSTRAINT_NAME = ?",
            [$databaseName, $tableName, $constraintName]
        );

        return count($check) > 0;
    }
};