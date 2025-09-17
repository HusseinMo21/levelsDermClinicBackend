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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('model_type'); // e.g., 'App\\Models\\Patient'
            $table->unsignedBigInteger('model_id');
            $table->string('action'); // e.g., 'created', 'updated', 'deleted'
            $table->json('old_values')->nullable(); // Store old values as JSON
            $table->json('new_values')->nullable(); // Store new values as JSON
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['model_type', 'model_id']);
            $table->index(['user_id', 'created_at']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
