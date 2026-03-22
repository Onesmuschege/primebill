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
        Schema::create('radius_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->foreignId('client_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address')->nullable();
            $table->bigInteger('bytes_in')->default(0);
            $table->bigInteger('bytes_out')->default(0);
            $table->timestamp('session_start')->nullable();
            $table->timestamp('session_stop')->nullable();
            $table->enum('status', ['active', 'stopped'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radius_sessions');
    }
};
