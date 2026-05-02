<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpesa_callbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->json('callback_data');
            $table->timestamps();
        });

        Schema::create('payment_failures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('error_message');
            $table->json('context')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->dateTime('cycle_start');
            $table->dateTime('cycle_end');
            $table->timestamps();

            $table->unique(['invoice_id', 'cycle_start', 'cycle_end']);
        });

        Schema::create('mikrotik_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->text('log_message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpesa_callbacks');
        Schema::dropIfExists('payment_failures');
        Schema::dropIfExists('invoice_cycles');
        Schema::dropIfExists('mikrotik_sync_logs');
    }
};