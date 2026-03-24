<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;

class PortalTicketController extends Controller
{
    // GET /api/portal/tickets
    public function index(Request $request)
    {
        $tickets = Ticket::where('client_id', $request->user()->id)
                         ->orderBy('created_at', 'desc')
                         ->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $tickets,
        ]);
    }

    // POST /api/portal/tickets
    public function store(Request $request)
    {
        $request->validate([
            'subject'     => 'required|string|max:255',
            'description' => 'required|string',
            'priority'    => 'nullable|in:low,medium,high',
        ]);

        $ticket = Ticket::create([
            'client_id'   => $request->user()->id,
            'subject'     => $request->subject,
            'description' => $request->description,
            'priority'    => $request->priority ?? 'medium',
            'status'      => 'open',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket submitted successfully',
            'data'    => $ticket,
        ], 201);
    }

    // GET /api/portal/tickets/{id}
    public function show(Request $request, Ticket $ticket)
    {
        if ($ticket->client_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $ticket->load('replies.user');

        return response()->json([
            'success' => true,
            'data'    => $ticket,
        ]);
    }

    // POST /api/portal/tickets/{id}/reply
    public function reply(Request $request, Ticket $ticket)
    {
        if ($ticket->client_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'message' => 'required|string',
        ]);

        $reply = TicketReply::create([
            'ticket_id'   => $ticket->id,
            'message'     => $request->message,
            'is_internal' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reply added successfully',
            'data'    => $reply,
        ], 201);
    }
}