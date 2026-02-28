<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

class MessengerService
{
    public function listTickets(string $status = 'open', int $limit = 20, string $range = 'all'): array
    {
        $resolvedStatus = strtolower(trim($status));
        if (!in_array($resolvedStatus, ['open', 'closed', 'all'], true)) {
            $resolvedStatus = 'open';
        }

        $resolvedRange = $this->resolveRange($range);

        if ($limit < 1) {
            $limit = 20;
        }
        if ($limit > 200) {
            $limit = 200;
        }

        $complaints = collect();

        foreach ($this->sourceMap() as $sourceKey => $sourceMeta) {
            $query = $this->buildComplaintQuery($sourceKey);

            if ($resolvedStatus === 'open') {
                $query->where($sourceMeta['status_column'], 0);
            } elseif ($resolvedStatus === 'closed') {
                $query->where($sourceMeta['status_column'], 1);
            }

            $this->applyDateRangeFilter($query, $sourceMeta, $resolvedRange);

            $rows = $query
                ->orderByDesc('id')
                ->limit($limit)
                ->get();

            foreach ($rows as $row) {
                $complaints->push($this->normalizeComplaintRow($sourceKey, $sourceMeta, $row));
            }
        }

        if ($complaints->isEmpty()) {
            return [];
        }

        $complaints = $complaints
            ->sortByDesc(function ($row) {
                return (int) ($row['time_sort'] ?? 0);
            })
            ->values()
            ->take($limit)
            ->values();

        return $this->hydrateTickets($complaints);
    }

    public function getTicket(string $source, int $complaintId): ?array
    {
        $sourceKey = $this->resolveSource($source);
        $sourceMeta = $this->sourceMeta($sourceKey);

        $row = $this->buildComplaintQuery($sourceKey)
            ->where('id', $complaintId)
            ->first();

        if (!$row) {
            return null;
        }

        $complaint = $this->normalizeComplaintRow($sourceKey, $sourceMeta, $row);
        $tickets = $this->hydrateTickets(collect([$complaint]));

        return isset($tickets[0]) ? $tickets[0] : null;
    }

    public function closeComplaint(string $source, int $complaintId): void
    {
        $sourceKey = $this->resolveSource($source);
        $sourceMeta = $this->sourceMeta($sourceKey);

        DB::connection('tenant')
            ->table($sourceMeta['table'])
            ->where('id', $complaintId)
            ->update([
                $sourceMeta['status_column'] => 1,
            ]);
    }

    public function sendSupportMessage(string $source, int $complaintId, string $message, string $locale = 'ru'): array
    {
        $messageText = trim($message);
        if ($messageText === '') {
            throw new \InvalidArgumentException('Заполните поле сообщения.');
        }

        $sourceKey = $this->resolveSource($source);
        $sourceMeta = $this->sourceMeta($sourceKey);

        $complaintRow = DB::connection('tenant')
            ->table($sourceMeta['table'])
            ->where('id', $complaintId)
            ->where($sourceMeta['status_column'], 0)
            ->first();

        if (!$complaintRow) {
            throw new \RuntimeException('Тикет не найден или уже закрыт.');
        }

        $userIdColumn = $sourceMeta['user_id_column'];
        $userId = is_numeric($complaintRow->{$userIdColumn}) ? (int) $complaintRow->{$userIdColumn} : 0;

        DB::connection('tenant')
            ->table('message_help_center')
            ->insert([
                'userId' => $userId,
                'complaintId' => $complaintId,
                'complaintTable' => $sourceKey,
                'messageDate' => time(),
                'messageType' => 'support',
                'messageContent' => $messageText,
            ]);

        $this->markComplaintUnreadByUser($sourceMeta['table'], $complaintId);
        $this->createNotification($userId, $complaintId, $locale);

        $ticket = $this->getTicket($sourceKey, $complaintId);
        if (!$ticket) {
            throw new \RuntimeException('Не удалось загрузить обновленный тикет.');
        }

        return $ticket;
    }

    public function allowedSources(): array
    {
        return array_keys($this->sourceMap());
    }

