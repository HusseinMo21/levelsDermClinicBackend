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
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->integer('current_stock')->default(0)->after('unit_cost');
            $table->decimal('unit_price', 10, 2)->nullable()->after('current_stock');
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active')->after('unit_price');
            $table->string('storage_location')->nullable()->after('status');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null')->after('storage_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn([
                'current_stock',
                'unit_price',
                'status',
                'storage_location',
                'supplier_id'
            ]);
        });
    }
};