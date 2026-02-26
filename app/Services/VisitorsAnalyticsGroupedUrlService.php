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

    public function detail(string $source, string $value, int $perPage = 100): array
    {
        $sourceKey = $this->resolveSource($source);
        $config = $this->sourceConfig($sourceKey);
        $valueText = trim($value);

        if ($perPage < 10) {
            $perPage = 10;
        }
        if ($perPage > 500) {
            $perPage = 500;
        }

        $baseQuery = DB::connection('tenant')
            ->table($config['table'])
            ->where($config['search_column'], $valueText);

        $summaryRow = (clone $baseQuery)
            ->selectRaw('COUNT(*) as total_rows')
            ->selectRaw('COALESCE(SUM(`' . $config['duration_column'] . '`), 0) as total_seconds')
            ->selectRaw('MIN(`time`) as first_time')
            ->selectRaw('MAX(`time`) as last_time')
            ->selectRaw('COUNT(DISTINCT CASE WHEN `user_id` > 0 THEN `user_id` END) as unique_users')
            ->first();

        $totalRows = is_numeric($summaryRow->total_rows ?? null) ? (int) $summaryRow->total_rows : 0;
        $totalSeconds = is_numeric($summaryRow->total_seconds ?? null) ? (float) $summaryRow->total_seconds : 0.0;

        $dailyRows = (clone $baseQuery)
            ->selectRaw('DATE(FROM_UNIXTIME(`time`)) as day')
            ->selectRaw('COUNT(*) as rows_count')
            ->selectRaw('COALESCE(SUM(`' . $config['duration_column'] . '`), 0) as total_seconds')
            ->groupBy('day')
            ->orderByDesc('day')
            ->limit(60)
            ->get()
            ->map(function ($row) {
                $seconds = is_numeric($row->total_seconds ?? null) ? (float) $row->total_seconds : 0.0;

                return (object) [
                    'day' => (string) ($row->day ?? '-'),
                    'rows_count' => is_numeric($row->rows_count ?? null) ? (int) $row->rows_count : 0,
                    'total_seconds' => $seconds,
                    'total_hours' => $seconds / 3600,
                ];
            });

        $topCountries = (clone $baseQuery)
            ->select('country')
            ->selectRaw('COUNT(*) as rows_count')
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->groupBy('country')
            ->orderByDesc('rows_count')
            ->limit(10)
            ->get();

        $topIps = (clone $baseQuery)
            ->select('ipAddress')
            ->selectRaw('COUNT(*) as rows_count')
            ->whereNotNull('ipAddress')
            ->where('ipAddress', '!=', '')
            ->groupBy('ipAddress')
            ->orderByDesc('rows_count')
            ->limit(10)
            ->get();

        if ($sourceKey === 'app') {
            $rows = (clone $baseQuery)
                ->select(
                    'id',
                    'user_id',
                    'ipAddress',
                    'screen',
                    'lastScreen',
                    'country',
                    'region',
                    'city',
                    'timezone',
                    'deviceName',
                    'operatingSystem',
                    'osVersion',
                    'windowWidth',
                    'language',
                    'lengthStayOnScreen',
                    'date',
                    'time'
                )
                ->orderByDesc('id')
                ->paginate($perPage);
        } else {
            $rows = (clone $baseQuery)
                ->select(
                    'id',
                    'user_id',
                    'ipAddress',
                    'recoveredPage',
                    'historyToPage',
                    'country',
                    'region',
                    'city',
                    'timezone',
                    'deviceName',
                    'operatingSystem',
                    'browserVersion',
                    'browserWindowWidth',
                    'browserLanguage',
                    'lengthStayOnPage',
                    'date',
                    'time'
                )
                ->orderByDesc('id')
                ->paginate($perPage);
        }

        $userIds = collect($rows->items())->pluck('user_id')->filter()->unique()->values();
        $users = collect();
        if ($userIds->isNotEmpty()) {
            $users = DB::connection('mysql')
                ->table('users')
                ->select('id', 'username', 'name')
                ->whereIn('id', $userIds)
                ->get()
                ->keyBy('id');
        }

        return [
            'source' => $sourceKey,
            'source_label' => $config['label'],
            'table' => $config['table'],
            'search_column' => $config['search_column'],
            'duration_column' => $config['duration_column'],
            'value' => $valueText,
            'total_rows' => $totalRows,
            'total_seconds' => $totalSeconds,
            'total_hours' => $totalSeconds / 3600,
            'avg_seconds' => $totalRows > 0 ? ($totalSeconds / $totalRows) : 0.0,
            'unique_users' => is_numeric($summaryRow->unique_users ?? null) ? (int) $summaryRow->unique_users : 0,
            'first_time' => is_numeric($summaryRow->first_time ?? null) ? (int) $summaryRow->first_time : null,
            'last_time' => is_numeric($summaryRow->last_time ?? null) ? (int) $summaryRow->last_time : null,
            'daily_rows' => $dailyRows,
            'top_countries' => $topCountries,
            'top_ips' => $topIps,
            'rows' => $rows,
            'users' => $users,
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
