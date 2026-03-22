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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['hotspot', 'pppoe', 'static'])->default('pppoe');
            $table->integer('speed_up')->comment('Upload speed in Kbps');
            $table->integer('speed_down')->comment('Download speed in Kbps');
            $table->integer('burst_up')->nullable();
            $table->integer('burst_down')->nullable();
            $table->integer('fup_limit')->nullable()->comment('FUP limit in MB');
            $table->integer('fup_speed_up')->nullable();
            $table->integer('fup_speed_down')->nullable();
            $table->integer('validity_days')->default(30);
            $table->decimal('price', 10, 2);
            $table->foreignId('router_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
