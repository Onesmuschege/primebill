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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->integer('quantity')->default(0);
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->string('serial_number')->nullable();
            $table->foreignId('assigned_to_client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->enum('status', ['in_stock', 'assigned', 'faulty', 'lost'])->default('in_stock');
            $table->integer('low_stock_alert')->default(5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
