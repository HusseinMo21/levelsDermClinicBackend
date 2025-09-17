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
        // Add national_id and visit_count to patients table
        Schema::table('patients', function (Blueprint $table) {
            if (!Schema::hasColumn('patients', 'national_id')) {
                $table->string('national_id')->unique()->after('patient_id');
            }
            if (!Schema::hasColumn('patients', 'visit_count')) {
                $table->integer('visit_count')->default(0)->after('status');
            }
        });

        // Add operation_number and discount_amount to appointments table
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'operation_number')) {
                $table->string('operation_number')->unique()->after('appointment_id');
            }
            if (!Schema::hasColumn('appointments', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('total_amount');
            }
        });

        // Add payment_source to payments table
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'payment_source')) {
                $table->string('payment_source')->nullable()->after('payment_notes');
            }
        });

        // Add specialization to services table
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'specialization')) {
                $table->string('specialization')->nullable()->after('category');
            }
        });

        // Add additional fields to users table if they don't exist
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'gender')) {
                $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            }
            if (!Schema::hasColumn('users', 'profile_image')) {
                $table->string('profile_image')->nullable()->after('gender');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('profile_image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['national_id', 'visit_count']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['operation_number', 'discount_amount']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('payment_source');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('specialization');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'address', 'date_of_birth', 'gender', 'profile_image', 'is_active']);
        });
    }
};
