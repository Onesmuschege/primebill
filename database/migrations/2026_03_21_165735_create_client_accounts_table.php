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
        Schema::create('client_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('ip_address')->nullable();
            $table->string('mac_address')->nullable();
            $table->enum('type', ['prepaid', 'postpaid'])->default('prepaid');
            $table->enum('status', ['active', 'inactive', 'suspended', 'expired'])->default('active');
            $table->timestamp('expiry_date')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_accounts');
    }
};
