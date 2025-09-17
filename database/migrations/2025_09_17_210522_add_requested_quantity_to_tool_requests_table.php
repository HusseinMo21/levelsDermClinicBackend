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
            // Add requested_quantity column if it doesn't exist
            if (!Schema::hasColumn('tool_requests', 'requested_quantity')) {
                $table->integer('requested_quantity')->default(0)->after('inventory_item_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tool_requests', function (Blueprint $table) {
            $table->dropColumn('requested_quantity');
        });
    }
};