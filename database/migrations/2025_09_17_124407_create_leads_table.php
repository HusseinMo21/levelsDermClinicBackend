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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_id')->unique(); // Custom lead ID (e.g., LEA001)
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_2')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('US');
            $table->enum('source', ['website', 'referral', 'social_media', 'advertisement', 'walk_in', 'phone_call', 'other'])->default('website');
            $table->string('source_details')->nullable(); // Specific details about the source
            $table->text('interested_services')->nullable(); // Services they're interested in
            $table->text('notes')->nullable();
            $table->enum('status', ['new', 'contacted', 'qualified', 'converted', 'lost', 'do_not_contact'])->default('new');
            $table->integer('priority')->default(1); // 1=Low, 2=Medium, 3=High
            $table->datetime('last_contact_date')->nullable();
            $table->datetime('next_follow_up_date')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users'); // Customer service rep
            $table->foreignId('converted_to_patient')->nullable()->constrained('patients');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            $table->index(['status', 'assigned_to']);
            $table->index('source');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
