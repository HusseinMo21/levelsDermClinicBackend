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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_id')->unique(); // Custom appointment ID (e.g., APT001)
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->datetime('appointment_date');
            $table->datetime('end_time');
            $table->enum('status', ['scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->enum('type', ['consultation', 'treatment', 'follow_up', 'emergency'])->default('consultation');
            $table->text('notes')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('prescription')->nullable();
            $table->json('before_photos')->nullable(); // Store photo URLs as JSON array
            $table->json('after_photos')->nullable(); // Store photo URLs as JSON array
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->boolean('payment_required')->default(true);
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('created_by')->constrained('users'); // Who created the appointment
            $table->foreignId('updated_by')->nullable()->constrained('users'); // Who last updated
            $table->timestamps();
            
            $table->index(['appointment_date', 'doctor_id']);
            $table->index(['appointment_date', 'patient_id']);
            $table->index('status');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
