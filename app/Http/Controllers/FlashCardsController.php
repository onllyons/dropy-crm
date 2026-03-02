<?php

namespace App\Http\Controllers;

use App\Services\FlashCardsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class FlashCardsController extends Controller
{
    public function debutIntegrity(Request $request, FlashCardsService $service): View
    {
        $error = null;
        $realCheckEnabled = $request->boolean('real_check', true);
        $realCheckLimit = (int) $request->query('real_check_limit', 500);
        $realCheckLimit = max(10, min($realCheckLimit, 50000));
        $startAfterId = (int) $request->query('start_after_id', 0);
        $startAfterId = max(0, $startAfterId);
        $wordsReport = [
            'totalRows' => 0,
            'missingPathCount' => 0,
            'realCheckEnabled' => $realCheckEnabled,
            'realCheckLimit' => $realCheckLimit,
            'startAfterId' => $startAfterId,
            'checkedRowsCount' => 0,
            'checkedMinId' => null,
            'checkedMaxId' => null,
            'nextStartAfterId' => $startAfterId,
            'missingOnServerCount' => 0,
            'missingOnServerRows' => collect(),
        ];
        $phrasesReport = [
            'totalRows' => 0,
            'missingPathCount' => 0,
            'realCheckEnabled' => $realCheckEnabled,
            'realCheckLimit' => $realCheckLimit,
            'startAfterId' => $startAfterId,
            'checkedRowsCount' => 0,
            'checkedMinId' => null,
            'checkedMaxId' => null,
            'nextStartAfterId' => $startAfterId,
            'missingOnServerCount' => 0,
            'missingOnServerRows' => collect(),
        ];
        $errors = [];

        try {
            $wordsReport = $service->getDebutIntegrityReport($realCheckEnabled, $realCheckLimit, $startAfterId);
        } catch (\Throwable $e) {
            $errors[] = 'Words audio: ' . $e->getMessage();
        }

        try {
            $phrasesReport = $service->getPhrasesDebutIntegrityReport($realCheckEnabled, $realCheckLimit, $startAfterId);
        } catch (\Throwable $e) {
            $errors[] = 'Phrases audio: ' . $e->getMessage();
        }

        if (!empty($errors)) {
            $error = implode(' | ', $errors);
        }

        return view('flash-cards.debut-integrity', [
            'wordsReport' => $wordsReport,
            'phrasesReport' => $phrasesReport,
            'error' => $error,
        ]);
    }

    public function index(Request $request, FlashCardsService $service): View
    {
        $filters = $this->resolveFilters($request);
        $error = null;

        $data = [
            'kpis' => [
                'total_words_lessons' => 0,
                'total_words_items' => 0,
                'total_phrases_lessons' => 0,
                'total_phrases_items' => 0,
                'total_finished_lessons' => 0,
            ],
            'wordsCategories' => null,
            'wordsLessons' => null,
            'phrasesLessons' => null,
            'usersProgress' => null,
            'wordsCategoryOptions' => collect(),
            'wordsLevelOptions' => collect(),
        ];

        try {
            $data = $service->getDashboardData($filters);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('flash-cards.index', [
            'filters' => $filters,
            'kpis' => $data['kpis'],
            'wordsCategories' => $data['wordsCategories'],
            'wordsLessons' => $data['wordsLessons'],
            'phrasesLessons' => $data['phrasesLessons'],
            'usersProgress' => $data['usersProgress'],
            'wordsCategoryOptions' => $data['wordsCategoryOptions'],
            'wordsLevelOptions' => $data['wordsLevelOptions'],
            'error' => $error,
        ]);
    }

    public function showWordsLesson(int $lessonId, Request $request, FlashCardsService $service): View
    {
        $filters = $this->resolveFilters($request);
        $error = null;

        $detail = [
            'lesson' => null,
            'activeTab' => 'content',
            'tabCounts' => [
                'content' => 0,
                'quiz' => 0,
                'history' => 0,
            ],
            'rows' => null,
        ];

        try {
            $detail = $service->getWordsLessonDetails($lessonId, $filters);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        if (!$detail['lesson'] && $error === null) {
            abort(404);
        }

        return view('flash-cards.words-lesson-detail', [
            'filters' => $filters,
            'detail' => $detail,
            'backQuery' => $request->except(['tab', 'tab_page']),
            'error' => $error,
        ]);
    }

    public function showPhrasesLesson(int $lessonId, Request $request, FlashCardsService $service): View
    {
        $filters = $this->resolveFilters($request);
        $error = null;

        $detail = [
            'lesson' => null,
            'activeTab' => 'content',
            'tabCounts' => [
                'content' => 0,
                'history' => 0,
            ],
            'rows' => null,
        ];

        try {
            $detail = $service->getPhrasesLessonDetails($lessonId, $filters);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        if (!$detail['lesson'] && $error === null) {
            abort(404);
        }

        return view('flash-cards.phrases-lesson-detail', [
            'filters' => $filters,
            'detail' => $detail,
            'backQuery' => $request->except(['tab', 'tab_page']),
            'error' => $error,
        ]);
    }

    private function resolveFilters(Request $request): array
    {
        $periodOptions = [
            'today' => 'Today',
            '1d' => 'Last 24h',
            '7d' => 'Last 7 days',
            '30d' => 'Last 30 days',
            'custom' => 'Custom',
        ];

        $period = (string) $request->query('period', '30d');
        if (!array_key_exists($period, $periodOptions)) {
            $period = '30d';
        }

        $dateStartInput = trim((string) $request->query('date_start', ''));
        $dateEndInput = trim((string) $request->query('date_end', ''));

        $range = $this->resolveRange($period, $dateStartInput, $dateEndInput);

        $userId = $this->normalizePositiveInt($request->query('user_id'));
        $wordsLevelRaw = $request->query('words_level');
        $wordsLevel = is_numeric($wordsLevelRaw) ? (int) $wordsLevelRaw : null;

        $wordsFinished = (string) $request->query('words_finished', 'all');
        if (!in_array($wordsFinished, ['all', 'yes', 'no'], true)) {
            $wordsFinished = 'all';
        }

        $phrasesFinished = (string) $request->query('phrases_finished', 'all');
        if (!in_array($phrasesFinished, ['all', 'yes', 'no'], true)) {
            $phrasesFinished = 'all';
        }

        return [
            'period' => $period,
            'period_label' => $periodOptions[$period],
            'period_options' => $periodOptions,
            'date_start' => $range['date_start'],
            'date_end' => $range['date_end'],
            'range_start_ts' => $range['range_start_ts'],
            'range_end_ts' => $range['range_end_ts'],
            'user_id' => $userId,
            'categories_q' => trim((string) $request->query('categories_q', '')),
            'words_q' => trim((string) $request->query('words_q', '')),
            'phrases_q' => trim((string) $request->query('phrases_q', '')),
            'words_category' => trim((string) $request->query('words_category', '')),
            'words_level' => $wordsLevel,
            'words_finished' => $wordsFinished,
            'phrases_finished' => $phrasesFinished,
            'categories_per_page' => $this->normalizePerPage($request->query('categories_per_page'), 20),
            'words_per_page' => $this->normalizePerPage($request->query('words_per_page'), 20),
            'phrases_per_page' => $this->normalizePerPage($request->query('phrases_per_page'), 20),
            'users_per_page' => $this->normalizePerPage($request->query('users_per_page'), 20),
            'details_per_page' => $this->normalizePerPage($request->query('details_per_page'), 50),
            'tab' => trim((string) $request->query('tab', 'content')),
        ];
    }

    private function resolveRange(string $period, string $dateStartInput, string $dateEndInput): array
    {
        $now = now();

        if ($period === 'today') {
            $start = $now->copy()->startOfDay();
            $end = $now->copy()->endOfDay();

            return [
                'date_start' => $start->toDateString(),
                'date_end' => $end->toDateString(),
                'range_start_ts' => $start->timestamp,
                'range_end_ts' => $end->timestamp,
            ];
        }

        if ($period === '1d') {
            $start = $now->copy()->subDay();
            $end = $now->copy();

            return [
                'date_start' => $start->toDateString(),
                'date_end' => $end->toDateString(),
                'range_start_ts' => $start->timestamp,
                'range_end_ts' => $end->timestamp,
            ];
        }

        if ($period === '7d') {
            $start = $now->copy()->subDays(6)->startOfDay();
            $end = $now->copy()->endOfDay();

            return [
                'date_start' => $start->toDateString(),
                'date_end' => $end->toDateString(),
                'range_start_ts' => $start->timestamp,
                'range_end_ts' => $end->timestamp,
            ];
        }

        if ($period === 'custom') {
            $start = $this->normalizeDateInput($dateStartInput);
            $end = $this->normalizeDateInput($dateEndInput);

            if (!$start || !$end) {
                $fallback = $this->resolveRange('30d', '', '');
                return $fallback;
            }

            if ($start->gt($end)) {
                $tmp = $start;
                $start = $end;
                $end = $tmp;
            }

            return [
                'date_start' => $start->toDateString(),
                'date_end' => $end->toDateString(),
                'range_start_ts' => $start->copy()->startOfDay()->timestamp,
                'range_end_ts' => $end->copy()->endOfDay()->timestamp,
            ];
        }

        $start = $now->copy()->subDays(29)->startOfDay();
        $end = $now->copy()->endOfDay();

        return [
            'date_start' => $start->toDateString(),
            'date_end' => $end->toDateString(),
            'range_start_ts' => $start->timestamp,
            'range_end_ts' => $end->timestamp,
        ];
    }

    private function normalizeDateInput(string $value): ?Carbon
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        return Carbon::createFromTimestamp($timestamp);
    }

    private function normalizePerPage($value, int $fallback): int
    {
        $allowed = [20, 50, 100, 200];
        if (is_numeric($value)) {
            $value = (int) $value;
            if (in_array($value, $allowed, true)) {
                return $value;
            }
        }

        return $fallback;
    }

    private function normalizePositiveInt($value): ?int
    {
        if (!is_numeric($value)) {
            return null;
        }

        $value = (int) $value;
        if ($value <= 0) {
            return null;
        }

        return $value;
    }
}
