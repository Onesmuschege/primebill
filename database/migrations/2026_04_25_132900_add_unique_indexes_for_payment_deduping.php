<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'mpesa_code')) {
                return;
            }

            // MySQL unique index allows multiple NULLs; good for non-mpesa methods.
            $table->unique('mpesa_code');
            $table->index(['method', 'reference']);
            $table->index(['invoice_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['mpesa_code']);
            $table->dropIndex(['method', 'reference']);
            $table->dropIndex(['invoice_id', 'status']);
        });
    }
};

