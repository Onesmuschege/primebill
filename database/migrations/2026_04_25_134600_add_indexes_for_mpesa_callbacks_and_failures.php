<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mpesa_callbacks', function (Blueprint $table) {
            $table->index('payment_id');
            $table->index('created_at');
        });

        Schema::table('payment_failures', function (Blueprint $table) {
            $table->index('payment_id');
            $table->index('created_at');
        });

        Schema::table('mpesa_transactions', function (Blueprint $table) {
            $table->index(['invoice_id', 'status']);
            $table->index(['client_id', 'status']);
            $table->index('created_at');
        });

        Schema::table('mikrotik_sync_logs', function (Blueprint $table) {
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        // Minimal rollback (see earlier migration note).
    }
};

