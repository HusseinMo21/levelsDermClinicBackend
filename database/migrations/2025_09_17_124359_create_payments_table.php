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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id')->unique(); // Custom payment ID (e.g., PAY001)
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'insurance', 'installment'])->default('cash');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded', 'partially_refunded'])->default('pending');
            $table->string('transaction_id')->nullable(); // External transaction ID
            $table->text('payment_notes')->nullable();
            $table->text('refund_reason')->nullable();
            $table->datetime('payment_date');
            $table->foreignId('processed_by')->constrained('users'); // Who processed the payment
            $table->timestamps();
            
            $table->index(['patient_id', 'payment_date']);
            $table->index(['appointment_id']);
            $table->index('status');
            $table->index('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
