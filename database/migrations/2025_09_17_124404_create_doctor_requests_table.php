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
        Schema::create('doctor_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_id')->unique(); // Custom request ID (e.g., REQ001)
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('inventory_batch_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('quantity_requested');
            $table->integer('quantity_provided')->default(0);
            $table->text('purpose')->nullable(); // Why the item is needed
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'fulfilled', 'rejected', 'cancelled'])->default('pending');
            $table->datetime('requested_at');
            $table->datetime('fulfilled_at')->nullable();
            $table->foreignId('fulfilled_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->index(['doctor_id', 'status']);
            $table->index(['inventory_item_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_requests');
    }
};
