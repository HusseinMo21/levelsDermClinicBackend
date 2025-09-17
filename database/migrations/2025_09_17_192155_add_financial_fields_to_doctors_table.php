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
        Schema::table('doctors', function (Blueprint $table) {
            $table->decimal('monthly_salary', 10, 2)->nullable()->after('consultation_fee');
            $table->decimal('detection_value', 10, 2)->nullable()->after('monthly_salary');
            $table->decimal('doctor_percentage', 5, 2)->nullable()->after('detection_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['monthly_salary', 'detection_value', 'doctor_percentage']);
        });
    }
};