    private function hydrateTickets(Collection $complaints): array
    {
        $userIds = $complaints
            ->pluck('userId')
            ->filter(function ($id) {
                return is_numeric($id) && (int) $id > 0;
            })
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values();

        $users = collect();
        if ($userIds->isNotEmpty()) {
            $users = DB::connection('mysql')
                ->table('users')
                ->select('id', 'name', 'image')
                ->whereIn('id', $userIds)
                ->get()
                ->keyBy('id');
        }

        $complaintIdsByTable = [];
        foreach ($complaints as $complaint) {
            $table = (string) ($complaint['table'] ?? '');
            $id = is_numeric($complaint['id'] ?? null) ? (int) $complaint['id'] : 0;
            if ($table === '' || $id <= 0) {
                continue;
            }
            if (!isset($complaintIdsByTable[$table])) {
                $complaintIdsByTable[$table] = [];
            }
            $complaintIdsByTable[$table][] = $id;
        }

        $messagesMap = $this->loadMessagesMap($complaintIdsByTable);

        $tickets = [];
        foreach ($complaints as $complaint) {
            $userId = is_numeric($complaint['userId']) ? (int) $complaint['userId'] : 0;
            $user = $userId > 0 ? $users->get($userId) : null;

            $userName = $user ? ($user->name ?: ('User #' . $userId)) : ('Пользователь не найден (ID: ' . $userId . ')');
            $userImage = $user ? ($user->image ?: 'default.png') : 'default.png';

            $messages = [];
            $messages[] = [
                'type' => 'user',
                'message' => strip_tags((string) ($complaint['message'] ?? '')),
                'time' => $complaint['time'],
                'time_sort' => (int) ($complaint['time_sort'] ?? 0),
                'user' => [
                    'id' => $userId,
                    'name' => $userName,
                    'image' => $userImage,
                ],
            ];

            $table = (string) $complaint['table'];
            $id = (int) $complaint['id'];
            $extraMessages = isset($messagesMap[$table][$id]) ? $messagesMap[$table][$id] : [];

            foreach ($extraMessages as $rowMessage) {
                $messageType = (string) ($rowMessage['type'] ?? '');
                $isUser = $messageType === 'user';

                $messages[] = [
                    'type' => $messageType,
                    'message' => $isUser
                        ? strip_tags((string) ($rowMessage['message'] ?? ''))
                        : (string) ($rowMessage['message'] ?? ''),
                    'time' => $rowMessage['time'],
                    'time_sort' => (int) ($rowMessage['time_sort'] ?? 0),
                    'user' => [
                        'id' => $isUser ? $userId : null,
                        'name' => $isUser ? $userName : 'Onllyons language Support',
                        'image' => $isUser ? $userImage : 'onllyons-support.png',
                    ],
                ];
            }

            $tickets[] = [
                'complaint' => $complaint,
                'messages' => $messages,
            ];
        }

        usort($tickets, function ($a, $b) {
            $left = is_numeric($a['complaint']['time_sort'] ?? null) ? (int) $a['complaint']['time_sort'] : 0;
            $right = is_numeric($b['complaint']['time_sort'] ?? null) ? (int) $b['complaint']['time_sort'] : 0;
            return $right <=> $left;
        });

        return $tickets;
    }

    private function loadMessagesMap(array $complaintIdsByTable): array
    {
        $map = [];

        foreach ($complaintIdsByTable as $table => $ids) {
            $idList = collect($ids)
                ->filter(function ($id) {
                    return is_numeric($id) && (int) $id > 0;
                })
                ->map(function ($id) {
                    return (int) $id;
                })
                ->unique()
                ->values();

            if ($idList->isEmpty()) {
                continue;
            }

            $rows = DB::connection('tenant')
                ->table('message_help_center')
                ->select(
                    'complaintId',
                    'complaintTable',
                    DB::raw('messageType as type'),
                    DB::raw('messageContent as message'),
                    DB::raw('messageDate as time')
                )
                ->where('complaintTable', $table)
                ->whereIn('complaintId', $idList)
                ->orderBy('id')
                ->get();

            foreach ($rows as $row) {
                $complaintId = is_numeric($row->complaintId ?? null) ? (int) $row->complaintId : 0;
                if ($complaintId <= 0) {
                    continue;
                }

                if (!isset($map[$table])) {
                    $map[$table] = [];
                }
                if (!isset($map[$table][$complaintId])) {
                    $map[$table][$complaintId] = [];
                }

                $map[$table][$complaintId][] = [
                    'type' => (string) ($row->type ?? ''),
                    'message' => (string) ($row->message ?? ''),
                    'time' => $row->time,
                    'time_sort' => $this->toTimestamp($row->time),
                ];
            }
        }

        return $map;
    }

