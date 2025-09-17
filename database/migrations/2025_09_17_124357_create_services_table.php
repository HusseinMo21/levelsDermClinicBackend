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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('service_code')->unique(); // Custom service code (e.g., SRV001)
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category'); // e.g., 'Consultation', 'Treatment', 'Procedure'
            $table->string('subcategory')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('duration_minutes')->default(30); // Service duration
            $table->text('requirements')->nullable(); // Pre-treatment requirements
            $table->text('aftercare_instructions')->nullable();
            $table->json('contraindications')->nullable(); // Store as JSON array
            $table->boolean('requires_consultation')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            $table->index('category');
            $table->index('name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
