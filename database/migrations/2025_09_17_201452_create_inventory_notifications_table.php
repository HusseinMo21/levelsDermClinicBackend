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
        Schema::create('inventory_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['withdrawal', 'supply_request', 'low_stock', 'expired_item', 'new_request'])->default('withdrawal');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->boolean('is_read')->default(false);
            $table->json('metadata')->nullable(); // Additional data like item_id, quantity, etc.
            $table->foreignId('related_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('related_item_id')->nullable()->constrained('inventory_items')->onDelete('set null');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_notifications');
    }
};