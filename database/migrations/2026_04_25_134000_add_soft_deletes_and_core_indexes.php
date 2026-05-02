<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Soft deletes (requested): clients, invoices, payments.
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'deleted_at')) {
                $table->softDeletes();
            }
            $table->index(['status', 'created_at']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'deleted_at')) {
                $table->softDeletes();
            }
            $table->index(['client_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('due_date');
            $table->index('paid_at');
        });

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'deleted_at')) {
                $table->softDeletes();
            }
            $table->index(['client_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['method', 'created_at']);
        });

        // Client accounts: common lookups by client/status/expiry.
        Schema::table('client_accounts', function (Blueprint $table) {
            $table->index(['client_id', 'status']);
            $table->index(['username']);
            $table->index(['expiry_date']);
        });

        // Tickets: dashboards frequently filter by status/priority/assignee.
        Schema::table('tickets', function (Blueprint $table) {
            $table->index(['client_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['priority', 'status']);
            $table->index('created_at');
        });

        Schema::table('ticket_replies', function (Blueprint $table) {
            $table->index(['ticket_id', 'created_at']);
        });

        Schema::table('routers', function (Blueprint $table) {
            $table->index(['status', 'last_seen']);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->index(['is_active', 'type']);
            $table->index('router_id');
        });

        Schema::table('sms_logs', function (Blueprint $table) {
            $table->index(['client_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('phone');
        });

        Schema::table('expenditures', function (Blueprint $table) {
            $table->index('date');
            $table->index(['category', 'date']);
            $table->index('created_at');
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->index(['status', 'category']);
            $table->index('assigned_to_client_id');
        });

        Schema::table('network_traffic', function (Blueprint $table) {
            $table->index(['router_id', 'recorded_at']);
            $table->index('recorded_at');
        });

        Schema::table('radius_sessions', function (Blueprint $table) {
            $table->index(['client_account_id', 'status']);
            $table->index(['username', 'status']);
            $table->index('session_start');
            $table->index('session_stop');
            $table->index('created_at');
        });

        Schema::table('sales_commissions', function (Blueprint $table) {
            $table->index(['user_id', 'status']);
            $table->index(['client_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        Schema::table('fup_logs', function (Blueprint $table) {
            $table->index(['client_account_id', 'triggered_at']);
            $table->index('created_at');
        });

        Schema::table('system_logs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
            $table->index(['model', 'model_id']);
            $table->index('created_at');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'read_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        // Intentionally minimal rollback to avoid accidental index-name mismatches across DB engines.
        // If you need reversible drops, we can name every index explicitly and drop by name.
    }
};

