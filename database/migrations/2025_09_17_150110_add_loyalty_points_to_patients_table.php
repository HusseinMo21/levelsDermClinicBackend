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
        Schema::table('patients', function (Blueprint $table) {
            $table->integer('loyalty_points')->default(0)->after('visit_count');
            $table->datetime('last_loyalty_points_used')->nullable()->after('loyalty_points');
            $table->datetime('last_activity')->nullable()->after('last_loyalty_points_used');
            $table->datetime('first_visit_date')->nullable()->after('last_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['loyalty_points', 'last_loyalty_points_used', 'last_activity', 'first_visit_date']);
        });
    }
};