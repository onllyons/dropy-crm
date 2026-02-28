<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MessageGamesService
{
    public function getRows(): Collection
    {
        if (!Schema::connection('tenant')->hasTable('message_games')) {
            throw new \RuntimeException('Table message_games does not exist in tenant DB.');
        }

        return DB::connection('tenant')
            ->table('message_games')
            ->select('id', 'title', 'description', 'image', 'type')
            ->orderByDesc('id')
            ->get();
    }
}

