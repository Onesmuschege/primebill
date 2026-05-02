<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mpesa_transactions')) {
            Schema::create('mpesa_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
                $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
                $table->string('phone')->nullable();
                $table->decimal('amount', 10, 2)->nullable();
                $table->string('account_reference')->nullable();

                $table->string('merchant_request_id')->nullable()->index();
                $table->string('checkout_request_id')->nullable()->unique();
                $table->string('mpesa_receipt_number')->nullable()->unique();

                $table->integer('result_code')->nullable();
                $table->string('result_desc')->nullable();
                $table->enum('status', ['pending', 'completed', 'failed'])->default('pending')->index();

                $table->json('raw_request')->nullable();
                $table->json('raw_callback')->nullable();

                $table->string('idempotency_key')->nullable();
                $table->timestamp('idempotency_created_at')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('mpesa_transactions', function (Blueprint $table) {
                if (!Schema::hasColumn('mpesa_transactions', 'idempotency_key')) {
                    $table->string('idempotency_key')->nullable();
                }
                if (!Schema::hasColumn('mpesa_transactions', 'idempotency_created_at')) {
                    $table->timestamp('idempotency_created_at')->nullable();
                }
            });
        }

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'idempotency_key')) {
                $table->string('idempotency_key')->nullable();
            }
            if (!Schema::hasColumn('payments', 'idempotency_created_at')) {
                $table->timestamp('idempotency_created_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'idempotency_key')) {
                $table->dropColumn('idempotency_key');
            }
            if (Schema::hasColumn('payments', 'idempotency_created_at')) {
                $table->dropColumn('idempotency_created_at');
            }
        });

        Schema::dropIfExists('mpesa_transactions');
    }
};