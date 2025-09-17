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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type'); // e.g., 'low_stock', 'expiry_alert', 'appointment_reminder'
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data as JSON
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['unread', 'read', 'dismissed'])->default('unread');
            $table->datetime('read_at')->nullable();
            $table->datetime('scheduled_at')->nullable(); // For scheduled notifications
            $table->boolean('is_system_notification')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('type');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
