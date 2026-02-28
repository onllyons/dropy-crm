<?php

namespace App\Http\Controllers;

use App\Services\MessageGamesService;

class MessageGamesController extends Controller
{
    public function index(MessageGamesService $service)
    {
        $rows = collect();
        $error = null;

        try {
            $rows = $service->getRows();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('games.message_games', [
            'rows' => $rows,
            'error' => $error,
        ]);
    }
}

