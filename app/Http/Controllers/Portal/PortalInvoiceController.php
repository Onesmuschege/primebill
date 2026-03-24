<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class PortalInvoiceController extends Controller
{
    // GET /api/portal/invoices
    public function index(Request $request)
    {
        $invoices = Invoice::where('client_id', $request->user()->id)
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $invoices,
        ]);
    }

    // GET /api/portal/invoices/{id}
    public function show(Request $request, Invoice $invoice)
    {
        if ($invoice->client_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => $invoice,
        ]);
    }
}