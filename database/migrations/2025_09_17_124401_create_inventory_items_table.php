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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique(); // Custom item code (e.g., ITM001)
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category'); // e.g., 'Medication', 'Equipment', 'Supplies'
            $table->string('subcategory')->nullable();
            $table->string('unit_of_measure'); // e.g., 'pieces', 'ml', 'grams'
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->integer('minimum_stock_level')->default(0);
            $table->integer('maximum_stock_level')->nullable();
            $table->boolean('has_expiry_date')->default(false);
            $table->integer('shelf_life_days')->nullable(); // Days before expiry
            $table->boolean('requires_prescription')->default(false);
            $table->text('storage_conditions')->nullable();
            $table->text('usage_instructions')->nullable();
            $table->json('contraindications')->nullable(); // Store as JSON array
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
        Schema::dropIfExists('inventory_items');
    }
};
