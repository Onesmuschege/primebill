<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Http\Requests\Ticket\ReplyTicketRequest;
use App\Models\Ticket;
use App\Services\Support\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    // GET /api/tickets
    public function index(Request $request)
    {
        $tickets = $this->ticketService->getAllTickets($request);

        return response()->json([
            'success' => true,
            'data'    => $tickets,
        ]);
    }

    // POST /api/tickets
    public function store(StoreTicketRequest $request)
    {
        $ticket = $this->ticketService->createTicket(
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Ticket created successfully',
            'data'    => $ticket,
        ], 201);
    }

    // GET /api/tickets/{id}
    public function show(Ticket $ticket)
    {
        $ticket->load('client', 'assignedTo', 'replies.user');

        return response()->json([
            'success' => true,
            'data'    => $ticket,
        ]);
    }

    // PUT /api/tickets/{id}
    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status'   => 'sometimes|in:open,pending,solved,closed',
            'priority' => 'sometimes|in:low,medium,high,critical',
        ]);

        $ticket->update($request->only('status', 'priority'));

        return response()->json([
            'success' => true,
            'message' => 'Ticket updated successfully',
            'data'    => $ticket,
        ]);
    }

    // POST /api/tickets/{id}/reply
    public function reply(ReplyTicketRequest $request, Ticket $ticket)
    {
        $reply = $this->ticketService->replyTicket(
            $ticket,
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Reply added successfully',
            'data'    => $reply,
        ], 201);
    }

    // POST /api/tickets/{id}/assign
    public function assign(Request $request, Ticket $ticket)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $ticket = $this->ticketService->assignTicket(
            $ticket,
            $request->user_id,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Ticket assigned successfully',
            'data'    => $ticket,
        ]);
    }

    // POST /api/tickets/{id}/close
    public function close(Request $request, Ticket $ticket)
    {
        $ticket = $this->ticketService->closeTicket(
            $ticket,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Ticket closed successfully',
            'data'    => $ticket,
        ]);
    }

    // POST /api/tickets/{id}/escalate
    public function escalate(Request $request, Ticket $ticket)
    {
        $ticket = $this->ticketService->escalateTicket(
            $ticket,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Ticket escalated successfully',
            'data'    => $ticket,
        ]);
    }

    // GET /api/tickets/stats
    public function stats()
    {
        $stats = $this->ticketService->getStats();

        return response()->json([
            'success' => true,
            'data'    => $stats,
        ]);
    }
}
