<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserBehaviorController extends Controller
{
    public function show(Request $request, $id)
    {
        $userId = (int) $id;
        $source = $this->resolveSource($request->query('source', 'web'));
        $range = $this->resolveRange($request->query('range', '30d'));

        $excludeOutliers = $request->boolean('exclude_outliers', true);
        $excludeZero = $request->boolean('exclude_zero', true);

        $sessionGapMinutes = (int) $request->query('session_gap', 30);
        if ($sessionGapMinutes < 5) {
            $sessionGapMinutes = 5;
        }
        if ($sessionGapMinutes > 180) {
            $sessionGapMinutes = 180;
        }

        $outlierMinutes = 30;
        $outlierThresholdSeconds = $outlierMinutes * 60;

        $rangeOptions = [
            '7d' => 'Last 7 days',
            '30d' => 'Last 30 days',
            '90d' => 'Last 90 days',
            'all' => 'All time',
        ];

        $user = DB::table('users')
            ->select('id', 'name', 'username', 'email')
            ->where('id', $userId)
            ->first();

        if (!$user) {
            abort(404);
        }

        $rows = $this->loadBehaviorRows($userId, $source);

        $events = [];
        foreach ($rows as $row) {
            $events[] = $this->normalizeEvent($row);
        }
        $events = $this->autoConvertDurationFromMsIfNeeded($events);

        $filterResult = $this->applyEventFilters($events, [
            'range' => $range,
            'exclude_outliers' => $excludeOutliers,
            'exclude_zero' => $excludeZero,
            'outlier_threshold_seconds' => $outlierThresholdSeconds,
        ]);

        $events = $this->attachSessionIds($filterResult['events'], $sessionGapMinutes * 60);

        $dailyRows = $this->buildDailyRows($events);
        $flow = $this->buildFlowRows($events);
        $engagement = $this->buildEngagementRows($events);
        $context = $this->buildContextRows($events);
        $learning = $this->buildLearningActions($events);

        $summary = $this->buildSummary(
            $events,
            $flow['session_count'],
            $engagement['top_time'],
            $learning,
            $filterResult['meta']
        );

        return view('user-behavior', [
            'user' => $user,
            'source' => $source,
            'sourceLabel' => $source === 'app' ? 'App' : 'Website',
            'range' => $range,
            'rangeOptions' => $rangeOptions,
            'excludeOutliers' => $excludeOutliers,
            'excludeZero' => $excludeZero,
            'sessionGapMinutes' => $sessionGapMinutes,
            'outlierMinutes' => $outlierMinutes,
            'summary' => $summary,
            'dailyRows' => $dailyRows,
            'flowRows' => $flow['rows'],
            'avgPerPageRows' => $engagement['avg_per_page'],
            'topTimeRows' => $engagement['top_time'],
            'bounceRows' => $engagement['bounce'],
            'countryRows' => $context['countries'],
            'languageRows' => $context['languages'],
            'osRows' => $context['os'],
            'deviceRows' => $context['devices'],
            'formFactorRows' => $context['form_factors'],
        ]);
    }

    private function resolveSource($source)
    {
        $normalized = strtolower((string) $source);
        return in_array($normalized, ['web', 'app'], true) ? $normalized : 'web';
    }

    private function resolveRange($range)
    {
        $normalized = strtolower((string) $range);
        return in_array($normalized, ['7d', '30d', '90d', 'all'], true) ? $normalized : '30d';
    }

    private function loadBehaviorRows($userId, $source)
    {
        if ($source === 'app') {
            return DB::table('visitorBehaviorAnalyticsApp')
                ->select(
                    'id',
                    'hash',
                    DB::raw('lastScreen as history_page'),
                    DB::raw('screen as page'),
                    'country',
                    DB::raw('language as language'),
                    'operatingSystem',
                    'osVersion',
                    'deviceName',
                    DB::raw('windowWidth as width'),
                    DB::raw('lengthStayOnScreen as duration'),
                    'date',
                    'time'
                )
                ->where('user_id', $userId)
                ->orderBy('time')
                ->orderBy('id')
                ->get();
        }

        return DB::table('visitorBehaviorAnalytics')
            ->select(
                'id',
                'hash',
                DB::raw('historyToPage as history_page'),
                DB::raw('recoveredPage as page'),
                'country',
                DB::raw('browserLanguage as language'),
                'operatingSystem',
                DB::raw('NULL as osVersion'),
                'deviceName',
                DB::raw('browserWindowWidth as width'),
                DB::raw('lengthStayOnPage as duration'),
                'date',
                'time'
            )
            ->where('user_id', $userId)
            ->orderBy('time')
            ->orderBy('id')
            ->get();
    }

    private function normalizeEvent($row)
    {
        $timestamp = $this->parseTimestamp($row->time ?? null, $row->date ?? null);
        $durationSeconds = $this->parseDurationSeconds($row->duration ?? null);
        $width = is_numeric($row->width ?? null) ? (int) $row->width : null;

        return [
            'id' => (int) ($row->id ?? 0),
            'hash' => $this->normalizeValue($row->hash ?? null, 'Unknown'),
            'history_page' => $this->normalizePage($row->history_page ?? null),
            'page' => $this->normalizePage($row->page ?? null),
            'country' => $this->normalizeValue($row->country ?? null, 'Unknown'),
            'language' => $this->normalizeValue($row->language ?? null, 'Unknown'),
            'os' => $this->resolveOsLabel($row->operatingSystem ?? null, $row->osVersion ?? null),
            'device' => $this->normalizeValue($row->deviceName ?? null, 'Unknown'),
            'duration' => $durationSeconds,
            'timestamp' => $timestamp,
            'date_key' => $this->resolveDateKey($row->date ?? null, $timestamp),
            'device_type' => $this->resolveDeviceType($width),
        ];
    }

    private function resolveOsLabel($operatingSystem, $osVersion)
    {
        $os = trim((string) $operatingSystem);
        $version = trim((string) $osVersion);

        if ($os === '' && $version === '') {
            return 'Unknown';
        }
        if ($os === '') {
            return $version;
        }
        if ($version === '') {
            return $os;
        }
        if (strpos($os, $version) !== false) {
            return $os;
        }

        return $os . ' ' . $version;
    }

    private function applyEventFilters(array $events, array $options)
    {
        $range = $options['range'];
        $excludeOutliers = (bool) $options['exclude_outliers'];
        $excludeZero = (bool) $options['exclude_zero'];
        $outlierThresholdSeconds = (float) $options['outlier_threshold_seconds'];

        $rangeStartTs = null;
        $rangeStartDate = null;

        if ($range !== 'all') {
            $daysMap = [
                '7d' => 7,
                '30d' => 30,
                '90d' => 90,
            ];
            $days = isset($daysMap[$range]) ? $daysMap[$range] : 30;
            $rangeStartTs = strtotime('-' . ($days - 1) . ' days 00:00:00');
            if ($rangeStartTs === false) {
                $rangeStartTs = time() - ($days * 86400);
            }
            $rangeStartDate = date('Y-m-d', $rangeStartTs);
        }

        $filtered = [];
        $removedRange = 0;
        $removedZero = 0;
        $removedOutliers = 0;

        foreach ($events as $event) {
            if (!$this->isEventInRange($event, $rangeStartTs, $rangeStartDate)) {
                $removedRange++;
                continue;
            }

            if ($excludeZero && $event['duration'] <= 0) {
                $removedZero++;
                continue;
            }

            if ($excludeOutliers && $event['duration'] > $outlierThresholdSeconds) {
                $removedOutliers++;
                continue;
            }

            $filtered[] = $event;
        }

        return [
            'events' => $filtered,
            'meta' => [
                'raw_total' => count($events),
                'kept_total' => count($filtered),
                'removed_range' => $removedRange,
                'removed_zero' => $removedZero,
                'removed_outliers' => $removedOutliers,
            ],
        ];
    }

    private function isEventInRange(array $event, $rangeStartTs, $rangeStartDate)
    {
        if ($rangeStartTs === null) {
            return true;
        }

        if ($event['timestamp'] !== null) {
            return $event['timestamp'] >= $rangeStartTs;
        }

        if ($event['date_key'] !== 'Unknown') {
            return strcmp($event['date_key'], $rangeStartDate) >= 0;
        }

        return false;
    }

    private function attachSessionIds(array $events, $gapSeconds)
    {
        $streams = [];

        foreach ($events as $event) {
            $streamKey = $event['hash'] !== 'Unknown' ? $event['hash'] : 'unknown';
            if (!isset($streams[$streamKey])) {
                $streams[$streamKey] = [];
            }
            $streams[$streamKey][] = $event;
        }

        $withSessions = [];

        foreach ($streams as $streamKey => $streamEvents) {
            usort($streamEvents, function ($a, $b) {
                $aTs = $a['timestamp'] !== null ? $a['timestamp'] : 0;
                $bTs = $b['timestamp'] !== null ? $b['timestamp'] : 0;
                if ($aTs === $bTs) {
                    return $a['id'] <=> $b['id'];
                }
                return $aTs <=> $bTs;
            });

            $sessionIndex = 0;
            $lastTs = null;

            foreach ($streamEvents as $event) {
                $startNewSession = false;

                if ($sessionIndex === 0) {
                    $startNewSession = true;
                } elseif ($event['timestamp'] === null || $lastTs === null) {
                    $startNewSession = true;
                } elseif (($event['timestamp'] - $lastTs) > $gapSeconds) {
                    $startNewSession = true;
                }

                if ($startNewSession) {
                    $sessionIndex++;
                }

                $event['session_id'] = $streamKey . '#' . $sessionIndex;
                $withSessions[] = $event;
                $lastTs = $event['timestamp'];
            }
        }

        usort($withSessions, function ($a, $b) {
            $aTs = $a['timestamp'] !== null ? $a['timestamp'] : 0;
            $bTs = $b['timestamp'] !== null ? $b['timestamp'] : 0;
            if ($aTs === $bTs) {
                return $a['id'] <=> $b['id'];
            }
            return $aTs <=> $bTs;
        });

        return $withSessions;
    }

    private function buildSummary(array $events, $sessionCount, array $topTimeRows, array $learning, array $filterMeta)
    {
        $totalEvents = count($events);
        $totalTime = 0.0;
        $firstVisit = null;
        $lastVisit = null;
        $activeDays = [];

        foreach ($events as $event) {
            $totalTime += $event['duration'];

            if ($event['timestamp'] !== null) {
                if ($firstVisit === null || $event['timestamp'] < $firstVisit) {
                    $firstVisit = $event['timestamp'];
                }
                if ($lastVisit === null || $event['timestamp'] > $lastVisit) {
                    $lastVisit = $event['timestamp'];
                }
            }

            if ($event['date_key'] !== 'Unknown') {
                $activeDays[$event['date_key']] = true;
            }
        }

        $topScreens = [];
        foreach (array_slice($topTimeRows, 0, 3) as $row) {
            $topScreens[] = [
                'page' => $row['page'],
                'time' => $row['total_time_label'],
            ];
        }

        return [
            'active_days' => count($activeDays),
            'total_events' => $totalEvents,
            'total_sessions' => $sessionCount,
            'total_time_label' => $this->formatDuration($totalTime),
            'first_visit' => $firstVisit ? date('d.m.Y H:i:s', $firstVisit) : '-',
            'last_visit' => $lastVisit ? date('d.m.Y H:i:s', $lastVisit) : '-',
            'top_screens' => $topScreens,
            'learning_total' => $learning['total'],
            'lesson_visits' => $learning['lesson_visits'],
            'quiz_visits' => $learning['quiz_visits'],
            'raw_total' => $filterMeta['raw_total'],
            'kept_total' => $filterMeta['kept_total'],
            'removed_range' => $filterMeta['removed_range'],
            'removed_zero' => $filterMeta['removed_zero'],
            'removed_outliers' => $filterMeta['removed_outliers'],
        ];
    }

    private function buildDailyRows(array $events)
    {
        $daily = [];

        foreach ($events as $event) {
            $dateKey = $event['date_key'];
            if (!isset($daily[$dateKey])) {
                $daily[$dateKey] = [
                    'date' => $dateKey,
                    'session_ids' => [],
                    'total_time' => 0.0,
                    'first_ts' => null,
                    'last_ts' => null,
                ];
            }

            $sessionId = isset($event['session_id']) ? $event['session_id'] : ('row-' . $event['id']);
            $daily[$dateKey]['session_ids'][$sessionId] = true;
            $daily[$dateKey]['total_time'] += $event['duration'];

            if ($event['timestamp'] !== null) {
                if ($daily[$dateKey]['first_ts'] === null || $event['timestamp'] < $daily[$dateKey]['first_ts']) {
                    $daily[$dateKey]['first_ts'] = $event['timestamp'];
                }
                if ($daily[$dateKey]['last_ts'] === null || $event['timestamp'] > $daily[$dateKey]['last_ts']) {
                    $daily[$dateKey]['last_ts'] = $event['timestamp'];
                }
            }
        }

        uksort($daily, function ($a, $b) {
            if ($a === 'Unknown') {
                return 1;
            }
            if ($b === 'Unknown') {
                return -1;
            }
            return strcmp($b, $a);
        });

        $rows = [];
        foreach ($daily as $row) {
            $rows[] = [
                'date' => $row['date'],
                'sessions' => count($row['session_ids']),
                'total_time_label' => $this->formatDuration($row['total_time']),
                'first_visit' => $row['first_ts'] ? date('d.m.Y H:i:s', $row['first_ts']) : '-',
                'last_visit' => $row['last_ts'] ? date('d.m.Y H:i:s', $row['last_ts']) : '-',
            ];
        }

        return $rows;
    }

    private function buildFlowRows(array $events)
    {
        $sessions = [];

        foreach ($events as $event) {
            $sessionId = isset($event['session_id']) ? $event['session_id'] : ('row-' . $event['id']);
            if (!isset($sessions[$sessionId])) {
                $sessions[$sessionId] = [];
            }
            $sessions[$sessionId][] = $event;
        }

        $flowCounts = [];

        foreach ($sessions as $sessionEvents) {
            usort($sessionEvents, function ($a, $b) {
                $aTs = $a['timestamp'] !== null ? $a['timestamp'] : 0;
                $bTs = $b['timestamp'] !== null ? $b['timestamp'] : 0;
                if ($aTs === $bTs) {
                    return $a['id'] <=> $b['id'];
                }
                return $aTs <=> $bTs;
            });

            $pages = [];
            foreach ($sessionEvents as $event) {
                if ($event['history_page'] !== 'Unknown') {
                    $this->appendUnique($pages, $event['history_page']);
                }
                if ($event['page'] !== 'Unknown') {
                    $this->appendUnique($pages, $event['page']);
                }
            }

            $flow = count($pages) > 0 ? implode(' -> ', $pages) : 'Unknown';
            if (!isset($flowCounts[$flow])) {
                $flowCounts[$flow] = 0;
            }
            $flowCounts[$flow]++;
        }

        arsort($flowCounts);

        $rows = [];
        $sessionCount = count($sessions);
        $i = 0;

        foreach ($flowCounts as $flow => $count) {
            if ($i >= 20) {
                break;
            }

            $rows[] = [
                'flow' => $flow,
                'sessions' => $count,
                'share' => $sessionCount > 0 ? round(($count * 100) / $sessionCount, 1) : 0,
            ];
            $i++;
        }

        return [
            'rows' => $rows,
            'session_count' => $sessionCount,
        ];
    }

    private function buildEngagementRows(array $events)
    {
        $pageStats = [];

        foreach ($events as $event) {
            $page = $event['page'] !== 'Unknown' ? $event['page'] : $event['history_page'];
            if ($page === 'Unknown') {
                continue;
            }

            if (!isset($pageStats[$page])) {
                $pageStats[$page] = [
                    'page' => $page,
                    'visits' => 0,
                    'total_time' => 0.0,
                    'bounce_count' => 0,
                    'durations' => [],
                ];
            }

            $pageStats[$page]['visits']++;
            $pageStats[$page]['total_time'] += $event['duration'];
            $pageStats[$page]['durations'][] = $event['duration'];

            if ($event['duration'] < 5) {
                $pageStats[$page]['bounce_count']++;
            }
        }

        $rows = [];

        foreach ($pageStats as $row) {
            $avgTime = $row['visits'] > 0 ? ($row['total_time'] / $row['visits']) : 0;
            $medianTime = $this->median($row['durations']);
            $bounceRate = $row['visits'] > 0 ? (($row['bounce_count'] * 100) / $row['visits']) : 0;

            $rows[] = [
                'page' => $row['page'],
                'visits' => $row['visits'],
                'total_time' => $row['total_time'],
                'total_time_label' => $this->formatDuration($row['total_time']),
                'avg_time' => $avgTime,
                'avg_time_label' => $this->formatDuration($avgTime),
                'median_time' => $medianTime,
                'median_time_label' => $this->formatDuration($medianTime),
                'bounce_count' => $row['bounce_count'],
                'bounce_rate' => round($bounceRate, 1),
            ];
        }

        $medianPerPage = $rows;
        usort($medianPerPage, function ($a, $b) {
            return $b['median_time'] <=> $a['median_time'];
        });

        $topTime = $rows;
        usort($topTime, function ($a, $b) {
            return $b['total_time'] <=> $a['total_time'];
        });

        $bounce = array_values(array_filter($rows, function ($row) {
            return $row['bounce_count'] > 0;
        }));

        usort($bounce, function ($a, $b) {
            if ($b['bounce_rate'] === $a['bounce_rate']) {
                return $b['visits'] <=> $a['visits'];
            }
            return $b['bounce_rate'] <=> $a['bounce_rate'];
        });

        return [
            'avg_per_page' => array_slice($medianPerPage, 0, 12),
            'top_time' => array_slice($topTime, 0, 12),
            'bounce' => array_slice($bounce, 0, 12),
        ];
    }

    private function buildContextRows(array $events)
    {
        return [
            'countries' => $this->distribution($events, 'country'),
            'languages' => $this->distribution($events, 'language'),
            'os' => $this->distribution($events, 'os'),
            'devices' => $this->distribution($events, 'device'),
            'form_factors' => $this->distribution($events, 'device_type'),
        ];
    }

    private function distribution(array $events, $field)
    {
        $counts = [];

        foreach ($events as $event) {
            $value = isset($event[$field]) ? $event[$field] : 'Unknown';
            if (!isset($counts[$value])) {
                $counts[$value] = 0;
            }
            $counts[$value]++;
        }

        arsort($counts);

        $total = array_sum($counts);
        $rows = [];
        $i = 0;

        foreach ($counts as $value => $count) {
            if ($i >= 10) {
                break;
            }

            $rows[] = [
                'value' => $value,
                'count' => $count,
                'share' => $total > 0 ? round(($count * 100) / $total, 1) : 0,
            ];
            $i++;
        }

        return $rows;
    }

    private function buildLearningActions(array $events)
    {
        $lessonVisits = 0;
        $quizVisits = 0;

        foreach ($events as $event) {
            $page = strtolower((string) ($event['page'] !== 'Unknown' ? $event['page'] : $event['history_page']));

            if ($page === '' || $page === 'unknown') {
                continue;
            }

            if ($this->containsAny($page, ['quiz', 'test', 'game', 'flashcard', 'riddle', 'crossword', 'dictation', 'task'])) {
                $quizVisits++;
                continue;
            }

            if ($this->containsAny($page, ['lesson', 'course', 'book', 'dialog', 'poetry'])) {
                $lessonVisits++;
            }
        }

        return [
            'lesson_visits' => $lessonVisits,
            'quiz_visits' => $quizVisits,
            'total' => $lessonVisits + $quizVisits,
        ];
    }

    private function containsAny($text, array $needles)
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && strpos($text, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    private function appendUnique(array &$pages, $value)
    {
        $last = count($pages) > 0 ? $pages[count($pages) - 1] : null;
        if ($last !== $value) {
            $pages[] = $value;
        }
    }

    private function parseTimestamp($timeValue, $dateValue)
    {
        if ($timeValue !== null && $timeValue !== '') {
            if (is_numeric($timeValue)) {
                $ts = (int) $timeValue;
                if ($ts > 0) {
                    return $ts;
                }
            }

            $ts = strtotime((string) $timeValue);
            if ($ts !== false) {
                return $ts;
            }
        }

        if ($dateValue !== null && $dateValue !== '') {
            $ts = strtotime((string) $dateValue);
            if ($ts !== false) {
                return $ts;
            }
        }

        return null;
    }

    private function parseDurationSeconds($value)
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_numeric($value)) {
            return $this->normalizeDurationNumeric((float) $value);
        }

        $text = trim((string) $value);

        if (strpos($text, ':') !== false) {
            $parts = array_map('trim', explode(':', $text));
            if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                return max(0.0, ((int) $parts[0] * 60) + (int) $parts[1]);
            }
            if (count($parts) === 3 && is_numeric($parts[0]) && is_numeric($parts[1]) && is_numeric($parts[2])) {
                return max(0.0, ((int) $parts[0] * 3600) + ((int) $parts[1] * 60) + (int) $parts[2]);
            }
        }

        if (preg_match('/-?\d+(\.\d+)?/', $text, $matches)) {
            $numeric = (float) $matches[0];
            if (stripos($text, 'ms') !== false) {
                return max(0.0, $numeric / 1000);
            }
            return $this->normalizeDurationNumeric($numeric);
        }

        return 0.0;
    }

    private function normalizeDurationNumeric($numeric)
    {
        return max(0.0, (float) $numeric);
    }

    private function autoConvertDurationFromMsIfNeeded(array $events)
    {
        $positiveDurations = [];
        foreach ($events as $event) {
            if ($event['duration'] > 0) {
                $positiveDurations[] = $event['duration'];
            }
        }

        if (count($positiveDurations) < 10) {
            return $events;
        }

        $medianDuration = $this->median($positiveDurations);

        // If median looks unrealistically high in seconds but reasonable in milliseconds, convert all.
        $shouldConvert = $medianDuration > 1800 && ($medianDuration / 1000) <= 1800;
        if (!$shouldConvert) {
            return $events;
        }

        foreach ($events as &$event) {
            $event['duration'] = $event['duration'] / 1000;
        }
        unset($event);

        return $events;
    }

    private function resolveDateKey($dateValue, $timestamp)
    {
        if ($dateValue !== null && $dateValue !== '') {
            $ts = strtotime((string) $dateValue);
            if ($ts !== false) {
                return date('Y-m-d', $ts);
            }
        }

        if ($timestamp !== null) {
            return date('Y-m-d', $timestamp);
        }

        return 'Unknown';
    }

    private function resolveDeviceType($width)
    {
        if ($width === null || $width <= 0) {
            return 'Unknown';
        }
        if ($width < 768) {
            return 'Mobile';
        }
        return 'Desktop';
    }

    private function normalizePage($value)
    {
        return $this->normalizeValue($value, 'Unknown');
    }

    private function normalizeValue($value, $fallback)
    {
        $string = trim((string) $value);
        return $string !== '' ? $string : $fallback;
    }

    private function median(array $values)
    {
        if (count($values) === 0) {
            return 0.0;
        }

        sort($values, SORT_NUMERIC);
        $count = count($values);
        $middle = (int) floor($count / 2);

        if ($count % 2 === 1) {
            return (float) $values[$middle];
        }

        return ((float) $values[$middle - 1] + (float) $values[$middle]) / 2;
    }

    private function formatDuration($seconds)
    {
        $seconds = (int) round((float) $seconds);

        if ($seconds <= 0) {
            return '0s';
        }
        if ($seconds < 60) {
            return $seconds . 's';
        }
        if ($seconds < 3600) {
            $minutes = (int) floor($seconds / 60);
            $restSeconds = $seconds % 60;
            return $minutes . 'm ' . str_pad((string) $restSeconds, 2, '0', STR_PAD_LEFT) . 's';
        }
        if ($seconds < 86400) {
            $hours = (int) floor($seconds / 3600);
            $minutes = (int) floor(($seconds % 3600) / 60);
            return $hours . 'h ' . str_pad((string) $minutes, 2, '0', STR_PAD_LEFT) . 'm';
        }

        $days = (int) floor($seconds / 86400);
        $hours = (int) floor(($seconds % 86400) / 3600);
        return $days . 'd ' . str_pad((string) $hours, 2, '0', STR_PAD_LEFT) . 'h';
    }
}
