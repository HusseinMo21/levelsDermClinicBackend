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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('doctor_id')->unique(); // Custom doctor ID (e.g., DOC001)
            $table->string('license_number')->unique();
            $table->string('specialization');
            $table->text('qualifications')->nullable();
            $table->integer('experience_years')->default(0);
            $table->decimal('consultation_fee', 10, 2)->default(0);
            $table->text('bio')->nullable();
            $table->string('profile_image')->nullable();
            $table->json('working_hours')->nullable(); // Store working hours as JSON
            $table->json('available_days')->nullable(); // Store available days as JSON
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('license_number');
            $table->index('specialization');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
