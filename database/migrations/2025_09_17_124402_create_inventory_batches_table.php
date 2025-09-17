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
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
            $table->string('batch_number')->unique(); // e.g., 'BATCH001'
            $table->string('supplier_batch_number')->nullable(); // Supplier's batch number
            $table->integer('quantity_received');
            $table->integer('quantity_remaining');
            $table->integer('quantity_used')->default(0);
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total_cost', 10, 2);
            $table->enum('status', ['active', 'expired', 'depleted', 'recalled'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->constrained('users');
            $table->timestamps();
            
            $table->index(['inventory_item_id', 'expiry_date']);
            $table->index('batch_number');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
    }
};
