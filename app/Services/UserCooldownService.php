<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserCooldownService
{
    public function getRowsForUser(int $userId, string $filter = 'all'): array
    {
        $resolvedFilter = $this->resolveFilter($filter);
        $nowTs = time();
        $currentHour = (int) now()->format('G');

        $userActionRows = collect();
        $userActionError = null;
        $gameActionRows = collect();
        $gameActionError = null;

        try {
            $userActionRows = $this->loadUserActionRows($userId, $resolvedFilter, $nowTs);
        } catch (\Throwable $e) {
            $userActionError = $e->getMessage();
        }

        try {
            $gameActionRows = $this->loadGameActionRows($userId, $resolvedFilter, $nowTs, $currentHour);
        } catch (\Throwable $e) {
            $gameActionError = $e->getMessage();
        }

        return [
            'filter' => $resolvedFilter,
            'filter_options' => [
                'all' => 'All',
                'active' => 'Only active',
            ],
            'user_action_rows' => $userActionRows,
            'user_action_error' => $userActionError,
            'game_action_rows' => $gameActionRows,
            'game_action_error' => $gameActionError,
        ];
    }

    private function resolveFilter(string $filter): string
    {
        $normalized = strtolower(trim($filter));
        return in_array($normalized, ['all', 'active'], true) ? $normalized : 'all';
    }

    private function loadUserActionRows(int $userId, string $filter, int $nowTs): Collection
    {
        $query = DB::connection('tenant')
            ->table('user_action_cooldown')
            ->select('id', 'userId', 'hash', 'action_type', 'counter', 'created', 'expire')
            ->selectRaw('CASE WHEN `expire` IS NOT NULL AND `expire` > ? THEN 1 ELSE 0 END as is_active', [$nowTs])
            ->where('userId', $userId)
            ->orderByDesc('created')
            ->orderByDesc('id');

        if ($filter === 'active') {
            $query->whereNotNull('expire')
                ->where('expire', '>', $nowTs);
        }

        return $query->get();
    }

    private function loadGameActionRows(int $userId, string $filter, int $nowTs, int $currentHour): Collection
    {
        $query = DB::connection('tenant')
            ->table('game_action_cooldown')
            ->select('id', 'userId', 'action_type', 'counter', 'hoursAtStart', 'reason', 'created')
            ->selectRaw('CASE WHEN (`created` + 86400) > ? AND `hoursAtStart` <= ? THEN 1 ELSE 0 END as is_active', [$nowTs, $currentHour])
            ->where('userId', $userId)
            ->orderByDesc('created')
            ->orderByDesc('id');

        if ($filter === 'active') {
            $query->whereRaw('(`created` + 86400) > ? AND `hoursAtStart` <= ?', [$nowTs, $currentHour]);
        }

        return $query->get()->map(function ($row) {
            $row->counter_decoded = $this->decodeCounter($row->counter ?? null);
            return $row;
        });
    }

    private function decodeCounter($counter): string
    {
        if ($counter === null || $counter === '') {
            return '-';
        }

        $raw = (string) $counter;
        $decoded = json_decode($raw, true);

        if (json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded))) {
            return (string) json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $raw;
    }
}
