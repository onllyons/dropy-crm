<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class VisitorsAnalyticsGroupedUrlService
{
    public function search(string $source, string $query): array
    {
        $sourceKey = $this->resolveSource($source);
        $queryText = trim($query);
        $config = $this->sourceConfig($sourceKey);
        $searchLike = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $queryText) . '%';

        $baseQuery = DB::connection('tenant')
            ->table($config['table'])
            ->whereNotNull($config['search_column'])
            ->where($config['search_column'], '!=', '')
            ->where($config['search_column'], 'like', $searchLike);

        $totalRows = (int) (clone $baseQuery)->count();

        $summary = (clone $baseQuery)
            ->selectRaw('COALESCE(SUM(`' . $config['duration_column'] . '`), 0) as total_seconds')
            ->first();

        $totalSeconds = is_numeric($summary->total_seconds ?? null) ? (float) $summary->total_seconds : 0.0;
        $groupLimit = 200;

        $groupsRaw = (clone $baseQuery)
            ->select($config['search_column'] . ' as grouped_value')
            ->selectRaw('COUNT(*) as rows_count')
            ->selectRaw('COALESCE(SUM(`' . $config['duration_column'] . '`), 0) as total_seconds')
            ->groupBy($config['search_column'])
            ->orderByDesc('rows_count')
            ->limit($groupLimit)
            ->get();

        $totalGroups = (int) (clone $baseQuery)
            ->distinct()
            ->count($config['search_column']);

        $groups = $groupsRaw->map(function ($row) {
            $seconds = is_numeric($row->total_seconds ?? null) ? (float) $row->total_seconds : 0.0;

            return (object) [
                'grouped_value' => (string) ($row->grouped_value ?? ''),
                'rows_count' => is_numeric($row->rows_count ?? null) ? (int) $row->rows_count : 0,
                'total_seconds' => $seconds,
                'total_hours' => $seconds / 3600,
            ];
        })->values();

        return [
            'source' => $sourceKey,
            'source_label' => $config['label'],
            'table' => $config['table'],
            'search_column' => $config['search_column'],
            'duration_column' => $config['duration_column'],
            'query' => $queryText,
            'search_like' => $searchLike,
            'total_rows' => $totalRows,
            'total_seconds' => $totalSeconds,
            'total_hours' => $totalSeconds / 3600,
            'groups' => $groups,
            'group_limit' => $groupLimit,
            'total_groups' => $totalGroups,
        ];
    }

    private function resolveSource(string $source): string
    {
        $normalized = strtolower(trim($source));
        return in_array($normalized, ['web', 'app'], true) ? $normalized : 'web';
    }

    private function sourceConfig(string $source): array
    {
        if ($source === 'app') {
            return [
                'label' => 'Mobile / App',
                'table' => 'visitorBehaviorAnalyticsApp',
                'search_column' => 'screen',
                'duration_column' => 'lengthStayOnScreen',
            ];
        }

        return [
            'label' => 'Website / Web',
            'table' => 'visitorBehaviorAnalytics',
            'search_column' => 'recoveredPage',
            'duration_column' => 'lengthStayOnPage',
        ];
    }
}
