<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Schema;

class CreatePaymentIdempotencyAndSecurityFeatures extends Migration
{
    public function up()
    {
        // New table for tracking M-Pesa callbacks
        Schema::create('mpesa_callbacks', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id');
            $table->string('callback_data');
            $table->timestamps();
        });

        // Table for recording payment failures
        Schema::create('payment_failures', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id');
            $table->string('error_message');
            $table->timestamps();
        });

        // Table for tracking invoice cycles
        Schema::create('invoice_cycles', function (Blueprint $table) {
            $table->id();
            $table->integer('invoice_id');
            $table->dateTime('cycle_start');
            $table->dateTime('cycle_end');
            $table->timestamps();
        });

        // Table for MikroTik sync logs
        Schema::create('mikrotik_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_message');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mpesa_callbacks');
        Schema::dropIfExists('payment_failures');
        Schema::dropIfExists('invoice_cycles');
        Schema::dropIfExists('mikrotik_sync_logs');
    }
}