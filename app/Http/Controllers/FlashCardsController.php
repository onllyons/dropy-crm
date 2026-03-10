<?php

namespace App\Http\Controllers;

use App\Services\FlashCardsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FlashCardsController extends Controller
{
    public function v2(FlashCardsService $service): View
    {
        $error = null;
        $modules = collect();
        $globalReusedGroups = collect();
        $globalSameLessonDuplicates = collect();
        $summary = [
            'modules' => 0,
            'lessons' => 0,
            'active_modules' => 0,
            'active_lessons' => 0,
            'items' => 0,
            'reused_groups_global' => 0,
            'reused_rows_global' => 0,
            'same_lesson_duplicate_groups_global' => 0,
            'same_lesson_duplicate_rows_global' => 0,
        ];

        try {
            $data = $service->getV2Data();
            $modules = $data['modules'] ?? collect();
            $summary = $data['summary'] ?? $summary;
            $globalReusedGroups = $data['globalReusedGroups'] ?? collect();
            $globalSameLessonDuplicates = $data['globalSameLessonDuplicates'] ?? collect();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('flash-cards.v2', [
            'modules' => $modules,
            'globalReusedGroups' => $globalReusedGroups,
            'globalSameLessonDuplicates' => $globalSameLessonDuplicates,
            'summary' => $summary,
            'error' => $error,
        ]);
    }

    public function downloadV2Json(Request $request, FlashCardsService $service)
    {
        $type = strtolower(trim((string) $request->query('type', 'all')));
        if (!in_array($type, ['all', 'word'], true)) {
            $type = 'all';
        }

        $payload = $service->getV2ExportPayload($type);
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json)) {
            $json = '{}';
        }

        $fileName = 'flash-cards-v2-' . $type . '-' . now()->format('Ymd-His') . '.json';

        return response($json, 200, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function showV2Progress(Request $request, FlashCardsService $service): View
    {
        $error = null;
        $progress = [
            'filters' => [
                'q' => '',
                'status' => 'all',
            ],
            'summary' => [
                'users_with_attempts' => 0,
                'attempts_total' => 0,
                'completed_attempts' => 0,
                'in_progress_attempts' => 0,
                'completed_lessons_distinct' => 0,
                'total_time_seconds' => 0,
                'catalog_lessons_total' => 0,
            ],
            'users' => collect(),
            'attempts' => collect(),
            'focusedUser' => null,
            'focusedUserProgress' => null,
        ];

        try {
            $progress = $service->getV2AttemptsProgress($request->only(['q', 'status']));
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('flash-cards-v2-progress.index', [
            'progress' => $progress,
            'error' => $error,
        ]);
    }

    public function showV2Lesson(int $lessonId, FlashCardsService $service): View
    {
        $error = null;
        $detail = [
            'lesson' => null,
            'items' => collect(),
            'itemsByType' => collect(),
            'summary' => [
                'items' => 0,
                'with_audio' => 0,
            ],
        ];

        try {
            $detail = $service->getV2LessonDetails($lessonId);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        if (!$detail['lesson'] && $error === null) {
            abort(404);
        }

        return view('flash-cards.v2-lesson', [
            'detail' => $detail,
            'error' => $error,
        ]);
    }

    public function updateV2ItemInline(Request $request, int $itemId, FlashCardsService $service): JsonResponse
    {
        $payload = $request->validate([
            'field' => ['required', 'string', Rule::in(['text_from', 'text_to', 'ipa'])],
            'value' => ['nullable', 'string'],
        ]);

        try {
            $result = $service->updateV2ItemTextField(
                $itemId,
                (string) $payload['field'],
                trim((string) ($payload['value'] ?? ''))
            );

            if (!$result) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Item not found.',
                ], 404);
            }

            return response()->json([
                'ok' => true,
                'message' => 'Saved.',
                'item' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function v2DuplicateGptPreview(Request $request, FlashCardsService $service): JsonResponse
    {
        $payload = $request->validate([
            'type_norm' => ['required', 'string', 'max:100'],
            'text_from' => ['nullable', 'string', 'max:1000'],
            'text_to' => ['nullable', 'string', 'max:1000'],
            'lesson_ids' => ['required', 'array', 'min:2', 'max:10'],
            'lesson_ids.*' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $preview = $service->buildV2DuplicateGptPreview(
                (array) $payload['lesson_ids'],
                (string) $payload['type_norm'],
                trim((string) ($payload['text_from'] ?? '')),
                trim((string) ($payload['text_to'] ?? ''))
            );

            return response()->json([
                'ok' => true,
                'preview' => $preview,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function v2DuplicateGptAsk(Request $request, FlashCardsService $service): JsonResponse
    {
        $payload = $request->validate([
            'type_norm' => ['required', 'string', 'max:100'],
            'text_from' => ['nullable', 'string', 'max:1000'],
            'text_to' => ['nullable', 'string', 'max:1000'],
            'lesson_ids' => ['required', 'array', 'min:2', 'max:10'],
            'lesson_ids.*' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $preview = $service->buildV2DuplicateGptPreview(
                (array) $payload['lesson_ids'],
                (string) $payload['type_norm'],
                trim((string) ($payload['text_from'] ?? '')),
                trim((string) ($payload['text_to'] ?? ''))
            );

            $result = $service->askV2DuplicateGptUpdateSql($preview);

            return response()->json([
                'ok' => true,
                'preview' => $preview,
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function showV2Module(int $moduleId, FlashCardsService $service): View
    {
        $error = null;
        $detail = [
            'module' => null,
            'lessons' => collect(),
            'duplicateGroups' => collect(),
            'reusedGroups' => collect(),
            'summary' => [
                'lessons' => 0,
                'active_lessons' => 0,
                'items' => 0,
                'items_with_audio' => 0,
                'duplicate_rows' => 0,
                'duplicate_groups' => 0,
                'reused_groups' => 0,
            ],
        ];

        try {
            $detail = $service->getV2ModuleDetails($moduleId);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        if (!$detail['module'] && $error === null) {
            abort(404);
        }

        return view('flash-cards.v2-module', [
            'detail' => $detail,
            'error' => $error,
        ]);
    }

    public function showV2ModulePlain(int $moduleId, FlashCardsService $service): View
    {
        $error = null;
        $detail = [
            'module' => null,
            'lessons' => collect(),
            'duplicateGroups' => collect(),
            'reusedGroups' => collect(),
            'summary' => [
                'lessons' => 0,
                'active_lessons' => 0,
                'items' => 0,
                'items_with_audio' => 0,
                'duplicate_rows' => 0,
                'duplicate_groups' => 0,
                'reused_groups' => 0,
            ],
        ];

        try {
            $detail = $service->getV2ModuleDetails($moduleId);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        if (!$detail['module'] && $error === null) {
            abort(404);
        }

        return view('flash-cards.v2-module-plain', [
            'detail' => $detail,
            'error' => $error,
        ]);
    }

    public function wordsList(FlashCardsService $service): View
    {
        $error = null;
        $lessons = collect();
        $outline = collect();
        $stats = [
            'categories' => 0,
            'lessons' => 0,
            'words' => 0,
        ];

        try {
            $lessons = $service->getWordsManualList();
            $outline = $lessons
                ->groupBy('group_category')
                ->map(function ($rows, $groupCode) {
                    $first = $rows->first();
                    $groupTitle = trim((string) ($first->group_title ?? ''));
                    $groupId = is_numeric($first->group_category_id ?? null) ? (int) $first->group_category_id : null;
                    if ($groupTitle === '') {
                        $groupTitle = trim((string) $groupCode) !== '' ? (string) $groupCode : 'Uncategorized';
                    }

                    return (object) [
                        'group_id' => $groupId,
                        'group_code' => $groupCode,
                        'group_title' => $groupTitle,
                        'lessons' => $rows->pluck('title')->filter(function ($title) {
                            return trim((string) $title) !== '';
                        })->values(),
                    ];
                })
                ->sortBy(function ($group) {
                    return $group->group_id === null ? PHP_INT_MAX : (int) $group->group_id;
                })
                ->values();

            $stats['categories'] = $outline->count();
            $stats['lessons'] = $lessons->count();
            $stats['words'] = (int) $lessons->sum(function ($lesson) {
                return (int) ($lesson->words_count ?? 0);
            });
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('flash-cards.words-list', [
            'lessons' => $lessons,
            'outline' => $outline,
            'stats' => $stats,
            'error' => $error,
        ]);
    }

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