    private function normalizeComplaintRow(string $sourceKey, array $sourceMeta, $row): array
    {
        return [
            'id' => is_numeric($row->id ?? null) ? (int) $row->id : 0,
            'userId' => is_numeric($row->userId ?? null) ? (int) $row->userId : 0,
            'theme' => (string) ($row->theme ?? ''),
            'message' => (string) ($row->message ?? ''),
            'status' => is_numeric($row->status ?? null) ? (int) $row->status : 0,
            'time' => $row->time,
            'time_sort' => $this->toTimestamp($row->time),
            'table' => $sourceKey,
            'displayName' => $sourceMeta['display_name'],
            'gameId' => isset($row->gameId) ? $row->gameId : null,
            'url' => isset($row->url) ? $row->url : null,
            'course_id_slide' => isset($row->course_id_slide) ? $row->course_id_slide : null,
            'course_url' => isset($row->course_url) ? $row->course_url : null,
        ];
    }

    private function buildComplaintQuery(string $source)
    {
        if ($source === 'complaint_user') {
            return DB::connection('tenant')
                ->table('complaint_user')
                ->select(
                    'id',
                    DB::raw('user_id as userId'),
                    DB::raw('form_support_theme as theme'),
                    DB::raw('form_support_message as message'),
                    DB::raw('form_support_status as status'),
                    DB::raw('form_support_date as time'),
                    DB::raw('NULL as gameId'),
                    DB::raw('NULL as url'),
                    DB::raw('NULL as course_id_slide'),
                    DB::raw('NULL as course_url')
                );
        }

        if ($source === 'complaint_user_course') {
            return DB::connection('tenant')
                ->table('complaint_user_course')
                ->select(
                    'id',
                    DB::raw('user_id as userId'),
                    DB::raw('course_support_theme as theme'),
                    DB::raw('course_support_message as message'),
                    DB::raw('course_support_status as status'),
                    DB::raw('course_support_date as time'),
                    DB::raw('NULL as gameId'),
                    DB::raw('NULL as url'),
                    'course_id_slide',
                    'course_url'
                );
        }

        if ($source === 'complaint_game') {
            return DB::connection('tenant')
                ->table('complaint_game')
                ->select(
                    'id',
                    'userId',
                    'theme',
                    'message',
                    'status',
                    'time',
                    'gameId',
                    DB::raw('urlPage as url'),
                    DB::raw('NULL as course_id_slide'),
                    DB::raw('NULL as course_url')
                );
        }

        return DB::connection('tenant')
            ->table('complaint_flashcards')
            ->select(
                'id',
                'userId',
                'theme',
                'message',
                'status',
                'time',
                DB::raw('NULL as gameId'),
                'url',
                DB::raw('NULL as course_id_slide'),
                DB::raw('NULL as course_url')
            );
    }

    private function sourceMap(): array
    {
        return [
            'complaint_user' => [
                'display_name' => 'Общая форма поддержки сайта',
                'table' => 'complaint_user',
                'status_column' => 'form_support_status',
                'user_id_column' => 'user_id',
                'time_column' => 'form_support_date',
            ],
            'complaint_user_course' => [
                'display_name' => 'Курс: Английский язык',
                'table' => 'complaint_user_course',
                'status_column' => 'course_support_status',
                'user_id_column' => 'user_id',
                'time_column' => 'course_support_date',
            ],
            'complaint_game' => [
                'display_name' => 'Игра',
                'table' => 'complaint_game',
                'status_column' => 'status',
                'user_id_column' => 'userId',
                'time_column' => 'time',
            ],
            'complaint_flashcards' => [
                'display_name' => 'Карточки',
                'table' => 'complaint_flashcards',
                'status_column' => 'status',
                'user_id_column' => 'userId',
                'time_column' => 'time',
            ],
        ];
    }

