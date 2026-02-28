<?php

namespace App\Http\Controllers;

use App\Services\MessengerService;
use Illuminate\Http\Request;

class MessengerController extends Controller
{
    public function index()
    {
        return view('messenger');
    }

    public function tickets(MessengerService $service)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $service->listTickets(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error_message' => $e->getMessage(),
            ], 500);
        }
    }

    public function ticket(Request $request, MessengerService $service)
    {
        $payload = $request->validate([
            'table' => ['required', 'string'],
            'complaintId' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $ticket = $service->getTicket($payload['table'], (int) $payload['complaintId']);
            if (!$ticket) {
                return response()->json([
                    'success' => false,
                    'error_message' => 'Тикет не найден.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $ticket,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error_message' => $e->getMessage(),
            ], 422);
        }
    }

    public function sendMessage(Request $request, MessengerService $service)
    {
        $payload = $request->validate([
            'table' => ['required', 'string'],
            'complaintId' => ['required', 'integer', 'min:1'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        try {
            $ticket = $service->sendSupportMessage(
                $payload['table'],
                (int) $payload['complaintId'],
                (string) $payload['message'],
                (string) app()->getLocale()
            );

            return response()->json([
                'success' => true,
                'data' => $ticket,
                'messages' => $ticket['messages'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error_message' => $e->getMessage(),
            ], 422);
        }
    }

    public function closeComplaint(Request $request, MessengerService $service)
    {
        $payload = $request->validate([
            'table' => ['required', 'string'],
            'complaintId' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $service->closeComplaint($payload['table'], (int) $payload['complaintId']);

            return response()->json([
                'success' => true,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error_message' => $e->getMessage(),
            ], 422);
        }
    }
}
