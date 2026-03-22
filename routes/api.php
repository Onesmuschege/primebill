<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\RouterController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\MpesaController;
use App\Http\Controllers\Api\SmsController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExpenditureController;
use App\Http\Controllers\Api\CommissionController;
use App\Http\Controllers\Api\InventoryController;

// M-Pesa callbacks (NO auth - Safaricom hits these directly)
Route::prefix('mpesa')->group(function () {
    Route::post('/stk-callback', [MpesaController::class, 'stkCallback']);
    Route::post('/c2b-validation', [MpesaController::class, 'c2bValidation']);
    Route::post('/c2b-confirmation', [MpesaController::class, 'c2bConfirmation']);
});

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });

    // Clients
    Route::prefix('clients')->group(function () {
        Route::get('/', [ClientController::class, 'index']);
        Route::post('/', [ClientController::class, 'store']);
        Route::get('/{client}', [ClientController::class, 'show']);
        Route::put('/{client}', [ClientController::class, 'update']);
        Route::delete('/{client}', [ClientController::class, 'destroy']);
        Route::get('/{client}/accounts', [ClientController::class, 'accounts']);
        Route::get('/{client}/invoices', [ClientController::class, 'invoices']);
        Route::get('/{client}/payments', [ClientController::class, 'payments']);
        Route::get('/{client}/tickets', [ClientController::class, 'tickets']);
        Route::post('/{client}/suspend', [ClientController::class, 'suspend']);
        Route::post('/{client}/activate', [ClientController::class, 'activate']);
    });

    // Plans
    Route::prefix('plans')->group(function () {
        Route::get('/', [PlanController::class, 'index']);
        Route::post('/', [PlanController::class, 'store']);
        Route::get('/{plan}', [PlanController::class, 'show']);
        Route::put('/{plan}', [PlanController::class, 'update']);
        Route::delete('/{plan}', [PlanController::class, 'destroy']);
        Route::get('/{plan}/clients', [PlanController::class, 'clients']);
    });

    // Routers
    Route::prefix('routers')->group(function () {
        Route::get('/', [RouterController::class, 'index']);
        Route::post('/', [RouterController::class, 'store']);
        Route::get('/{router}', [RouterController::class, 'show']);
        Route::put('/{router}', [RouterController::class, 'update']);
        Route::delete('/{router}', [RouterController::class, 'destroy']);
        Route::post('/{router}/test-connection', [RouterController::class, 'testConnection']);
        Route::get('/{router}/resources', [RouterController::class, 'resources']);
        Route::get('/{router}/sessions', [RouterController::class, 'sessions']);
    });

    // Invoices
    Route::prefix('invoices')->group(function () {
        Route::get('/', [InvoiceController::class, 'index']);
        Route::post('/', [InvoiceController::class, 'store']);
        Route::post('/bulk-generate', [InvoiceController::class, 'bulkGenerate']);
        Route::get('/{invoice}', [InvoiceController::class, 'show']);
        Route::put('/{invoice}', [InvoiceController::class, 'update']);
        Route::delete('/{invoice}', [InvoiceController::class, 'destroy']);
    });

    // Payments
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);
        Route::post('/', [PaymentController::class, 'store']);
        Route::get('/summary', [PaymentController::class, 'summary']);
        Route::get('/{payment}', [PaymentController::class, 'show']);
        Route::delete('/{payment}', [PaymentController::class, 'destroy']);
    });

    // M-Pesa protected
    Route::prefix('mpesa')->group(function () {
        Route::post('/stk-push', [MpesaController::class, 'stkPush']);
    });

    // SMS
    Route::prefix('sms')->group(function () {
        Route::post('/send', [SmsController::class, 'send']);
        Route::post('/send-bulk', [SmsController::class, 'sendBulk']);
        Route::get('/logs', [SmsController::class, 'logs']);
        Route::get('/balance', [SmsController::class, 'balance']);
        Route::get('/templates', [SmsController::class, 'templates']);
    });

    // Tickets
    Route::prefix('tickets')->group(function () {
        Route::get('/stats', [TicketController::class, 'stats']);
        Route::get('/', [TicketController::class, 'index']);
        Route::post('/', [TicketController::class, 'store']);
        Route::get('/{ticket}', [TicketController::class, 'show']);
        Route::put('/{ticket}', [TicketController::class, 'update']);
        Route::post('/{ticket}/reply', [TicketController::class, 'reply']);
        Route::post('/{ticket}/assign', [TicketController::class, 'assign']);
        Route::post('/{ticket}/close', [TicketController::class, 'close']);
        Route::post('/{ticket}/escalate', [TicketController::class, 'escalate']);
    });

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'stats']);
        Route::get('/traffic', [DashboardController::class, 'traffic']);
        Route::get('/top-downloaders', [DashboardController::class, 'topDownloaders']);
    });

    // Analytics
    Route::prefix('analytics')->group(function () {
        Route::get('/income', [DashboardController::class, 'incomeAnalytics']);
    });

    // Expenditures
    Route::prefix('expenditures')->group(function () {
        Route::get('/summary', [ExpenditureController::class, 'summary']);
        Route::get('/categories', [ExpenditureController::class, 'categories']);
        Route::get('/', [ExpenditureController::class, 'index']);
        Route::post('/', [ExpenditureController::class, 'store']);
        Route::get('/{expenditure}', [ExpenditureController::class, 'show']);
        Route::put('/{expenditure}', [ExpenditureController::class, 'update']);
        Route::delete('/{expenditure}', [ExpenditureController::class, 'destroy']);
    });

    // Commissions
    Route::prefix('commissions')->group(function () {
        Route::get('/', [CommissionController::class, 'index']);
        Route::get('/summary', [CommissionController::class, 'summary']);
        Route::post('/{commission}/approve', [CommissionController::class, 'approve']);
        Route::post('/{commission}/pay', [CommissionController::class, 'pay']);
    });

    // Inventory
    Route::prefix('inventory')->group(function () {
        Route::get('/low-stock', [InventoryController::class, 'lowStock']);
        Route::get('/assigned', [InventoryController::class, 'assigned']);
        Route::get('/summary', [InventoryController::class, 'summary']);
        Route::get('/', [InventoryController::class, 'index']);
        Route::post('/', [InventoryController::class, 'store']);
        Route::get('/{inventoryItem}', [InventoryController::class, 'show']);
        Route::put('/{inventoryItem}', [InventoryController::class, 'update']);
        Route::delete('/{inventoryItem}', [InventoryController::class, 'destroy']);
        Route::post('/{inventoryItem}/assign', [InventoryController::class, 'assign']);
        Route::post('/{inventoryItem}/return', [InventoryController::class, 'return']);
    });

});