    private function resolveRange(string $range): string
    {
        $normalized = strtolower(trim($range));
        return in_array($normalized, ['all', 'today', 'yesterday', 'last5', 'last30'], true)
            ? $normalized
            : 'all';
    }

    private function applyDateRangeFilter($query, array $sourceMeta, string $range): void
    {
        if ($range === 'all') {
            return;
        }

        $timeColumn = (string) ($sourceMeta['time_column'] ?? 'time');
        $bounds = $this->rangeBounds($range);
        $startDate = $bounds['start'];
        $endDate = $bounds['end'];

        $dateExpression = "CASE WHEN CAST(`{$timeColumn}` AS CHAR) REGEXP '^[0-9]+$' " .
            "THEN DATE(FROM_UNIXTIME(`{$timeColumn}`)) ELSE DATE(`{$timeColumn}`) END";

        $query->whereRaw($dateExpression . ' BETWEEN ? AND ?', [$startDate, $endDate]);
    }

    private function rangeBounds(string $range): array
    {
        $today = Carbon::now()->startOfDay();

        if ($range === 'today') {
            $start = $today->copy();
            $end = $today->copy();
        } elseif ($range === 'yesterday') {
            $start = $today->copy()->subDay();
            $end = $today->copy()->subDay();
        } elseif ($range === 'last5') {
            $start = $today->copy()->subDays(4);
            $end = $today->copy();
        } elseif ($range === 'last30') {
            $start = $today->copy()->subDays(29);
            $end = $today->copy();
        } else {
            $start = Carbon::create(1970, 1, 1);
            $end = $today->copy();
        }

        return [
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
        ];
    }

    private function resolveSource(string $source): string
    {
        $normalized = strtolower(trim($source));
        if (!array_key_exists($normalized, $this->sourceMap())) {
            throw new \InvalidArgumentException('Неверный источник тикета.');
        }

        return $normalized;
    }

    private function sourceMeta(string $source): array
    {
        $sources = $this->sourceMap();
        if (!isset($sources[$source])) {
            throw new \InvalidArgumentException('Неверный источник тикета.');
        }

        return $sources[$source];
    }

    private function toTimestamp($value): int
    {
        if (is_numeric($value)) {
            $ts = (int) $value;
            return $ts > 0 ? $ts : 0;
        }

        $ts = strtotime((string) $value);
        return $ts ? (int) $ts : 0;
    }

    private function markComplaintUnreadByUser(string $table, int $complaintId): void
    {
        try {
            if (Schema::connection('tenant')->hasColumn($table, 'readByUser')) {
                DB::connection('tenant')
                    ->table($table)
                    ->where('id', $complaintId)
                    ->update(['readByUser' => 0]);
            }
        } catch (\Throwable $e) {
            // Best effort only; ticket reply should still succeed.
        }
    }

    private function createNotification(int $userId, int $complaintId, string $locale): void
    {
        if ($userId <= 0) {
            return;
        }

        $langKey = 'notifications__new-ticket-message';
        $candidateLang = 'notifications__new-ticket-message-' . strtolower(trim($locale));

        try {
            $candidateExists = DB::connection('mysql')
                ->table('Language_general')
                ->where('value', $candidateLang)
                ->exists();

            if ($candidateExists) {
                $langKey = $candidateLang;
            }
        } catch (\Throwable $e) {
            // Keep fallback language key.
        }

        try {
            DB::connection('mysql')
                ->table('notifications')
                ->insert([
                    'type' => 'ticket',
                    'langValue' => $langKey,
                    'url' => '/messenger/' . $complaintId . '-' . strtolower(trim($locale)),
                    'userId' => $userId,
                    'read' => 0,
                    'time' => time(),
                ]);
        } catch (\Throwable $e) {
            // Best effort only.
        }
    }
}
