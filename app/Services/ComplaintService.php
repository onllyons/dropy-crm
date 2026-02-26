<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ComplaintService
{
    public function getCooldownRows(int $userId): array
    {
        $limits = $this->cooldownLimits();

        try {
            $rows = DB::connection('tenant')->select(
                'SELECT
                    src,
                    counter,
                    max_tries,
                    FROM_UNIXTIME(created) AS window_start,
                    FROM_UNIXTIME(created + window_sec) AS window_end,
                    (expire > UNIX_TIMESTAMP()) AS is_blocked,
                    FROM_UNIXTIME(NULLIF(expire, 0)) AS blocked_until,
                    GREATEST(0, expire - UNIX_TIMESTAMP()) AS block_sec_left,
                    CASE
                        WHEN expire > UNIX_TIMESTAMP() THEN 0
                        WHEN created + window_sec > UNIX_TIMESTAMP() THEN GREATEST(0, max_tries - counter)
                        ELSE max_tries
                    END AS tries_left
                FROM (
                    SELECT \'problem\' AS src, t.counter, t.created, t.expire, 5 AS max_tries, 600 AS window_sec
                    FROM (SELECT counter, created, expire FROM complaintProblemCooldown WHERE userId = ? ORDER BY id DESC LIMIT 1) t

                    UNION ALL

                    SELECT \'game\' AS src, t.counter, t.created, t.expire, 25 AS max_tries, 600 AS window_sec
                    FROM (SELECT counter, created, expire FROM complaintGameCooldown WHERE userId = ? ORDER BY id DESC LIMIT 1) t

                    UNION ALL

                    SELECT \'flashcards\' AS src, t.counter, t.created, t.expire, 35 AS max_tries, 600 AS window_sec
                    FROM (SELECT counter, created, expire FROM complaintFlashcardsCooldown WHERE userId = ? ORDER BY id DESC LIMIT 1) t
                ) q',
                [$userId, $userId, $userId]
            );

            $rowsBySource = [];
            foreach ($rows as $row) {
                $source = (string) ($row->src ?? '');
                if ($source === '') {
                    continue;
                }

                $rowsBySource[$source] = [
                    'src' => $source,
                    'counter' => is_numeric($row->counter ?? null) ? (int) $row->counter : 0,
                    'max_tries' => is_numeric($row->max_tries ?? null) ? (int) $row->max_tries : ($limits[$source]['max_tries'] ?? 0),
                    'tries_left' => is_numeric($row->tries_left ?? null) ? (int) $row->tries_left : 0,
                    'is_blocked' => is_numeric($row->is_blocked ?? null) ? (int) $row->is_blocked : 0,
                    'blocked_until' => $row->blocked_until ?? null,
                    'window_start' => $row->window_start ?? null,
                    'window_end' => $row->window_end ?? null,
                    'block_sec_left' => is_numeric($row->block_sec_left ?? null) ? (int) $row->block_sec_left : 0,
                ];
            }

            $normalizedRows = [];
            foreach ($limits as $source => $config) {
                if (isset($rowsBySource[$source])) {
                    $normalizedRows[] = $rowsBySource[$source];
                    continue;
                }

                $normalizedRows[] = [
                    'src' => $source,
                    'counter' => 0,
                    'max_tries' => (int) $config['max_tries'],
                    'tries_left' => (int) $config['max_tries'],
                    'is_blocked' => 0,
                    'blocked_until' => null,
                    'window_start' => null,
                    'window_end' => null,
                    'block_sec_left' => 0,
                ];
            }

            return [
                'rows' => $normalizedRows,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'rows' => $this->emptyRows($limits),
                'error' => $e->getMessage(),
            ];
        }
    }

    private function cooldownLimits(): array
    {
        return [
            'problem' => [
                'max_tries' => 5,
            ],
            'game' => [
                'max_tries' => 25,
            ],
            'flashcards' => [
                'max_tries' => 35,
            ],
        ];
    }

    private function emptyRows(array $limits): array
    {
        $rows = [];
        foreach ($limits as $source => $config) {
            $rows[] = [
                'src' => $source,
                'counter' => 0,
                'max_tries' => (int) ($config['max_tries'] ?? 0),
                'tries_left' => (int) ($config['max_tries'] ?? 0),
                'is_blocked' => 0,
                'blocked_until' => null,
                'window_start' => null,
                'window_end' => null,
                'block_sec_left' => 0,
            ];
        }

        return $rows;
    }
}
