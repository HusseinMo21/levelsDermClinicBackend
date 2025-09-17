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
        Schema::table('tool_requests', function (Blueprint $table) {
            // Add quantity column if it doesn't exist
            if (!Schema::hasColumn('tool_requests', 'quantity')) {
                $table->integer('quantity')->default(0)->after('inventory_item_id');
            }
            
            // Add processed_by column if it doesn't exist
            if (!Schema::hasColumn('tool_requests', 'processed_by')) {
                $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null')->after('status');
            }
            
            // Add processed_at column if it doesn't exist
            if (!Schema::hasColumn('tool_requests', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('processed_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tool_requests', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'processed_by', 'processed_at']);
        });
    }
};