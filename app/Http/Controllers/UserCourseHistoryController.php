<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class UserCourseHistoryController extends Controller
{
    public function show(int $id)
    {
        $user = DB::table('users')
            ->select('id', 'name', 'username', 'email')
            ->where('id', $id)
            ->first();

        if (!$user) {
            abort(404);
        }

        $error = null;
        $rows = collect();
        $summary = [
            'total_rows' => 0,
            'unique_courses' => 0,
            'started_rows' => 0,
            'completed_rows' => 0,
            'total_time_seconds' => 0,
            'total_time_label' => '0s',
            'series_entries' => 0,
            'series_correct_answers' => 0,
            'series_wrong_answers' => 0,
        ];

        try {
            $rawRows = DB::connection('tenant')
                ->table('course_history')
                ->select('id', 'user_id', 'course_id', 'slides_study', 'quizzes_study', 'series_data', 'time_study', 'start_time', 'end_time')
                ->where('user_id', $id)
                ->orderByDesc('id')
                ->get();

            $courseIds = $rawRows->pluck('course_id')
                ->filter(function ($courseId) {
                    return is_numeric($courseId) && (int) $courseId > 0;
                })
                ->map(function ($courseId) {
                    return (int) $courseId;
                })
                ->unique()
                ->values();

            $coursesById = collect();
            if ($courseIds->isNotEmpty()) {
                $coursesById = DB::connection('tenant')
                    ->table('course')
                    ->select('id', 'url', 'title')
                    ->whereIn('id', $courseIds)
                    ->get()
                    ->keyBy('id');
            }

            $summary['total_rows'] = $rawRows->count();
            $summary['unique_courses'] = $rawRows->pluck('course_id')->filter()->unique()->count();
            $summary['started_rows'] = $rawRows->filter(function ($row) {
                return (int) ($row->start_time ?? 0) > 0;
            })->count();
            $summary['completed_rows'] = $rawRows->filter(function ($row) {
                return (int) ($row->end_time ?? 0) > 0;
            })->count();

            $summary['total_time_seconds'] = (int) $rawRows->sum(function ($row) {
                return is_numeric($row->time_study ?? null) ? (int) $row->time_study : 0;
            });
            $summary['total_time_label'] = $this->formatDuration($summary['total_time_seconds']);

            $seriesEntries = 0;
            $seriesCorrectAnswers = 0;
            $seriesWrongAnswers = 0;

            $rows = $rawRows->map(function ($row) use (&$seriesEntries, &$seriesCorrectAnswers, &$seriesWrongAnswers, $coursesById) {
                $timeStudySeconds = is_numeric($row->time_study ?? null) ? (int) $row->time_study : 0;
                $seriesParsed = $this->parseSeriesData($row->series_data ?? null);
                $courseId = (int) ($row->course_id ?? 0);
                $course = $coursesById->get($courseId);

                foreach ($seriesParsed['items'] as $seriesItem) {
                    $seriesEntries++;
                    $seriesCorrectAnswers += $seriesItem['correct_answers'];
                    $seriesWrongAnswers += $seriesItem['wrong_answers'];
                }

                return [
                    'id' => (int) ($row->id ?? 0),
                    'user_id' => (int) ($row->user_id ?? 0),
                    'course_id' => $courseId,
                    'course_slug' => trim((string) ($course->url ?? '')),
                    'course_title' => trim((string) ($course->title ?? '')),
                    'slides_study' => (int) ($row->slides_study ?? 0),
                    'quizzes_study' => (int) ($row->quizzes_study ?? 0),
                    'series_data_raw' => (string) ($row->series_data ?? ''),
                    'series_items' => $seriesParsed['items'],
                    'series_error' => $seriesParsed['error'],
                    'series_count' => count($seriesParsed['items']),
                    'time_study_seconds' => $timeStudySeconds,
                    'time_study_label' => $this->formatDuration($timeStudySeconds),
                    'start_time' => is_numeric($row->start_time ?? null) ? (int) $row->start_time : 0,
                    'end_time' => is_numeric($row->end_time ?? null) ? (int) $row->end_time : 0,
                    'start_time_label' => $this->formatDateTime($row->start_time ?? null),
                    'end_time_label' => $this->formatDateTime($row->end_time ?? null),
                ];
            });

            $summary['series_entries'] = $seriesEntries;
            $summary['series_correct_answers'] = $seriesCorrectAnswers;
            $summary['series_wrong_answers'] = $seriesWrongAnswers;
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('user-course-history', [
            'user' => $user,
            'rows' => $rows,
            'summary' => $summary,
            'error' => $error,
        ]);
    }

    private function parseSeriesData($value)
    {
        if ($value === null) {
            return ['items' => [], 'error' => null];
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return ['items' => [], 'error' => null];
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return ['items' => [], 'error' => 'Invalid JSON'];
        }

        $items = [];
        foreach ($decoded as $seriesKey => $seriesRow) {
            if (!is_array($seriesRow)) {
                continue;
            }

            $timeCarousel = is_numeric($seriesRow['time_carousel'] ?? null) ? (int) $seriesRow['time_carousel'] : 0;
            $scoreCarousel = is_numeric($seriesRow['score_carousel'] ?? null) ? (int) $seriesRow['score_carousel'] : 0;
            $timeQuiz = is_numeric($seriesRow['time_quiz'] ?? null) ? (int) $seriesRow['time_quiz'] : 0;
            $wrongAnswers = is_numeric($seriesRow['wrongAnswers'] ?? null) ? (int) $seriesRow['wrongAnswers'] : 0;
            $correctAnswers = is_numeric($seriesRow['correctAnswers'] ?? null) ? (int) $seriesRow['correctAnswers'] : 0;

            $items[] = [
                'series' => (string) $seriesKey,
                'series_sort' => is_numeric($seriesKey) ? (int) $seriesKey : PHP_INT_MAX,
                'time_carousel' => $timeCarousel,
                'time_carousel_label' => $this->formatDuration($timeCarousel),
                'score_carousel' => $scoreCarousel,
                'time_quiz' => $timeQuiz,
                'time_quiz_label' => $this->formatDuration($timeQuiz),
                'wrong_answers' => $wrongAnswers,
                'correct_answers' => $correctAnswers,
            ];
        }

        usort($items, function ($a, $b) {
            if ($a['series_sort'] === $b['series_sort']) {
                return strcmp($a['series'], $b['series']);
            }

            return $a['series_sort'] <=> $b['series_sort'];
        });

        foreach ($items as &$item) {
            unset($item['series_sort']);
        }
        unset($item);

        return ['items' => $items, 'error' => null];
    }

    private function formatDateTime($value)
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (is_numeric($value)) {
            $ts = (int) $value;
            return $ts > 0 ? date('d.m.Y H:i:s', $ts) : '-';
        }

        $ts = strtotime((string) $value);
        return $ts ? date('d.m.Y H:i:s', $ts) : (string) $value;
    }

    private function formatDuration($seconds)
    {
        if (!is_numeric($seconds)) {
            return '0s';
        }

        $seconds = (int) $seconds;
        if ($seconds <= 0) {
            return '0s';
        }

        $hours = (int) floor($seconds / 3600);
        $minutes = (int) floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = $hours . 'h';
        }
        if ($minutes > 0) {
            $parts[] = $minutes . 'm';
        }
        if ($secs > 0 || empty($parts)) {
            $parts[] = $secs . 's';
        }

        return implode(' ', $parts);
    }
}
