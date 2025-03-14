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
        Schema::table('system_settings', function (Blueprint $table) {
            // Thêm các trường mới
            $table->string('school_name')->nullable()->after('id');
            $table->string('school_address')->nullable()->after('school_name');
            $table->string('school_phone')->nullable()->after('school_address');
            $table->string('school_email')->nullable()->after('school_phone');
            $table->string('principal_name')->nullable()->after('school_email');
            $table->string('school_logo')->nullable()->after('principal_name');
            $table->json('working_hours')->nullable()->after('holidays');
            $table->json('payment_methods')->nullable()->after('working_hours');
            $table->json('notification_settings')->nullable()->after('payment_methods');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
