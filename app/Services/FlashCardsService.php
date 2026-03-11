<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FlashCardsService
{
    private const PROD_BASE_URL = 'https://www.language.onllyons.com';
    private const FLASHCARDS_AUDIO_BASE = 'https://www.language.onllyons.com/ru/ru-en/packs/assest/game-card-word/content/audio_file/en-to-ru';
    private const FLASHCARDS_PHRASES_AUDIO_BASE = 'https://www.language.onllyons.com/ru/ru-en/packs/assest/flashcard-questions-and-sentences/audio/audio-en-ru';

    public function getModules(): Collection
    {
        return DB::connection('tenant')
            ->table('flashcard_modules')
            ->select('id', 'title', 'slug', 'description', 'sort_order', 'is_active', 'created_at', 'updated_at')
            ->orderBy('id')
            ->get();
    }

    public function getV2Data(): array
    {
        $modules = DB::connection('tenant')
            ->table('flashcard_modules')
            ->select('id', 'title', 'slug', 'description', 'sort_order', 'is_active', 'created_at', 'updated_at')
            ->orderByRaw('CASE WHEN sort_order IS NULL THEN 1 ELSE 0 END')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->keyBy(function ($row) {
                return (int) ($row->id ?? 0);
            });

        $lessonsGrouped = DB::connection('tenant')
            ->table('flashcard_lessons')
            ->select('id', 'module_id', 'title', 'url', 'lesson_type', 'level', 'sort_order', 'is_active', 'created_at', 'updated_at')
            ->orderByRaw('CASE WHEN sort_order IS NULL THEN 1 ELSE 0 END')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy(function ($row) {
                return (int) ($row->module_id ?? 0);
            });

        $itemsCountByModule = DB::connection('tenant')
            ->table('flashcard_items as i')
            ->join('flashcard_lessons as l', 'l.id', '=', 'i.lesson_id')
            ->select('l.module_id', DB::raw('COUNT(i.id) as items_count'))
            ->groupBy('l.module_id')
            ->pluck('items_count', 'l.module_id');

        $moduleCards = $modules->map(function ($module, $moduleId) use ($lessonsGrouped, $itemsCountByModule) {
            $lessonRows = $lessonsGrouped->get((int) $moduleId, collect())
                ->map(function ($lesson) {
                    $lesson->is_active = (int) ($lesson->is_active ?? 0);
                    return $lesson;
                })
                ->values();

            $module->is_active = (int) ($module->is_active ?? 0);
            $module->lessons = $lessonRows;
            $module->lessons_count = $lessonRows->count();
            $module->active_lessons_count = (int) $lessonRows->where('is_active', 1)->count();
            $module->items_count = (int) ($itemsCountByModule->get((int) $moduleId, 0));

            return $module;
        })->values();

        $summary = [
            'modules' => $moduleCards->count(),
            'lessons' => (int) $moduleCards->sum('lessons_count'),
            'active_modules' => (int) $moduleCards->where('is_active', 1)->count(),
            'active_lessons' => (int) $moduleCards->sum('active_lessons_count'),
            'items' => (int) $moduleCards->sum('items_count'),
            'reused_groups_global' => 0,
            'reused_rows_global' => 0,
            'same_lesson_duplicate_groups_global' => 0,
            'same_lesson_duplicate_rows_global' => 0,
        ];

        $globalTypeExpr = "CASE LOWER(TRIM(COALESCE(i.type, ''))) WHEN 'questions' THEN 'question' WHEN 'question' THEN 'question' WHEN 'phrases' THEN 'phrase' WHEN 'phrase' THEN 'phrase' WHEN 'words' THEN 'word' WHEN 'word' THEN 'word' ELSE LOWER(TRIM(COALESCE(i.type, ''))) END";
        $globalFromExpr = "TRIM(COALESCE(i.text_from, ''))";
        $globalToExpr = "TRIM(COALESCE(i.text_to, ''))";

        $globalReusedGroups = DB::connection('tenant')
            ->table('flashcard_items as i')
            ->join('flashcard_lessons as l', 'l.id', '=', 'i.lesson_id')
            ->selectRaw($globalTypeExpr . ' as type')
            ->selectRaw($globalFromExpr . ' as text_from')
            ->selectRaw($globalToExpr . ' as text_to')
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('COUNT(DISTINCT i.lesson_id) as lesson_count')
            ->selectRaw('COUNT(DISTINCT l.module_id) as module_count')
            ->selectRaw("GROUP_CONCAT(DISTINCT i.lesson_id ORDER BY i.lesson_id ASC SEPARATOR ', ') as lesson_ids")
            ->selectRaw("GROUP_CONCAT(DISTINCT l.module_id ORDER BY l.module_id ASC SEPARATOR ', ') as module_ids")
            ->selectRaw("GROUP_CONCAT(DISTINCT LOWER(TRIM(COALESCE(i.type, ''))) ORDER BY LOWER(TRIM(COALESCE(i.type, ''))) ASC SEPARATOR ', ') as raw_types")
            ->where(function ($query) {
                $query->whereRaw("TRIM(COALESCE(i.text_from, '')) <> ''")
                    ->orWhereRaw("TRIM(COALESCE(i.text_to, '')) <> ''");
            })
            ->groupByRaw($globalTypeExpr . ', ' . $globalFromExpr . ', ' . $globalToExpr)
            ->havingRaw('COUNT(DISTINCT i.lesson_id) > 1')
            ->orderByDesc('total_count')
            ->get();

        $summary['reused_groups_global'] = (int) $globalReusedGroups->count();
        $summary['reused_rows_global'] = (int) $globalReusedGroups->sum(function ($row) {
            return max(((int) ($row->total_count ?? 0)) - 1, 0);
        });

        $globalSameLessonDuplicates = DB::connection('tenant')
            ->table('flashcard_items as i')
            ->join('flashcard_lessons as l', 'l.id', '=', 'i.lesson_id')
            ->select('l.module_id', 'l.id as lesson_id', 'l.title as lesson_title')
            ->selectRaw($globalTypeExpr . ' as type')
            ->selectRaw($globalFromExpr . ' as text_from')
            ->selectRaw($globalToExpr . ' as text_to')
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw("GROUP_CONCAT(i.id ORDER BY i.id ASC SEPARATOR ', ') as row_ids")
            ->selectRaw("GROUP_CONCAT(DISTINCT LOWER(TRIM(COALESCE(i.type, ''))) ORDER BY LOWER(TRIM(COALESCE(i.type, ''))) ASC SEPARATOR ', ') as raw_types")
            ->where(function ($query) {
                $query->whereRaw("TRIM(COALESCE(i.text_from, '')) <> ''")
                    ->orWhereRaw("TRIM(COALESCE(i.text_to, '')) <> ''");
            })
            ->groupBy('l.module_id', 'l.id', 'l.title')
            ->groupByRaw($globalTypeExpr . ', ' . $globalFromExpr . ', ' . $globalToExpr)
            ->havingRaw('COUNT(*) > 1')
            ->orderByDesc('total_count')
            ->orderBy('l.module_id')
            ->orderBy('l.id')
            ->get();

        $summary['same_lesson_duplicate_groups_global'] = (int) $globalSameLessonDuplicates->count();
        $summary['same_lesson_duplicate_rows_global'] = (int) $globalSameLessonDuplicates->sum(function ($row) {
            return max(((int) ($row->total_count ?? 0)) - 1, 0);
        });

        return [
            'modules' => $moduleCards,
            'summary' => $summary,
            'globalReusedGroups' => $globalReusedGroups,
            'globalSameLessonDuplicates' => $globalSameLessonDuplicates,
        ];
    }

    public function getV2ExportPayload(string $type = 'all'): array
    {
        $mode = strtolower(trim($type)) === 'word' ? 'word' : 'all';

        $modules = DB::connection('tenant')
            ->table('flashcard_modules')
            ->select('id', 'title', 'slug', 'description', 'sort_order', 'is_active')
            ->orderByRaw('CASE WHEN sort_order IS NULL THEN 1 ELSE 0 END')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->keyBy(function ($row) {
                return (int) ($row->id ?? 0);
            });

        $lessonsByModule = DB::connection('tenant')
            ->table('flashcard_lessons')
            ->select('id', 'module_id', 'title', 'url', 'lesson_type', 'level', 'sort_order', 'is_active')
            ->orderByRaw('CASE WHEN sort_order IS NULL THEN 1 ELSE 0 END')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy(function ($row) {
                return (int) ($row->module_id ?? 0);
            });

        $itemsQuery = DB::connection('tenant')
            ->table('flashcard_items')
            ->select('id', 'lesson_id', 'type', 'text_from', 'text_to', 'ipa')
            ->orderBy('lesson_id')
            ->orderBy('id');

        if ($mode === 'word') {
            $itemsQuery->whereRaw("LOWER(TRIM(COALESCE(type, ''))) IN ('word', 'words')");
        }

        $itemsByLesson = $itemsQuery
            ->get()
            ->groupBy(function ($row) {
                return (int) ($row->lesson_id ?? 0);
            });

        $modulesPayload = $modules->map(function ($module, $moduleId) use ($lessonsByModule, $itemsByLesson, $mode) {
            $lessonPayload = collect($lessonsByModule->get((int) $moduleId, collect()))
                ->map(function ($lesson) use ($itemsByLesson, $mode) {
                    $isWordsLesson = strtolower(trim((string) ($lesson->lesson_type ?? ''))) === 'words';
                    if ($mode === 'word' && !$isWordsLesson) {
                        return null;
                    }

                    $rows = collect($itemsByLesson->get((int) ($lesson->id ?? 0), collect()));
                    if ($mode === 'word') {
                        $rows = $rows->filter(function ($item) {
                            return strtolower(trim((string) ($item->type ?? ''))) === 'word';
                        })->values();
                    }

                    if ($rows->isEmpty()) {
                        return null;
                    }

                    $items = $rows->map(function ($item) use ($isWordsLesson) {
                        $itemRow = [
                            'id' => (int) ($item->id ?? 0),
                            'text_from' => trim((string) ($item->text_from ?? '')),
                            'text_to' => trim((string) ($item->text_to ?? '')),
                            'type' => trim((string) ($item->type ?? '')),
                        ];

                        if ($isWordsLesson) {
                            $itemRow['ipa'] = trim((string) ($item->ipa ?? ''));
                        }

                        return $itemRow;
                    })->values()->all();

                    return [
                        'lesson_id' => (int) ($lesson->id ?? 0),
                        'title' => trim((string) ($lesson->title ?? '')),
                        'url' => trim((string) ($lesson->url ?? '')),
                        'level' => trim((string) ($lesson->level ?? '')),
                        'type' => trim((string) ($lesson->lesson_type ?? '')),
                        'items_total' => count($items),
                        'items' => $items,
                    ];
                })
                ->filter()
                ->values()
                ->all();

            if (empty($lessonPayload)) {
                return null;
            }

            return [
                'module_id' => (int) ($module->id ?? 0),
                'title' => trim((string) ($module->title ?? '')),
                'slug' => trim((string) ($module->slug ?? '')),
                'description' => trim((string) ($module->description ?? '')),
                'is_active' => (int) ($module->is_active ?? 0),
                'lessons_total' => count($lessonPayload),
                'lessons' => $lessonPayload,
            ];
        })->filter()->values()->all();

        return [
            'exported_at' => now()->toDateTimeString(),
            'type' => $mode,
            'modules_total' => count($modulesPayload),
            'modules' => $modulesPayload,
        ];
    }

    public function getV2AttemptsProgress(array $filters = []): array
    {
        $search = trim((string) ($filters['q'] ?? ''));
        $status = strtolower(trim((string) ($filters['status'] ?? 'all')));
        if (!in_array($status, ['all', 'completed', 'in_progress'], true)) {
            $status = 'all';
        }

        $usersPerPage = 25;
        $attemptsPerPage = 50;
        $totalLessons = (int) DB::connection('tenant')->table('flashcard_lessons')->count();

        $matchingUserIds = $this->resolveFlashcardAttemptUserIds($search);
        $baseQuery = DB::connection('tenant')
            ->table('flashcard_lesson_attempts as a')
            ->leftJoin('flashcard_lessons as l', 'l.id', '=', 'a.lesson_id')
            ->leftJoin('flashcard_modules as m', 'm.id', '=', 'l.module_id');

        if ($matchingUserIds !== null) {
            if ($matchingUserIds->isEmpty()) {
                $baseQuery->whereRaw('1 = 0');
            } else {
                $baseQuery->whereIn('a.user_id', $matchingUserIds->all());
            }
        }

        if ($status !== 'all') {
            $baseQuery->where('a.status', $status);
        }

        $summaryBase = clone $baseQuery;
        $summary = [
            'users_with_attempts' => (int) (clone $summaryBase)->distinct()->count('a.user_id'),
            'attempts_total' => (int) (clone $summaryBase)->count(),
            'completed_attempts' => (int) (clone $summaryBase)->where('a.status', 'completed')->count(),
            'in_progress_attempts' => (int) (clone $summaryBase)->where('a.status', 'in_progress')->count(),
            'completed_lessons_distinct' => (int) (clone $summaryBase)
                ->where('a.status', 'completed')
                ->distinct()
                ->count(DB::raw("CONCAT(a.user_id, '#', a.lesson_id)")),
            'total_time_seconds' => (int) ((clone $summaryBase)
                ->selectRaw('COALESCE(SUM(COALESCE(a.lesson_time_seconds, 0) + COALESCE(a.quiz_time_seconds, 0)), 0) as total_time_seconds')
                ->value('total_time_seconds') ?? 0),
            'catalog_lessons_total' => $totalLessons,
        ];

        $usersQuery = clone $baseQuery;
        $usersPaginator = $usersQuery
            ->select('a.user_id')
            ->selectRaw('COUNT(*) as attempts_total')
            ->selectRaw("SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END) as completed_attempts")
            ->selectRaw("SUM(CASE WHEN a.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_attempts")
            ->selectRaw("COUNT(DISTINCT CASE WHEN a.status = 'completed' THEN a.lesson_id END) as completed_lessons")
            ->selectRaw('COALESCE(SUM(COALESCE(a.lesson_time_seconds, 0) + COALESCE(a.quiz_time_seconds, 0)), 0) as total_time_seconds')
            ->selectRaw('COALESCE(SUM(COALESCE(a.questions_total, 0)), 0) as questions_total')
            ->selectRaw('COALESCE(SUM(COALESCE(a.answers_correct, 0)), 0) as answers_correct')
            ->selectRaw('COALESCE(SUM(COALESCE(a.answers_wrong, 0)), 0) as answers_wrong')
            ->selectRaw('MAX(COALESCE(a.updated_at, a.completed_at, a.started_at, a.created_at)) as last_activity_at')
            ->groupBy('a.user_id')
            ->orderByDesc('completed_lessons')
            ->orderByDesc('attempts_total')
            ->orderByDesc('last_activity_at')
            ->paginate($usersPerPage, ['*'], 'users_page')
            ->withQueryString();

        $usersById = $this->getUsersByIds(collect($usersPaginator->items())->pluck('user_id'));
        $usersPaginator->getCollection()->transform(function ($row) use ($usersById, $totalLessons) {
            $user = $usersById->get((int) ($row->user_id ?? 0));
            $completedLessons = (int) ($row->completed_lessons ?? 0);
            $questionsTotal = (int) ($row->questions_total ?? 0);
            $answersCorrect = (int) ($row->answers_correct ?? 0);

            $row->user_label = $user
                ? trim((string) (($user->username ?? '') !== '' ? $user->username : ($user->name ?? '')))
                : null;
            $row->user_name = $user ? trim((string) ($user->name ?? '')) : null;
            $row->progress_percent = $totalLessons > 0
                ? round(($completedLessons / $totalLessons) * 100, 1)
                : 0.0;
            $row->accuracy_percent = $questionsTotal > 0
                ? round(($answersCorrect / $questionsTotal) * 100, 1)
                : null;

            return $row;
        });

        $attemptsQuery = clone $baseQuery;
        $attemptsPaginator = $attemptsQuery
            ->select(
                'a.id',
                'a.user_id',
                'a.lesson_id',
                'a.attempt_number',
                'a.status',
                'a.started_at',
                'a.completed_at',
                'a.lesson_time_seconds',
                'a.quiz_time_seconds',
                'a.questions_total',
                'a.answers_correct',
                'a.answers_wrong',
                'a.created_at',
                'a.updated_at',
                'l.title as lesson_title',
                'l.level as lesson_level',
                'l.lesson_type',
                'm.id as module_id',
                'm.title as module_title'
            )
            ->orderByDesc('a.id')
            ->paginate($attemptsPerPage, ['*'], 'attempts_page')
            ->withQueryString();

        $attemptUsersById = $this->getUsersByIds(collect($attemptsPaginator->items())->pluck('user_id'));
        $attemptsPaginator->getCollection()->transform(function ($row) use ($attemptUsersById) {
            $user = $attemptUsersById->get((int) ($row->user_id ?? 0));
            $questionsTotal = (int) ($row->questions_total ?? 0);
            $answersCorrect = (int) ($row->answers_correct ?? 0);

            $row->user_label = $user
                ? trim((string) (($user->username ?? '') !== '' ? $user->username : ($user->name ?? '')))
                : null;
            $row->user_name = $user ? trim((string) ($user->name ?? '')) : null;
            $row->accuracy_percent = $questionsTotal > 0
                ? round(($answersCorrect / $questionsTotal) * 100, 1)
                : null;
            $row->total_time_seconds = (int) ($row->lesson_time_seconds ?? 0) + (int) ($row->quiz_time_seconds ?? 0);

            return $row;
        });

        $focusedUserId = null;
        if ($matchingUserIds !== null && $matchingUserIds->count() === 1) {
            $focusedUserId = (int) $matchingUserIds->first();
        } elseif ($search !== '' && method_exists($usersPaginator, 'total') && (int) $usersPaginator->total() === 1) {
            $focusedRow = collect($usersPaginator->items())->first();
            $focusedUserId = (int) ($focusedRow->user_id ?? 0);
        }

        $focusedUser = null;
        $focusedUserProgress = null;
        if ($focusedUserId > 0) {
            $focusedUserRow = $this->getUsersByIds(collect([$focusedUserId]))->get($focusedUserId);
            $focusedUser = [
                'id' => $focusedUserId,
                'label' => $focusedUserRow
                    ? trim((string) (($focusedUserRow->username ?? '') !== '' ? $focusedUserRow->username : ($focusedUserRow->name ?? '')))
                    : ('User #' . $focusedUserId),
                'name' => $focusedUserRow ? trim((string) ($focusedUserRow->name ?? '')) : null,
            ];
            $focusedUserProgress = $this->getV2ProgressForUser($focusedUserId);
        }

        return [
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
            'summary' => $summary,
            'users' => $usersPaginator,
            'attempts' => $attemptsPaginator,
            'focusedUser' => $focusedUser,
            'focusedUserProgress' => $focusedUserProgress,
        ];
    }

    public function getV2ProgressForUser(int $userId): array
    {
        $summary = [
            'attempts_total' => 0,
            'completed_attempts' => 0,
            'in_progress_attempts' => 0,
            'completed_lessons' => 0,
            'started_lessons' => 0,
            'catalog_lessons_total' => (int) DB::connection('tenant')->table('flashcard_lessons')->count(),
            'progress_percent' => 0.0,
            'total_time_seconds' => 0,
            'questions_total' => 0,
            'answers_correct' => 0,
            'answers_wrong' => 0,
            'accuracy_percent' => null,
            'last_activity_at' => null,
        ];

        $baseQuery = DB::connection('tenant')
            ->table('flashcard_lesson_attempts as a')
            ->where('a.user_id', $userId);

        $summary['attempts_total'] = (int) (clone $baseQuery)->count();
        $summary['completed_attempts'] = (int) (clone $baseQuery)->where('a.status', 'completed')->count();
        $summary['in_progress_attempts'] = (int) (clone $baseQuery)->where('a.status', 'in_progress')->count();
        $summary['completed_lessons'] = (int) (clone $baseQuery)
            ->where('a.status', 'completed')
            ->distinct()
            ->count('a.lesson_id');
        $summary['started_lessons'] = (int) (clone $baseQuery)
            ->distinct()
            ->count('a.lesson_id');
        $summary['total_time_seconds'] = (int) ((clone $baseQuery)
            ->selectRaw('COALESCE(SUM(COALESCE(a.lesson_time_seconds, 0) + COALESCE(a.quiz_time_seconds, 0)), 0) as total_time_seconds')
            ->value('total_time_seconds') ?? 0);
        $summary['questions_total'] = (int) ((clone $baseQuery)
            ->selectRaw('COALESCE(SUM(COALESCE(a.questions_total, 0)), 0) as questions_total')
            ->value('questions_total') ?? 0);
        $summary['answers_correct'] = (int) ((clone $baseQuery)
            ->selectRaw('COALESCE(SUM(COALESCE(a.answers_correct, 0)), 0) as answers_correct')
            ->value('answers_correct') ?? 0);
        $summary['answers_wrong'] = (int) ((clone $baseQuery)
            ->selectRaw('COALESCE(SUM(COALESCE(a.answers_wrong, 0)), 0) as answers_wrong')
            ->value('answers_wrong') ?? 0);
        $summary['last_activity_at'] = (clone $baseQuery)
            ->selectRaw('MAX(COALESCE(a.updated_at, a.completed_at, a.started_at, a.created_at)) as last_activity_at')
            ->value('last_activity_at');
        $summary['progress_percent'] = $summary['catalog_lessons_total'] > 0
            ? round(($summary['completed_lessons'] / $summary['catalog_lessons_total']) * 100, 1)
            : 0.0;
        $summary['accuracy_percent'] = $summary['questions_total'] > 0
            ? round(($summary['answers_correct'] / $summary['questions_total']) * 100, 1)
            : null;

        $moduleProgress = DB::connection('tenant')
            ->table('flashcard_modules as m')
            ->join('flashcard_lessons as l', 'l.module_id', '=', 'm.id')
            ->leftJoin('flashcard_lesson_attempts as a', function ($join) use ($userId) {
                $join->on('a.lesson_id', '=', 'l.id')
                    ->where('a.user_id', '=', $userId);
            })
            ->select('m.id as module_id', 'm.title as module_title')
            ->selectRaw('COUNT(DISTINCT l.id) as lessons_total')
            ->selectRaw("COUNT(DISTINCT CASE WHEN a.status = 'completed' THEN l.id END) as completed_lessons")
            ->selectRaw('COUNT(a.id) as attempts_total')
            ->selectRaw('COALESCE(SUM(COALESCE(a.lesson_time_seconds, 0) + COALESCE(a.quiz_time_seconds, 0)), 0) as total_time_seconds')
            ->groupBy('m.id', 'm.title')
            ->orderByDesc('completed_lessons')
            ->orderByDesc('attempts_total')
            ->orderBy('m.id')
            ->get()
            ->map(function ($row) {
                $lessonsTotal = (int) ($row->lessons_total ?? 0);
                $completedLessons = (int) ($row->completed_lessons ?? 0);
                $row->progress_percent = $lessonsTotal > 0
                    ? round(($completedLessons / $lessonsTotal) * 100, 1)
                    : 0.0;

                return $row;
            })
            ->filter(function ($row) {
                return (int) ($row->attempts_total ?? 0) > 0;
            })
            ->values();

        $recentAttempts = DB::connection('tenant')
            ->table('flashcard_lesson_attempts as a')
            ->leftJoin('flashcard_lessons as l', 'l.id', '=', 'a.lesson_id')
            ->leftJoin('flashcard_modules as m', 'm.id', '=', 'l.module_id')
            ->select(
                'a.id',
                'a.lesson_id',
                'a.attempt_number',
                'a.status',
                'a.started_at',
                'a.completed_at',
                'a.lesson_time_seconds',
                'a.quiz_time_seconds',
                'a.questions_total',
                'a.answers_correct',
                'a.answers_wrong',
                'a.quiz_data',
                'a.created_at',
                'a.updated_at',
                'l.title as lesson_title',
                'l.level as lesson_level',
                'l.lesson_type',
                'm.id as module_id',
                'm.title as module_title'
            )
            ->where('a.user_id', $userId)
            ->orderByDesc('a.id')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                return $this->hydrateV2AttemptRow($row, true);
            });

        return [
            'summary' => $summary,
            'moduleProgress' => $moduleProgress,
            'recentAttempts' => $recentAttempts,
            'error' => null,
        ];
    }

    private function hydrateV2AttemptRow($row, bool $includeQuizData = false)
    {
        $questionsTotal = (int) ($row->questions_total ?? 0);
        $answersCorrect = (int) ($row->answers_correct ?? 0);
        $row->accuracy_percent = $questionsTotal > 0
            ? round(($answersCorrect / $questionsTotal) * 100, 1)
            : null;
        $row->total_time_seconds = (int) ($row->lesson_time_seconds ?? 0) + (int) ($row->quiz_time_seconds ?? 0);

        if (!$includeQuizData) {
            return $row;
        }

        $row->quiz_data_summary = [];
        $row->quiz_data_steps = collect();
        $row->quiz_data_pretty = null;
        $row->quiz_data_error = null;

        $rawQuizData = trim((string) ($row->quiz_data ?? ''));
        if ($rawQuizData === '') {
            return $row;
        }

        $decodedQuizData = json_decode($rawQuizData, true);
        if (!is_array($decodedQuizData)) {
            $row->quiz_data_error = 'Invalid JSON';
            $row->quiz_data_pretty = $rawQuizData;

            return $row;
        }

        $steps = collect(isset($decodedQuizData['steps']) && is_array($decodedQuizData['steps']) ? $decodedQuizData['steps'] : [])
            ->values()
            ->map(function ($step, $index) {
                $step = is_array($step) ? $step : [];
                $timestamp = isset($step['timestamp']) ? (int) $step['timestamp'] : 0;

                return (object) [
                    'index' => $index + 1,
                    'item_id' => isset($step['item_id']) ? (int) $step['item_id'] : 0,
                    'step_id' => trim((string) ($step['step_id'] ?? '')),
                    'quiz_type' => trim((string) ($step['quiz_type'] ?? '')),
                    'result' => trim((string) ($step['result'] ?? '')),
                    'use_hint' => !empty($step['use_hint']),
                    'timestamp' => $timestamp,
                    'timestamp_label' => $timestamp > 0 ? date('Y-m-d H:i:s', (int) floor($timestamp / 1000)) : null,
                ];
            });

        $row->quiz_data_steps = $steps;
        $row->quiz_data_summary = [
            'version' => isset($decodedQuizData['version']) ? (int) $decodedQuizData['version'] : null,
            'lesson_index' => isset($decodedQuizData['lesson_index']) ? (int) $decodedQuizData['lesson_index'] : null,
            'current_index' => isset($decodedQuizData['current_index']) ? (int) $decodedQuizData['current_index'] : null,
            'done_steps' => isset($decodedQuizData['done_steps']) ? (int) $decodedQuizData['done_steps'] : null,
            'total_steps' => isset($decodedQuizData['total_steps']) ? (int) $decodedQuizData['total_steps'] : $steps->count(),
            'show_quiz' => array_key_exists('show_quiz', $decodedQuizData) ? (bool) $decodedQuizData['show_quiz'] : null,
            'client_rev' => isset($decodedQuizData['client_rev']) ? (int) $decodedQuizData['client_rev'] : null,
            'updated_at' => isset($decodedQuizData['updated_at']) ? (int) $decodedQuizData['updated_at'] : null,
            'updated_at_label' => isset($decodedQuizData['updated_at']) && (int) $decodedQuizData['updated_at'] > 0
                ? date('Y-m-d H:i:s', (int) floor(((int) $decodedQuizData['updated_at']) / 1000))
                : null,
            'steps_total' => $steps->count(),
            'correct_steps' => $steps->filter(function ($step) {
                return $step->result === 'correct';
            })->count(),
            'incorrect_steps' => $steps->filter(function ($step) {
                return $step->result === 'incorrect';
            })->count(),
            'hint_used_steps' => $steps->filter(function ($step) {
                return $step->use_hint;
            })->count(),
            'quiz_types' => $steps
                ->pluck('quiz_type')
                ->filter()
                ->unique()
                ->values()
                ->all(),
        ];
        $prettyQuizData = json_encode($decodedQuizData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $row->quiz_data_pretty = is_string($prettyQuizData) ? $prettyQuizData : $rawQuizData;

        return $row;
    }

    public function getV2LessonDetails(int $lessonId): array
    {
        $lesson = DB::connection('tenant')
            ->table('flashcard_lessons as l')
            ->leftJoin('flashcard_modules as m', 'm.id', '=', 'l.module_id')
            ->select(
                'l.id',
                'l.module_id',
                'l.title',
                'l.url',
                'l.lesson_type',
                'l.level',
                'l.sort_order',
                'l.is_active',
                'l.created_at',
                'l.updated_at',
                'm.title as module_title',
                'm.slug as module_slug',
                'm.is_active as module_is_active'
            )
            ->where('l.id', $lessonId)
            ->first();

        if (!$lesson) {
            return [
                'lesson' => null,
                'items' => collect(),
                'itemsByType' => collect(),
                'summary' => [
                    'items' => 0,
                    'with_audio' => 0,
                ],
            ];
        }

        $items = DB::connection('tenant')
            ->table('flashcard_items')
            ->select('id', 'lesson_id', 'type', 'text_from', 'text_to', 'ipa', 'audio', 'created_at', 'updated_at')
            ->where('lesson_id', $lessonId)
            ->orderBy('id')
            ->get();

        $lesson->is_active = (int) ($lesson->is_active ?? 0);
        $lesson->module_is_active = (int) ($lesson->module_is_active ?? 0);

        return [
            'lesson' => $lesson,
            'items' => $items,
            'itemsByType' => $items
                ->groupBy(function ($row) {
                    $type = $this->normalizeV2ItemType((string) ($row->type ?? ''));
                    return $type !== '' ? $type : 'unknown';
                })
                ->map(function ($rows) {
                    return $rows->count();
                })
                ->sortKeys(),
            'summary' => [
                'items' => $items->count(),
                'with_audio' => (int) $items->filter(function ($row) {
                    return trim((string) ($row->audio ?? '')) !== '';
                })->count(),
            ],
        ];
    }

    public function updateV2ItemTextField(int $itemId, string $field, string $value): ?array
    {
        if (!in_array($field, ['text_from', 'text_to', 'ipa'], true)) {
            return null;
        }

        $row = DB::connection('tenant')
            ->table('flashcard_items')
            ->select('id', 'lesson_id')
            ->where('id', $itemId)
            ->first();

        if (!$row) {
            return null;
        }

        DB::connection('tenant')
            ->table('flashcard_items')
            ->where('id', $itemId)
            ->update([
                $field => $value,
                'updated_at' => now(),
            ]);

        return [
            'id' => (int) ($row->id ?? 0),
            'lesson_id' => (int) ($row->lesson_id ?? 0),
            'field' => $field,
            'value' => $value,
        ];
    }

    public function buildV2DuplicateGptPreview(array $lessonIds, string $typeNorm, string $textFrom, string $textTo): array
    {
        $normalizedType = $this->normalizeV2ItemType($typeNorm);
        $normalizedFrom = $this->normalizeV2DuplicateText($textFrom);
        $normalizedTo = $this->normalizeV2DuplicateText($textTo);

        $lessonIds = collect($lessonIds)
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) {
                return $id > 0;
            })
            ->unique()
            ->sort()
            ->values();

        if ($lessonIds->count() < 2) {
            throw new \RuntimeException('At least 2 lessons are required.');
        }

        $lessons = DB::connection('tenant')
            ->table('flashcard_lessons')
            ->select('id', 'module_id', 'title', 'url', 'lesson_type', 'level')
            ->whereIn('id', $lessonIds)
            ->orderBy('id')
            ->get()
            ->keyBy('id');

        $items = DB::connection('tenant')
            ->table('flashcard_items')
            ->select('id', 'lesson_id', 'type', 'text_from', 'text_to', 'ipa')
            ->whereIn('lesson_id', $lessonIds)
            ->orderBy('lesson_id')
            ->orderBy('id')
            ->get();

        $duplicateItems = $items
            ->filter(function ($item) use ($normalizedType, $normalizedFrom, $normalizedTo) {
                return $this->normalizeV2ItemType((string) ($item->type ?? '')) === $normalizedType
                    && $this->normalizeV2DuplicateText((string) ($item->text_from ?? '')) === $normalizedFrom
                    && $this->normalizeV2DuplicateText((string) ($item->text_to ?? '')) === $normalizedTo;
            })
            ->map(function ($item) use ($lessons) {
                $lesson = $lessons->get((int) ($item->lesson_id ?? 0));

                return [
                    'id' => (int) ($item->id ?? 0),
                    'lesson_id' => (int) ($item->lesson_id ?? 0),
                    'lesson_title' => trim((string) ($lesson->title ?? '')),
                    'lesson_level' => strtoupper(trim((string) ($lesson->level ?? ''))),
                    'type' => trim((string) ($item->type ?? '')),
                    'text_from' => trim((string) ($item->text_from ?? '')),
                    'text_to' => trim((string) ($item->text_to ?? '')),
                    'ipa' => trim((string) ($item->ipa ?? '')),
                ];
            })
            ->values();

        $allowedItemIds = $duplicateItems
            ->pluck('id')
            ->filter(function ($id) {
                return (int) $id > 0;
            })
            ->unique()
            ->sort()
            ->values();

        if ($allowedItemIds->count() < 2) {
            throw new \RuntimeException('Duplicate rows were not found for selected lessons.');
        }

        $itemsByLesson = $items->groupBy(function ($item) {
            return (int) ($item->lesson_id ?? 0);
        });

        $lessonsPayload = $lessonIds
            ->map(function ($lessonId) use ($lessons, $itemsByLesson) {
                $lesson = $lessons->get((int) $lessonId);
                $rows = $itemsByLesson->get((int) $lessonId, collect());

                return [
                    'id' => (int) $lessonId,
                    'title' => trim((string) ($lesson->title ?? '')),
                    'url' => trim((string) ($lesson->url ?? '')),
                    'lesson_type' => trim((string) ($lesson->lesson_type ?? '')),
                    'level' => strtoupper(trim((string) ($lesson->level ?? ''))),
                    'module_id' => is_numeric($lesson->module_id ?? null) ? (int) $lesson->module_id : null,
                    'items' => $rows->map(function ($item) {
                        return [
                            'id' => (int) ($item->id ?? 0),
                            'text_from' => trim((string) ($item->text_from ?? '')),
                            'text_to' => trim((string) ($item->text_to ?? '')),
                            'ipa' => trim((string) ($item->ipa ?? '')),
                            'type' => trim((string) ($item->type ?? '')),
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->all();

        return [
            'target' => [
                'type_norm' => $normalizedType,
                'text_from' => $normalizedFrom,
                'text_to' => $normalizedTo,
            ],
            'lesson_ids' => $lessonIds->all(),
            'allowed_item_ids' => $allowedItemIds->all(),
            'duplicate_items' => $duplicateItems->all(),
            'lessons' => $lessonsPayload,
        ];
    }

    public function askV2DuplicateGptUpdateSql(array $preview): array
    {
        $apiKey = (string) (config('services.openai.api_key') ?: env('OPENAI_API_KEY', ''));
        if ($apiKey === '') {
            throw new \RuntimeException('OPENAI_API_KEY is missing.');
        }

        $model = (string) (config('services.openai.model') ?: env('OPENAI_MODEL', 'gpt-4.1'));
        $allowedItemIds = collect($preview['allowed_item_ids'] ?? [])
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) {
                return $id > 0;
            })
            ->unique()
            ->values();

        if ($allowedItemIds->count() < 2) {
            throw new \RuntimeException('No safe item IDs available for GPT action.');
        }

        $systemPrompt = <<<PROMPT
You are assisting with semantic deduplication in MySQL table flashcard_items.

The same word appears in two different lessons.

Your task:
1) Decide in which lesson the word fits better contextually.
2) In the other lesson, replace the duplicate with a new word that:
   - fits the lesson theme precisely
   - matches the lesson CEFR level strictly
   - for A2: avoid very basic A1 vocabulary (e.g. old, big, small, good, bad)
   - maintains similar lexical complexity to the other words in that lesson
   - does NOT duplicate any existing item in that lesson
   - keeps the same type (word/phrase/question)
3) Do NOT invent IDs.
4) Use only IDs from allowed_item_ids.
5) Do NOT delete rows.
6) Return ONLY one SQL statement, nothing else:
   UPDATE `flashcard_items` SET `text_from`='...', `text_to`='...', `ipa`='...', `updated_at`=NOW() WHERE `id`=... AND `lesson_id`=... LIMIT 1;
7) The SQL must update exactly one row from allowed_item_ids.
8) If the proposed replacement lowers the lexical difficulty compared to the lesson's existing words, choose a more appropriate alternative.
PROMPT;

        $userPayload = [
            'target' => $preview['target'] ?? [],
            'allowed_item_ids' => $allowedItemIds->all(),
            'duplicate_items' => $preview['duplicate_items'] ?? [],
            'lessons' => $preview['lessons'] ?? [],
        ];

        $userPayloadJson = json_encode($userPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($userPayloadJson) || $userPayloadJson === '') {
            throw new \RuntimeException('Invalid payload JSON.');
        }

        $duplicateMap = collect($preview['duplicate_items'] ?? [])
            ->keyBy(function ($row) {
                return (int) ($row['id'] ?? 0);
            });
        $maxAttempts = 3;
        $attemptErrors = [];
        $final = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $rawText = '';
            $errors = [];
            $attemptPrompt = $systemPrompt;

            if ($attempt > 1 && $attemptErrors !== []) {
                $attemptPrompt .= "\nIMPORTANT: Previous proposal was rejected because: " . end($attemptErrors) . ".\nReturn a different replacement and keep strict JSON output.";
            }

            $responsesResponse = Http::withToken($apiKey)
                ->timeout(90)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => $model,
                    'input' => [
                        ['role' => 'system', 'content' => $attemptPrompt],
                        ['role' => 'user', 'content' => $userPayloadJson],
                    ],
                ]);

            if ($responsesResponse->successful()) {
                $rawText = $this->extractOpenAiText($responsesResponse->json('output_text', ''));
                if ($rawText === '') {
                    $rawText = $this->extractOpenAiText($responsesResponse->json('output', []));
                }
            } else {
                $errors[] = 'responses: HTTP ' . $responsesResponse->status() . ' ' . $this->summarizeOpenAiErrorBody((string) $responsesResponse->body());
            }

            if ($rawText === '') {
                $chatResponse = Http::withToken($apiKey)
                    ->timeout(90)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => $model,
                        'temperature' => 0,
                        'response_format' => [
                            'type' => 'json_object',
                        ],
                        'messages' => [
                            ['role' => 'system', 'content' => $attemptPrompt],
                            ['role' => 'user', 'content' => $userPayloadJson],
                        ],
                    ]);

                if ($chatResponse->successful()) {
                    $rawText = $this->extractOpenAiText($chatResponse->json('choices.0.message.content', ''));
                } else {
                    $errors[] = 'chat.completions: HTTP ' . $chatResponse->status() . ' ' . $this->summarizeOpenAiErrorBody((string) $chatResponse->body());
                }
            }

            if ($rawText === '') {
                $attemptErrors[] = 'OpenAI response is empty.' . ($errors !== [] ? ' ' . implode(' | ', $errors) : '');
                continue;
            }

            $sqlCandidate = $this->extractFirstUpdateSql($rawText);
            if ($sqlCandidate === null) {
                $attemptErrors[] = 'Model did not return an UPDATE statement.';
                continue;
            }

            $parsedSql = $this->parseFlashcardUpdateSql($sqlCandidate);
            if ($parsedSql === null) {
                $attemptErrors[] = 'Model returned invalid SQL format.';
                continue;
            }

            $updateItemId = (int) ($parsedSql['id'] ?? 0);
            $updateLessonId = (int) ($parsedSql['lesson_id'] ?? 0);
            $nextTextFrom = trim((string) ($parsedSql['text_from'] ?? ''));
            $nextTextTo = trim((string) ($parsedSql['text_to'] ?? ''));
            $nextIpa = trim((string) ($parsedSql['ipa'] ?? ''));

            if (!$allowedItemIds->contains($updateItemId)) {
                $attemptErrors[] = 'OpenAI returned unsafe item ID.';
                continue;
            }

            $updateRow = $duplicateMap->get($updateItemId);
            if (!$updateRow) {
                $attemptErrors[] = 'Unable to map update item to lesson.';
                continue;
            }

            if ($updateLessonId !== (int) ($updateRow['lesson_id'] ?? 0)) {
                $attemptErrors[] = 'SQL lesson_id does not match selected duplicate item.';
                continue;
            }

            if ($nextTextFrom === '' && $nextTextTo === '') {
                $attemptErrors[] = 'OpenAI returned empty text_from and text_to.';
                continue;
            }

            $targetTypeNorm = $this->normalizeV2ItemType((string) ($preview['target']['type_norm'] ?? ($updateRow['type'] ?? '')));
            $originalTextFrom = trim((string) ($updateRow['text_from'] ?? ''));
            $originalTextTo = trim((string) ($updateRow['text_to'] ?? ''));
            $originalNormFrom = $this->normalizeV2DuplicateText($originalTextFrom);
            $originalNormTo = $this->normalizeV2DuplicateText($originalTextTo);
            $nextNormFrom = $this->normalizeV2DuplicateText($nextTextFrom);
            $nextNormTo = $this->normalizeV2DuplicateText($nextTextTo);

            if ($nextNormFrom === $originalNormFrom && $nextNormTo === $originalNormTo) {
                $attemptErrors[] = 'OpenAI returned same text_from/text_to. No replacement was generated.';
                continue;
            }

            if ($nextNormFrom === $originalNormFrom || $nextNormTo === $originalNormTo) {
                $attemptErrors[] = 'Replacement must change both text_from and text_to.';
                continue;
            }

            $updateLesson = collect($preview['lessons'] ?? [])
                ->first(function ($lesson) use ($updateLessonId) {
                    return (int) ($lesson['id'] ?? 0) === $updateLessonId;
                });

            if (is_array($updateLesson)) {
                $duplicateInsideLesson = collect($updateLesson['items'] ?? [])
                    ->contains(function ($item) use ($updateItemId, $targetTypeNorm, $nextNormFrom, $nextNormTo) {
                        return (int) ($item['id'] ?? 0) !== $updateItemId
                            && $this->normalizeV2ItemType((string) ($item['type'] ?? '')) === $targetTypeNorm
                            && $this->normalizeV2DuplicateText((string) ($item['text_from'] ?? '')) === $nextNormFrom
                            && $this->normalizeV2DuplicateText((string) ($item['text_to'] ?? '')) === $nextNormTo;
                    });

                if ($duplicateInsideLesson) {
                    $attemptErrors[] = 'OpenAI proposed another duplicate already existing in target lesson.';
                    continue;
                }
            }

            $final = [
                'update_item_id' => $updateItemId,
                'update_lesson_id' => $updateLessonId,
                'next_text_from' => $nextTextFrom,
                'next_text_to' => $nextTextTo,
                'next_ipa' => $nextIpa,
                'sql' => $sqlCandidate,
            ];
            break;
        }

        if ($final === null) {
            $lastError = $attemptErrors !== [] ? end($attemptErrors) : 'Unknown model validation error.';
            throw new \RuntimeException('Unable to generate valid replacement after ' . $maxAttempts . ' attempts. Last error: ' . $lastError);
        }

        $updateItemId = (int) $final['update_item_id'];
        $updateLessonId = (int) $final['update_lesson_id'];
        $nextTextFrom = (string) $final['next_text_from'];
        $nextTextTo = (string) $final['next_text_to'];
        $nextIpa = (string) $final['next_ipa'];
        $sql = (string) $final['sql'];

        return [
            'model' => $model,
            'update_item_id' => $updateItemId,
            'update_lesson_id' => $updateLessonId,
            'updated_fields' => [
                'text_from' => $nextTextFrom,
                'text_to' => $nextTextTo,
                'ipa' => $nextIpa,
            ],
            'sql' => $sql,
            'raw_model_response' => [
                'sql' => $sql,
            ],
        ];
    }

    private function extractFirstUpdateSql(string $text): ?string
    {
        if (preg_match('/UPDATE\s+`?flashcard_items`?.+?;/is', $text, $match) === 1) {
            return trim((string) $match[0]);
        }

        try {
            $decoded = $this->decodeJsonObjectFromText($text);
            $sql = trim((string) ($decoded['sql'] ?? ''));
            if ($sql !== '' && preg_match('/^UPDATE\s+`?flashcard_items`?/i', $sql) === 1) {
                return $sql;
            }
        } catch (\Throwable $e) {
            // Ignore non-JSON responses; SQL may still be absent.
        }

        return null;
    }

    private function parseFlashcardUpdateSql(string $sql): ?array
    {
        $cleanSql = trim($sql);
        if (preg_match('/^UPDATE\s+`?flashcard_items`?/i', $cleanSql) !== 1) {
            return null;
        }

        if (preg_match('/\bWHERE\b.+`?id`?\s*=\s*(\d+).+`?lesson_id`?\s*=\s*(\d+)/is', $cleanSql, $idMatches) !== 1) {
            return null;
        }

        if (preg_match('/`?text_from`?\s*=\s*\'((?:\'\'|[^\'])*)\'/i', $cleanSql, $fromMatches) !== 1) {
            return null;
        }

        if (preg_match('/`?text_to`?\s*=\s*\'((?:\'\'|[^\'])*)\'/i', $cleanSql, $toMatches) !== 1) {
            return null;
        }

        if (preg_match('/`?ipa`?\s*=\s*\'((?:\'\'|[^\'])*)\'/i', $cleanSql, $ipaMatches) !== 1) {
            return null;
        }

        if (preg_match('/\bLIMIT\s+1\b/i', $cleanSql) !== 1) {
            return null;
        }

        return [
            'text_from' => str_replace("''", "'", (string) ($fromMatches[1] ?? '')),
            'text_to' => str_replace("''", "'", (string) ($toMatches[1] ?? '')),
            'ipa' => str_replace("''", "'", (string) ($ipaMatches[1] ?? '')),
            'id' => (int) ($idMatches[1] ?? 0),
            'lesson_id' => (int) ($idMatches[2] ?? 0),
        ];
    }

    private function summarizeOpenAiErrorBody(string $body): string
    {
        $trimmed = trim($body);
        if ($trimmed === '') {
            return '';
        }

        $decoded = json_decode($trimmed, true);
        if (is_array($decoded)) {
            $message = trim((string) ($decoded['error']['message'] ?? $decoded['message'] ?? ''));
            if ($message !== '') {
                return preg_replace('/\s+/u', ' ', $message) ?? $message;
            }
        }

        $compact = preg_replace('/\s+/u', ' ', $trimmed);
        return substr((string) ($compact ?? $trimmed), 0, 220);
    }

    public function getV2ModuleDetails(int $moduleId): array
    {
        $module = DB::connection('tenant')
            ->table('flashcard_modules')
            ->select('id', 'title', 'slug', 'description', 'sort_order', 'is_active', 'created_at', 'updated_at')
            ->where('id', $moduleId)
            ->first();

        if (!$module) {
            return [
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
        }

        $lessons = DB::connection('tenant')
            ->table('flashcard_lessons')
            ->select('id', 'module_id', 'title', 'url', 'lesson_type', 'level', 'sort_order', 'is_active', 'created_at', 'updated_at')
            ->where('module_id', $moduleId)
            ->orderByRaw('CASE WHEN sort_order IS NULL THEN 1 ELSE 0 END')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $lessonIds = $lessons->pluck('id')->filter()->values();
        $itemsByLesson = collect();
        if ($lessonIds->isNotEmpty()) {
            $itemsByLesson = DB::connection('tenant')
                ->table('flashcard_items')
                ->select('id', 'lesson_id', 'type', 'text_from', 'text_to', 'ipa', 'audio', 'created_at', 'updated_at')
                ->whereIn('lesson_id', $lessonIds)
                ->orderBy('lesson_id')
                ->orderBy('id')
                ->get()
                ->groupBy('lesson_id');
        }

        $lessons = $lessons->map(function ($lesson) use ($itemsByLesson) {
            $items = $itemsByLesson->get((int) ($lesson->id ?? 0), collect())->values();
            $lesson->is_active = (int) ($lesson->is_active ?? 0);
            $lesson->items = $items;
            $lesson->items_count = $items->count();
            $lesson->items_with_audio = (int) $items->filter(function ($row) {
                return trim((string) ($row->audio ?? '')) !== '';
            })->count();
            return $lesson;
        });

        $allItems = $lessons
            ->flatMap(function ($lesson) {
                return ($lesson->items ?? collect())->map(function ($item) {
                    $typeRaw = strtolower(trim((string) ($item->type ?? '')));
                    $type = $this->normalizeV2ItemType((string) ($item->type ?? ''));
                    $from = $this->normalizeV2DuplicateText((string) ($item->text_from ?? ''));
                    $to = $this->normalizeV2DuplicateText((string) ($item->text_to ?? ''));

                    return (object) [
                        'key' => $type . '||' . $from . '||' . $to,
                        'type' => $type,
                        'type_raw' => $typeRaw,
                        'text_from' => $from,
                        'text_to' => $to,
                        'lesson_id' => (int) ($item->lesson_id ?? 0),
                        'lesson_title' => trim((string) ($lesson->title ?? '')),
                        'item_id' => (int) ($item->id ?? 0),
                    ];
                });
            })
            ->filter(function ($row) {
                return $row->key !== '||||';
            });

        // Real duplicates: same lesson + same type + same text pair.
        $duplicateGroups = $allItems
            ->groupBy(function ($row) {
                return $row->lesson_id . '||' . $row->key;
            })
            ->filter(function ($rows) {
                return $rows->count() > 1;
            })
            ->map(function ($rows) {
                $first = $rows->first();
                return (object) [
                    'lesson_id' => $first->lesson_id,
                    'lesson_title' => $first->lesson_title,
                    'type' => $first->type,
                    'raw_types' => $rows->pluck('type_raw')->filter()->unique()->sort()->values(),
                    'text_from' => $first->text_from,
                    'text_to' => $first->text_to,
                    'count' => $rows->count(),
                    'item_ids' => $rows->pluck('item_id')->sort()->values(),
                ];
            })
            ->sortBy(function ($row) {
                return sprintf('%08d-%08d', (int) ($row->lesson_id ?? 0), 99999999 - (int) ($row->count ?? 0));
            })
            ->values();

        $duplicateRows = (int) $duplicateGroups->sum(function ($row) {
            return max(((int) ($row->count ?? 0)) - 1, 0);
        });

        // Informational: repeated across different lessons (can be intentional).
        $reusedGroups = $allItems
            ->groupBy('key')
            ->filter(function ($rows) {
                return $rows->pluck('lesson_id')->unique()->count() > 1;
            })
            ->map(function ($rows) {
                $first = $rows->first();
                return (object) [
                    'type' => $first->type,
                    'raw_types' => $rows->pluck('type_raw')->filter()->unique()->sort()->values(),
                    'text_from' => $first->text_from,
                    'text_to' => $first->text_to,
                    'count' => $rows->count(),
                    'lesson_ids' => $rows->pluck('lesson_id')->unique()->sort()->values(),
                ];
            })
            ->sortByDesc('count')
            ->values();

        $lessonIdsForTitleLookup = $reusedGroups
            ->pluck('lesson_ids')
            ->flatten()
            ->filter()
            ->unique()
            ->values();

        $lessonTitlesLookup = collect();
        if ($lessonIdsForTitleLookup->isNotEmpty()) {
            $lessonTitlesLookup = DB::connection('tenant')
                ->table('flashcard_lessons')
                ->whereIn('id', $lessonIdsForTitleLookup)
                ->pluck('title', 'id');
        }

        $reusedGroups = $reusedGroups->map(function ($row) use ($lessonTitlesLookup) {
            $titles = collect($row->lesson_ids ?? collect())
                ->map(function ($lessonId) use ($lessonTitlesLookup) {
                    return trim((string) ($lessonTitlesLookup->get($lessonId) ?? ''));
                })
                ->filter()
                ->unique()
                ->values()
                ->implode(' · ');

            $row->lesson_titles = $titles;

            return $row;
        });

        $module->is_active = (int) ($module->is_active ?? 0);

        return [
            'module' => $module,
            'lessons' => $lessons,
            'duplicateGroups' => $duplicateGroups,
            'reusedGroups' => $reusedGroups,
            'summary' => [
                'lessons' => $lessons->count(),
                'active_lessons' => (int) $lessons->where('is_active', 1)->count(),
                'items' => (int) $lessons->sum('items_count'),
                'items_with_audio' => (int) $lessons->sum('items_with_audio'),
                'duplicate_rows' => $duplicateRows,
                'duplicate_groups' => (int) $duplicateGroups->count(),
                'reused_groups' => (int) $reusedGroups->count(),
            ],
        ];
    }

    public function getDebutIntegrityReport(bool $realCheckEnabled = true, int $realCheckLimit = 500, int $startAfterId = 0): array
    {
        $base = DB::connection('tenant')
            ->table('cardsLearnWordsContent');

        $totalRows = (int) (clone $base)->count();
        $missingPathCount = (int) (clone $base)
            ->where(function ($query) {
                $query->whereNull('word_audio_mp3')
                    ->orWhereRaw("TRIM(word_audio_mp3) = ''");
            })
            ->count();

        $realCheckLimit = max(10, min($realCheckLimit, 50000));
        $startAfterId = max(0, $startAfterId);
        $checkedRowsCount = 0;
        $checkedMinId = null;
        $checkedMaxId = null;
        $nextStartAfterId = $startAfterId;
        $missingOnServerRows = collect();

        if ($realCheckEnabled) {
            $rowsToCheck = (clone $base)
                ->select('id', 'url_display', 'word_en', 'word_ru', 'word_audio_mp3')
                ->whereRaw("TRIM(COALESCE(word_audio_mp3, '')) <> ''")
                ->where('id', '>', $startAfterId)
                ->orderBy('id')
                ->limit($realCheckLimit)
                ->get();

            $checkedRowsCount = $rowsToCheck->count();
            $checkedMinId = $rowsToCheck->min('id');
            $checkedMaxId = $rowsToCheck->max('id');
            if ($checkedMaxId !== null) {
                $nextStartAfterId = (int) $checkedMaxId;
            }
            $urlStatusCache = [];

            foreach ($rowsToCheck as $row) {
                $resolvedUrl = $this->resolveFlashCardsAudioUrl((string) ($row->word_audio_mp3 ?? ''));
                if ($resolvedUrl === null) {
                    continue;
                }

                if (!array_key_exists($resolvedUrl, $urlStatusCache)) {
                    $urlStatusCache[$resolvedUrl] = $this->urlExists($resolvedUrl);
                }

                if (!$urlStatusCache[$resolvedUrl]) {
                    $row->check_url = $resolvedUrl;
                    $row->issue = 'missing_on_server';
                    $missingOnServerRows->push($row);
                }
            }
        }

        return [
            'totalRows' => $totalRows,
            'missingPathCount' => $missingPathCount,
            'realCheckEnabled' => $realCheckEnabled,
            'realCheckLimit' => $realCheckLimit,
            'startAfterId' => $startAfterId,
            'checkedRowsCount' => (int) $checkedRowsCount,
            'checkedMinId' => $checkedMinId,
            'checkedMaxId' => $checkedMaxId,
            'nextStartAfterId' => $nextStartAfterId,
            'missingOnServerCount' => (int) $missingOnServerRows->count(),
            'missingOnServerRows' => $missingOnServerRows,
        ];
    }

    public function getWordsManualList(): Collection
    {
        $lessons = DB::connection('tenant')
            ->table('cardsLearnWordsPag as l')
            ->leftJoin('cardsLearnWordsCategoryPag as c', 'c.url_category', '=', 'l.group_category')
            ->select(
                'l.id',
                'l.url',
                'l.title',
                'l.group_category',
                'l.category',
                'c.id as group_category_id',
                'c.title_category as group_title'
            )
            ->orderByRaw('CASE WHEN c.id IS NULL THEN 1 ELSE 0 END')
            ->orderBy('c.id')
            ->orderBy('l.id')
            ->get();

        $wordsByUrl = DB::connection('tenant')
            ->table('cardsLearnWordsContent')
            ->select('id', 'url_display', 'word_en', 'word_ru')
            ->orderBy('url_display')
            ->orderBy('id')
            ->get()
            ->groupBy('url_display');

        return $lessons->map(function ($lesson) use ($wordsByUrl) {
            $words = $wordsByUrl
                ->get((string) ($lesson->url ?? ''), collect())
                ->map(function ($wordRow) {
                    return (object) [
                        'id' => $wordRow->id ?? null,
                        'word_en' => $wordRow->word_en ?? '',
                        'word_ru' => $wordRow->word_ru ?? '',
                    ];
                })
                ->values();

            $lesson->words = $words;
            $lesson->words_count = $words->count();

            return $lesson;
        });
    }

    public function getPhrasesDebutIntegrityReport(bool $realCheckEnabled = true, int $realCheckLimit = 500, int $startAfterId = 0): array
    {
        $base = DB::connection('tenant')
            ->table('flashcards_question_sentences_content');

        $totalRows = (int) (clone $base)->count();
        $missingPathCount = (int) (clone $base)
            ->where(function ($query) {
                $query->whereNull('word_audio')
                    ->orWhereRaw("TRIM(word_audio) = ''");
            })
            ->count();

        $realCheckLimit = max(10, min($realCheckLimit, 50000));
        $startAfterId = max(0, $startAfterId);
        $checkedRowsCount = 0;
        $checkedMinId = null;
        $checkedMaxId = null;
        $nextStartAfterId = $startAfterId;
        $missingOnServerRows = collect();

        if ($realCheckEnabled) {
            $rowsToCheck = (clone $base)
                ->select('id', 'url_display', 'word_en', 'word_ru', 'word_audio')
                ->whereRaw("TRIM(COALESCE(word_audio, '')) <> ''")
                ->where('id', '>', $startAfterId)
                ->orderBy('id')
                ->limit($realCheckLimit)
                ->get();

            $checkedRowsCount = $rowsToCheck->count();
            $checkedMinId = $rowsToCheck->min('id');
            $checkedMaxId = $rowsToCheck->max('id');
            if ($checkedMaxId !== null) {
                $nextStartAfterId = (int) $checkedMaxId;
            }
            $urlStatusCache = [];

            foreach ($rowsToCheck as $row) {
                $resolvedUrl = $this->resolveFlashCardsPhrasesAudioUrl((string) ($row->word_audio ?? ''));
                if ($resolvedUrl === null) {
                    continue;
                }

                if (!array_key_exists($resolvedUrl, $urlStatusCache)) {
                    $urlStatusCache[$resolvedUrl] = $this->urlExists($resolvedUrl);
                }

                if (!$urlStatusCache[$resolvedUrl]) {
                    $row->check_url = $resolvedUrl;
                    $row->issue = 'missing_on_server';
                    $missingOnServerRows->push($row);
                }
            }
        }

        return [
            'totalRows' => $totalRows,
            'missingPathCount' => $missingPathCount,
            'realCheckEnabled' => $realCheckEnabled,
            'realCheckLimit' => $realCheckLimit,
            'startAfterId' => $startAfterId,
            'checkedRowsCount' => (int) $checkedRowsCount,
            'checkedMinId' => $checkedMinId,
            'checkedMaxId' => $checkedMaxId,
            'nextStartAfterId' => $nextStartAfterId,
            'missingOnServerCount' => (int) $missingOnServerRows->count(),
            'missingOnServerRows' => $missingOnServerRows,
        ];
    }

    public function getDashboardData(array $filters): array
    {
        $totalWordsLessons = (int) DB::connection('tenant')
            ->table('cardsLearnWordsPag')
            ->count();

        $totalWordsItems = (int) DB::connection('tenant')
            ->table('cardsLearnWordsContent')
            ->count();

        $totalPhrasesLessons = (int) DB::connection('tenant')
            ->table('flashcards_question_sentences')
            ->count();

        $totalPhrasesItems = (int) DB::connection('tenant')
            ->table('flashcards_question_sentences_content')
            ->count();

        $totalFinishedLessons = $this->countFinishedLessons($filters['user_id'], $filters['range_start_ts'], $filters['range_end_ts']);

        $wordsCategories = $this->getWordsCategories($filters);
        $wordsLessons = $this->getWordsLessons($filters);
        $phrasesLessons = $this->getPhrasesLessons($filters);
        $usersProgress = $this->getUsersProgress($filters, $totalWordsLessons, $totalPhrasesLessons);

        $wordsCategoryOptions = DB::connection('tenant')
            ->table('cardsLearnWordsCategoryPag')
            ->select('url_category', 'title_category')
            ->orderBy('title_category')
            ->get();

        $wordsLevelOptions = DB::connection('tenant')
            ->table('cardsLearnWordsPag')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return [
            'kpis' => [
                'total_words_lessons' => $totalWordsLessons,
                'total_words_items' => $totalWordsItems,
                'total_phrases_lessons' => $totalPhrasesLessons,
                'total_phrases_items' => $totalPhrasesItems,
                'total_finished_lessons' => $totalFinishedLessons,
            ],
            'wordsCategories' => $wordsCategories,
            'wordsLessons' => $wordsLessons,
            'phrasesLessons' => $phrasesLessons,
            'usersProgress' => $usersProgress,
            'wordsCategoryOptions' => $wordsCategoryOptions,
            'wordsLevelOptions' => $wordsLevelOptions,
        ];
    }

    public function getWordsLessonDetails(int $lessonId, array $filters): array
    {
        $lesson = DB::connection('tenant')
            ->table('cardsLearnWordsPag')
            ->select('id', 'url', 'title', 'category', 'group_category', 'time_lessons', 'time')
            ->where('id', $lessonId)
            ->first();

        if (!$lesson) {
            return [
                'lesson' => null,
                'activeTab' => 'content',
                'tabCounts' => [
                    'content' => 0,
                    'quiz' => 0,
                    'history' => 0,
                ],
                'rows' => null,
            ];
        }

        $activeTab = in_array($filters['tab'], ['content', 'quiz', 'history'], true)
            ? $filters['tab']
            : 'content';

        $contentQuery = DB::connection('tenant')
            ->table('cardsLearnWordsContent')
            ->select('id', 'url_display', 'word_en', 'word_ru', 'word_audio', 'word_audio_mp3', 'tophoneticsBritish', 'tophoneticsAmerican')
            ->where('url_display', $lesson->url);

        $quizQuery = DB::connection('tenant')
            ->table('cardsLearnWordsQuizy')
            ->select(
                'id',
                'quiz_url',
                'type',
                'question',
                'answer_1',
                'answer_2',
                'answer_3',
                'answer_4',
                'answer_1_audio',
                'answer_2_audio',
                'answer_3_audio',
                'answer_4_audio',
                'answer_correct'
            )
            ->where('quiz_url', $lesson->url);

        $historyQuery = DB::connection('tenant')
            ->table('cardsLearnWordsHistory')
            ->select(
                'id',
                'user_id',
                'card_id',
                'score_carousel',
                'score_quiz',
                'correct_answers',
                'wrong_answers',
                'time_carousel',
                'wrong_words_json',
                'time_quiz',
                'start_time',
                'end_time'
            )
            ->where('card_id', $lesson->id);

        if ($filters['user_id'] !== null) {
            $historyQuery->where('user_id', $filters['user_id']);
        }

        $this->applyPeriodToHistoryScope($historyQuery, $filters['range_start_ts'], $filters['range_end_ts']);

        $tabCounts = [
            'content' => (clone $contentQuery)->count(),
            'quiz' => (clone $quizQuery)->count(),
            'history' => (clone $historyQuery)->count(),
        ];

        $rows = null;
        if ($activeTab === 'content') {
            $rows = $contentQuery
                ->orderBy('id')
                ->paginate($filters['details_per_page'], ['*'], 'tab_page')
                ->withQueryString();
        } elseif ($activeTab === 'quiz') {
            $rows = $quizQuery
                ->orderBy('id')
                ->paginate($filters['details_per_page'], ['*'], 'tab_page')
                ->withQueryString();
        } else {
            $rows = $historyQuery
                ->orderByDesc('end_time')
                ->orderByDesc('id')
                ->paginate($filters['details_per_page'], ['*'], 'tab_page')
                ->withQueryString();

            $rows->getCollection()->transform(function ($row) {
                $rawJson = trim((string) ($row->wrong_words_json ?? ''));
                $row->wrong_words_pretty = null;
                if ($rawJson === '') {
                    return $row;
                }

                $decoded = json_decode($rawJson, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $row->wrong_words_pretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } else {
                    $row->wrong_words_pretty = $rawJson;
                }

                return $row;
            });
        }

        return [
            'lesson' => $lesson,
            'activeTab' => $activeTab,
            'tabCounts' => $tabCounts,
            'rows' => $rows,
        ];
    }

    public function getPhrasesLessonDetails(int $lessonId, array $filters): array
    {
        $lesson = DB::connection('tenant')
            ->table('flashcards_question_sentences')
            ->select('id', 'url', 'title')
            ->where('id', $lessonId)
            ->first();

        if (!$lesson) {
            return [
                'lesson' => null,
                'activeTab' => 'content',
                'tabCounts' => [
                    'content' => 0,
                    'history' => 0,
                ],
                'rows' => null,
            ];
        }

        $activeTab = in_array($filters['tab'], ['content', 'history'], true)
            ? $filters['tab']
            : 'content';

        $contentQuery = DB::connection('tenant')
            ->table('flashcards_question_sentences_content')
            ->select('id', 'url_display', 'word_en', 'word_ru', 'word_audio')
            ->where('url_display', $lesson->url);

        $historyQuery = DB::connection('tenant')
            ->table('flashcards_question_history')
            ->select('id', 'user_id', 'card_id', 'score_carousel', 'time_carousel', 'start_time', 'end_time')
            ->where('card_id', $lesson->id);

        if ($filters['user_id'] !== null) {
            $historyQuery->where('user_id', $filters['user_id']);
        }

        $this->applyPeriodToHistoryScope($historyQuery, $filters['range_start_ts'], $filters['range_end_ts']);

        $tabCounts = [
            'content' => (clone $contentQuery)->count(),
            'history' => (clone $historyQuery)->count(),
        ];

        $rows = null;
        if ($activeTab === 'content') {
            $rows = $contentQuery
                ->orderBy('id')
                ->paginate($filters['details_per_page'], ['*'], 'tab_page')
                ->withQueryString();
        } else {
            $rows = $historyQuery
                ->orderByDesc('end_time')
                ->orderByDesc('id')
                ->paginate($filters['details_per_page'], ['*'], 'tab_page')
                ->withQueryString();
        }

        return [
            'lesson' => $lesson,
            'activeTab' => $activeTab,
            'tabCounts' => $tabCounts,
            'rows' => $rows,
        ];
    }

    private function getWordsCategories(array $filters): LengthAwarePaginator
    {
        $lessonsCountByCategory = DB::connection('tenant')
            ->table('cardsLearnWordsPag')
            ->select('group_category', DB::raw('COUNT(*) as lessons_count'))
            ->groupBy('group_category');

        $query = DB::connection('tenant')
            ->table('cardsLearnWordsCategoryPag as c')
            ->leftJoinSub($lessonsCountByCategory, 'lc', function ($join) {
                $join->on('lc.group_category', '=', 'c.url_category');
            })
            ->select(
                'c.id',
                'c.code_name',
                'c.title_category',
                'c.url_category',
                'c.img_category'
            )
            ->selectRaw('COALESCE(lc.lessons_count, 0) as lessons_count');

        if ($filters['user_id'] !== null) {
            $finishedByCategory = DB::connection('tenant')
                ->table('cardsLearnWordsHistory as h')
                ->join('cardsLearnWordsPag as l', 'l.id', '=', 'h.card_id')
                ->select('l.group_category', DB::raw('COUNT(DISTINCT h.card_id) as finished_lessons_count'))
                ->where('h.user_id', $filters['user_id'])
                ->where('h.end_time', '>', 0)
                ->groupBy('l.group_category');

            $this->applyPeriodToFinishedScope($finishedByCategory, $filters['range_start_ts'], $filters['range_end_ts'], 'h.end_time');

            $query->leftJoinSub($finishedByCategory, 'fc', function ($join) {
                $join->on('fc.group_category', '=', 'c.url_category');
            });

            $query->selectRaw('COALESCE(fc.finished_lessons_count, 0) as finished_lessons_count');
        } else {
            $query->selectRaw('NULL as finished_lessons_count');
        }

        if ($filters['categories_q'] !== '') {
            $like = $this->toLikePattern($filters['categories_q']);
            $query->where(function ($subQuery) use ($like) {
                $subQuery->where('c.title_category', 'like', $like)
                    ->orWhere('c.code_name', 'like', $like);
            });
        }

        return $query
            ->orderBy('c.id')
            ->paginate($filters['categories_per_page'], ['*'], 'categories_page')
            ->withQueryString();
    }

    private function getWordsLessons(array $filters): LengthAwarePaginator
    {
        $wordsCountSub = DB::connection('tenant')
            ->table('cardsLearnWordsContent')
            ->select('url_display', DB::raw('COUNT(*) as words_count'))
            ->groupBy('url_display');

        $query = DB::connection('tenant')
            ->table('cardsLearnWordsPag as l')
            ->leftJoinSub($wordsCountSub, 'wc', function ($join) {
                $join->on('wc.url_display', '=', 'l.url');
            })
            ->select('l.id', 'l.group_category', 'l.title', 'l.url', 'l.category')
            ->selectRaw('COALESCE(wc.words_count, 0) as words_count');

        if ($filters['user_id'] !== null) {
            $finishedSub = DB::connection('tenant')
                ->table('cardsLearnWordsHistory')
                ->select('card_id', DB::raw('1 as finished'))
                ->where('user_id', $filters['user_id'])
                ->where('end_time', '>', 0)
                ->groupBy('card_id');

            $this->applyPeriodToFinishedScope($finishedSub, $filters['range_start_ts'], $filters['range_end_ts']);

            $latestHistoryIdsSub = DB::connection('tenant')
                ->table('cardsLearnWordsHistory')
                ->select('card_id', DB::raw('MAX(id) as latest_id'))
                ->where('user_id', $filters['user_id'])
                ->groupBy('card_id');

            $this->applyPeriodToHistoryScope($latestHistoryIdsSub, $filters['range_start_ts'], $filters['range_end_ts']);

            $latestHistorySub = DB::connection('tenant')
                ->table('cardsLearnWordsHistory as h')
                ->joinSub($latestHistoryIdsSub, 'lh', function ($join) {
                    $join->on('lh.latest_id', '=', 'h.id');
                })
                ->select(
                    'h.card_id',
                    'h.score_carousel',
                    'h.score_quiz',
                    'h.correct_answers',
                    'h.wrong_answers',
                    'h.time_carousel',
                    'h.time_quiz',
                    'h.end_time'
                );

            $query->leftJoinSub($finishedSub, 'wf', function ($join) {
                $join->on('wf.card_id', '=', 'l.id');
            });

            $query->leftJoinSub($latestHistorySub, 'wh', function ($join) {
                $join->on('wh.card_id', '=', 'l.id');
            });

            $query->selectRaw('COALESCE(wf.finished, 0) as finished')
                ->addSelect(
                    'wh.score_carousel',
                    'wh.score_quiz',
                    'wh.correct_answers',
                    'wh.wrong_answers',
                    'wh.time_carousel',
                    'wh.time_quiz',
                    'wh.end_time'
                );
        } else {
            $query->selectRaw('NULL as finished')
                ->selectRaw('NULL as score_carousel')
                ->selectRaw('NULL as score_quiz')
                ->selectRaw('NULL as correct_answers')
                ->selectRaw('NULL as wrong_answers')
                ->selectRaw('NULL as time_carousel')
                ->selectRaw('NULL as time_quiz')
                ->selectRaw('NULL as end_time');
        }

        if ($filters['words_category'] !== '') {
            $query->where('l.group_category', $filters['words_category']);
        }

        if ($filters['words_level'] !== null) {
            $query->where('l.category', $filters['words_level']);
        }

        if ($filters['words_q'] !== '') {
            $like = $this->toLikePattern($filters['words_q']);
            $query->where(function ($subQuery) use ($like) {
                $subQuery->where('l.title', 'like', $like)
                    ->orWhere('l.url', 'like', $like);
            });
        }

        if ($filters['user_id'] !== null && $filters['words_finished'] !== 'all') {
            if ($filters['words_finished'] === 'yes') {
                $query->whereRaw('COALESCE(wf.finished, 0) = 1');
            }

            if ($filters['words_finished'] === 'no') {
                $query->whereRaw('COALESCE(wf.finished, 0) = 0');
            }
        }

        return $query
            ->orderBy('l.id')
            ->paginate($filters['words_per_page'], ['*'], 'words_page')
            ->withQueryString();
    }

    private function getPhrasesLessons(array $filters): LengthAwarePaginator
    {
        $contentCountSub = DB::connection('tenant')
            ->table('flashcards_question_sentences_content')
            ->select('url_display', DB::raw('COUNT(*) as content_count'))
            ->groupBy('url_display');

        $query = DB::connection('tenant')
            ->table('flashcards_question_sentences as p')
            ->leftJoinSub($contentCountSub, 'pc', function ($join) {
                $join->on('pc.url_display', '=', 'p.url');
            })
            ->select('p.id', 'p.title', 'p.url')
            ->selectRaw('COALESCE(pc.content_count, 0) as content_count');

        if ($filters['user_id'] !== null) {
            $finishedSub = DB::connection('tenant')
                ->table('flashcards_question_history')
                ->select('card_id', DB::raw('1 as finished'))
                ->where('user_id', $filters['user_id'])
                ->where('end_time', '>', 0)
                ->groupBy('card_id');

            $this->applyPeriodToFinishedScope($finishedSub, $filters['range_start_ts'], $filters['range_end_ts']);

            $latestHistoryIdsSub = DB::connection('tenant')
                ->table('flashcards_question_history')
                ->select('card_id', DB::raw('MAX(id) as latest_id'))
                ->where('user_id', $filters['user_id'])
                ->groupBy('card_id');

            $this->applyPeriodToHistoryScope($latestHistoryIdsSub, $filters['range_start_ts'], $filters['range_end_ts']);

            $latestHistorySub = DB::connection('tenant')
                ->table('flashcards_question_history as h')
                ->joinSub($latestHistoryIdsSub, 'lh', function ($join) {
                    $join->on('lh.latest_id', '=', 'h.id');
                })
                ->select('h.card_id', 'h.score_carousel', 'h.time_carousel', 'h.start_time', 'h.end_time');

            $query->leftJoinSub($finishedSub, 'pf', function ($join) {
                $join->on('pf.card_id', '=', 'p.id');
            });

            $query->leftJoinSub($latestHistorySub, 'ph', function ($join) {
                $join->on('ph.card_id', '=', 'p.id');
            });

            $query->selectRaw('COALESCE(pf.finished, 0) as finished')
                ->addSelect('ph.score_carousel', 'ph.time_carousel', 'ph.start_time', 'ph.end_time');
        } else {
            $query->selectRaw('NULL as finished')
                ->selectRaw('NULL as score_carousel')
                ->selectRaw('NULL as time_carousel')
                ->selectRaw('NULL as start_time')
                ->selectRaw('NULL as end_time');
        }

        if ($filters['phrases_q'] !== '') {
            $like = $this->toLikePattern($filters['phrases_q']);
            $query->where(function ($subQuery) use ($like) {
                $subQuery->where('p.title', 'like', $like)
                    ->orWhere('p.url', 'like', $like);
            });
        }

        if ($filters['user_id'] !== null && $filters['phrases_finished'] !== 'all') {
            if ($filters['phrases_finished'] === 'yes') {
                $query->whereRaw('COALESCE(pf.finished, 0) = 1');
            }

            if ($filters['phrases_finished'] === 'no') {
                $query->whereRaw('COALESCE(pf.finished, 0) = 0');
            }
        }

        return $query
            ->orderBy('p.id')
            ->paginate($filters['phrases_per_page'], ['*'], 'phrases_page')
            ->withQueryString();
    }

    private function getUsersProgress(array $filters, int $totalWordsLessons, int $totalPhrasesLessons): LengthAwarePaginator
    {
        $wordsAgg = DB::connection('tenant')
            ->table('cardsLearnWordsHistory')
            ->select(
                'user_id',
                DB::raw('COUNT(DISTINCT CASE WHEN end_time > 0 THEN card_id END) as finished_words_lessons'),
                DB::raw('SUM(COALESCE(time_carousel, 0) + COALESCE(time_quiz, 0)) as total_time_words')
            )
            ->where('user_id', '>', 0)
            ->groupBy('user_id');

        if ($filters['user_id'] !== null) {
            $wordsAgg->where('user_id', $filters['user_id']);
        }

        $this->applyPeriodToHistoryScope($wordsAgg, $filters['range_start_ts'], $filters['range_end_ts']);

        $phrasesAgg = DB::connection('tenant')
            ->table('flashcards_question_history')
            ->select(
                'user_id',
                DB::raw('COUNT(DISTINCT CASE WHEN end_time > 0 THEN card_id END) as finished_phrases_lessons'),
                DB::raw('SUM(COALESCE(time_carousel, 0)) as total_time_phrases')
            )
            ->where('user_id', '>', 0)
            ->groupBy('user_id');

        if ($filters['user_id'] !== null) {
            $phrasesAgg->where('user_id', $filters['user_id']);
        }

        $this->applyPeriodToHistoryScope($phrasesAgg, $filters['range_start_ts'], $filters['range_end_ts']);

        $wordsUsers = DB::connection('tenant')
            ->table('cardsLearnWordsHistory')
            ->select('user_id')
            ->where('user_id', '>', 0)
            ->groupBy('user_id');

        if ($filters['user_id'] !== null) {
            $wordsUsers->where('user_id', $filters['user_id']);
        }

        $this->applyPeriodToHistoryScope($wordsUsers, $filters['range_start_ts'], $filters['range_end_ts']);

        $phrasesUsers = DB::connection('tenant')
            ->table('flashcards_question_history')
            ->select('user_id')
            ->where('user_id', '>', 0)
            ->groupBy('user_id');

        if ($filters['user_id'] !== null) {
            $phrasesUsers->where('user_id', $filters['user_id']);
        }

        $this->applyPeriodToHistoryScope($phrasesUsers, $filters['range_start_ts'], $filters['range_end_ts']);

        $userUnion = $wordsUsers->union($phrasesUsers);

        $wordsPercentExpr = $totalWordsLessons > 0
            ? 'ROUND((COALESCE(w.finished_words_lessons, 0) / ' . $totalWordsLessons . ') * 100, 2)'
            : '0';

        $phrasesPercentExpr = $totalPhrasesLessons > 0
            ? 'ROUND((COALESCE(p.finished_phrases_lessons, 0) / ' . $totalPhrasesLessons . ') * 100, 2)'
            : '0';

        $progressScoreExpr = '(' . $wordsPercentExpr . ' + ' . $phrasesPercentExpr . ')';

        $query = DB::connection('tenant')
            ->query()
            ->fromSub($userUnion, 'u')
            ->leftJoinSub($wordsAgg, 'w', function ($join) {
                $join->on('w.user_id', '=', 'u.user_id');
            })
            ->leftJoinSub($phrasesAgg, 'p', function ($join) {
                $join->on('p.user_id', '=', 'u.user_id');
            })
            ->select('u.user_id')
            ->selectRaw('COALESCE(w.finished_words_lessons, 0) as finished_words_lessons')
            ->selectRaw($totalWordsLessons . ' as total_words_lessons')
            ->selectRaw($wordsPercentExpr . ' as finished_words_percent')
            ->selectRaw('COALESCE(p.finished_phrases_lessons, 0) as finished_phrases_lessons')
            ->selectRaw($totalPhrasesLessons . ' as total_phrases_lessons')
            ->selectRaw($phrasesPercentExpr . ' as finished_phrases_percent')
            ->selectRaw('COALESCE(w.total_time_words, 0) as total_time_words')
            ->selectRaw('COALESCE(p.total_time_phrases, 0) as total_time_phrases')
            ->selectRaw($progressScoreExpr . ' as progress_score')
            ->orderByDesc('progress_score')
            ->orderByDesc('total_time_words')
            ->orderByDesc('total_time_phrases')
            ->orderBy('u.user_id');

        $paginator = $query
            ->paginate($filters['users_per_page'], ['*'], 'users_page')
            ->withQueryString();

        $userIds = collect($paginator->items())
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->values();

        $usersById = collect();
        if ($userIds->isNotEmpty()) {
            $usersById = DB::connection('mysql')
                ->table('users')
                ->select('id', 'username', 'name')
                ->whereIn('id', $userIds)
                ->get()
                ->keyBy('id');
        }

        $paginator->getCollection()->transform(function ($row) use ($usersById) {
            $user = $usersById->get($row->user_id);

            $row->user_label = $user
                ? trim((string) (($user->username ?? '') !== '' ? $user->username : ($user->name ?? '')))
                : null;

            return $row;
        });

        return $paginator;
    }

    private function countFinishedLessons($userId, $startTs, $endTs): int
    {
        $wordsFinished = DB::connection('tenant')
            ->table('cardsLearnWordsHistory')
            ->where('end_time', '>', 0)
            ->where('user_id', '>', 0);

        if ($userId !== null) {
            $wordsFinished->where('user_id', $userId);
        }

        $this->applyPeriodToFinishedScope($wordsFinished, $startTs, $endTs);

        $wordsCount = $userId !== null
            ? (int) $wordsFinished->distinct()->count('card_id')
            : (int) $wordsFinished->distinct()->count(DB::raw('CONCAT(user_id, "#", card_id)'));

        $phrasesFinished = DB::connection('tenant')
            ->table('flashcards_question_history')
            ->where('end_time', '>', 0)
            ->where('user_id', '>', 0);

        if ($userId !== null) {
            $phrasesFinished->where('user_id', $userId);
        }

        $this->applyPeriodToFinishedScope($phrasesFinished, $startTs, $endTs);

        $phrasesCount = $userId !== null
            ? (int) $phrasesFinished->distinct()->count('card_id')
            : (int) $phrasesFinished->distinct()->count(DB::raw('CONCAT(user_id, "#", card_id)'));

        return $wordsCount + $phrasesCount;
    }

    private function applyPeriodToFinishedScope($query, $startTs, $endTs, $endColumn = 'end_time'): void
    {
        if ($startTs === null || $endTs === null) {
            return;
        }

        $query->whereBetween($endColumn, [$startTs, $endTs]);
    }

    private function applyPeriodToHistoryScope($query, $startTs, $endTs, $startColumn = 'start_time', $endColumn = 'end_time'): void
    {
        if ($startTs === null || $endTs === null) {
            return;
        }

        $query->where(function ($subQuery) use ($startTs, $endTs, $startColumn, $endColumn) {
            $subQuery
                ->where(function ($endedQuery) use ($startTs, $endTs, $endColumn) {
                    $endedQuery->where($endColumn, '>', 0)
                        ->whereBetween($endColumn, [$startTs, $endTs]);
                })
                ->orWhere(function ($startedQuery) use ($startTs, $endTs, $startColumn, $endColumn) {
                    $startedQuery->where(function ($endEmptyQuery) use ($endColumn) {
                            $endEmptyQuery->whereNull($endColumn)
                                ->orWhere($endColumn, '<=', 0);
                        })
                        ->whereBetween($startColumn, [$startTs, $endTs]);
                });
        });
    }

    private function toLikePattern(string $value): string
    {
        return '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value) . '%';
    }

    private function resolveFlashcardAttemptUserIds(string $search): ?Collection
    {
        if ($search === '') {
            return null;
        }

        $userIds = collect();
        if (ctype_digit($search)) {
            $userIds->push((int) $search);
        }

        $like = $this->toLikePattern($search);
        $matchedUsers = DB::connection('mysql')
            ->table('users')
            ->select('id')
            ->where(function ($query) use ($like) {
                $query->where('username', 'like', $like)
                    ->orWhere('name', 'like', $like);
            })
            ->limit(200)
            ->get()
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            });

        return $userIds
            ->concat($matchedUsers)
            ->filter(function ($id) {
                return $id > 0;
            })
            ->unique()
            ->values();
    }

    private function getUsersByIds(Collection $userIds): Collection
    {
        $ids = $userIds
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) {
                return $id > 0;
            })
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return DB::connection('mysql')
            ->table('users')
            ->select('id', 'username', 'name')
            ->whereIn('id', $ids->all())
            ->get()
            ->keyBy('id');
    }

    private function decodeJsonObjectFromText(string $value): array
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            throw new \RuntimeException('Empty model response.');
        }

        $decoded = $this->tryDecodeJsonObject($trimmed);
        if ($decoded !== null) {
            return $decoded;
        }

        $candidates = [];

        if (preg_match_all('/```(?:json)?\s*([\s\S]*?)```/i', $trimmed, $matches) === 1) {
            foreach ((array) ($matches[1] ?? []) as $block) {
                $candidate = trim((string) $block);
                if ($candidate !== '') {
                    $candidates[] = $candidate;
                }
            }
        }

        foreach ($this->extractBalancedJsonSegments($trimmed, '{', '}') as $segment) {
            $candidate = trim($segment);
            if ($candidate !== '') {
                $candidates[] = $candidate;
            }
        }

        foreach ($this->extractBalancedJsonSegments($trimmed, '[', ']') as $segment) {
            $candidate = trim($segment);
            if ($candidate !== '') {
                $candidates[] = $candidate;
            }
        }

        $seen = [];
        foreach ($candidates as $candidate) {
            $hash = md5($candidate);
            if (isset($seen[$hash])) {
                continue;
            }
            $seen[$hash] = true;

            $decoded = $this->tryDecodeJsonObject($candidate);
            if ($decoded !== null) {
                return $decoded;
            }
        }

        $oneLine = preg_replace('/\s+/u', ' ', $trimmed);
        $snippet = substr((string) $oneLine, 0, 300);
        throw new \RuntimeException('Failed to parse JSON from model response. Snippet: ' . $snippet);
    }

    private function tryDecodeJsonObject(string $candidate): ?array
    {
        $decoded = json_decode($candidate, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return null;
        }

        if ($this->isAssocArray($decoded)) {
            return $decoded;
        }

        foreach ($decoded as $item) {
            if (is_array($item) && $this->isAssocArray($item)) {
                return $item;
            }
        }

        return null;
    }

    private function extractBalancedJsonSegments(string $text, string $openChar, string $closeChar): array
    {
        $segments = [];
        $depth = 0;
        $start = -1;
        $inString = false;
        $escape = false;
        $length = strlen($text);

        for ($i = 0; $i < $length; $i++) {
            $char = $text[$i];

            if ($inString) {
                if ($escape) {
                    $escape = false;
                    continue;
                }
                if ($char === '\\') {
                    $escape = true;
                    continue;
                }
                if ($char === '"') {
                    $inString = false;
                }
                continue;
            }

            if ($char === '"') {
                $inString = true;
                continue;
            }

            if ($char === $openChar) {
                if ($depth === 0) {
                    $start = $i;
                }
                $depth++;
                continue;
            }

            if ($char === $closeChar && $depth > 0) {
                $depth--;
                if ($depth === 0 && $start >= 0) {
                    $segments[] = substr($text, $start, $i - $start + 1);
                    $start = -1;
                }
            }
        }

        return $segments;
    }

    private function isAssocArray(array $value): bool
    {
        $expectedKey = 0;
        foreach (array_keys($value) as $key) {
            if ($key !== $expectedKey) {
                return true;
            }
            $expectedKey++;
        }

        return false;
    }

    private function extractOpenAiText($value): string
    {
        if (is_string($value)) {
            return trim($value);
        }

        if (is_numeric($value)) {
            return trim((string) $value);
        }

        if (!is_array($value)) {
            return '';
        }

        $parts = [];

        if ($this->isAssocArray($value)) {
            foreach (['output_text', 'text', 'value', 'content', 'message'] as $key) {
                if (!array_key_exists($key, $value)) {
                    continue;
                }
                $text = $this->extractOpenAiText($value[$key]);
                if ($text !== '') {
                    $parts[] = $text;
                }
            }

            if ($parts !== []) {
                return trim(implode(PHP_EOL, $parts));
            }
        }

        foreach ($value as $item) {
            $text = $this->extractOpenAiText($item);
            if ($text !== '') {
                $parts[] = $text;
            }
        }

        return trim(implode(PHP_EOL, $parts));
    }

    private function sqlQuoteString(string $value): string
    {
        return "'" . str_replace("'", "''", $value) . "'";
    }

    private function normalizeV2ItemType(string $type): string
    {
        $value = strtolower(trim($type));
        if ($value === 'questions') {
            return 'question';
        }
        if ($value === 'phrases') {
            return 'phrase';
        }
        if ($value === 'words') {
            return 'word';
        }

        return $value;
    }

    private function normalizeV2DuplicateText(string $value): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim($value));
        return trim((string) ($normalized ?? ''));
    }

    private function resolveFlashCardsAudioUrl(string $filePath): ?string
    {
        $path = trim($filePath);
        if ($path === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $path) === 1) {
            return $this->normalizeAbsoluteUrl($path);
        }

        if (strpos($path, '/ru/ru-en/packs/assest/game-card-word/content/audio_file/en-to-ru/') === 0) {
            return self::PROD_BASE_URL . $path;
        }

        if (strpos($path, '/packs/assest/game-card-word/content/audio_file/en-to-ru/') === 0) {
            return self::PROD_BASE_URL . '/ru/ru-en' . $path;
        }

        if (strpos($path, 'packs/assest/game-card-word/content/audio_file/en-to-ru/') === 0) {
            return self::PROD_BASE_URL . '/ru/ru-en/' . ltrim($path, '/');
        }

        if (strpos($path, '/game-card-word/content/audio_file/en-to-ru/') === 0) {
            return self::PROD_BASE_URL . '/ru/ru-en/packs/assest' . $path;
        }

        if (strpos($path, 'game-card-word/content/audio_file/en-to-ru/') === 0) {
            return self::PROD_BASE_URL . '/ru/ru-en/packs/assest/' . ltrim($path, '/');
        }

        if (strpos($path, '/') === 0) {
            return self::PROD_BASE_URL . $path;
        }

        return self::FLASHCARDS_AUDIO_BASE . '/' . ltrim($path, '/');
    }

    private function resolveFlashCardsPhrasesAudioUrl(string $filePath): ?string
    {
        $path = trim($filePath);
        if ($path === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $path) === 1) {
            return $this->normalizeAbsoluteUrl($path);
        }

        if (strpos($path, '/ru/ru-en/packs/assest/flashcard-questions-and-sentences/audio/audio-en-ru/') === 0) {
            return self::PROD_BASE_URL . $path;
        }

        if (strpos($path, '/packs/assest/flashcard-questions-and-sentences/audio/audio-en-ru/') === 0) {
            return self::PROD_BASE_URL . '/ru/ru-en' . $path;
        }

        if (strpos($path, 'packs/assest/flashcard-questions-and-sentences/audio/audio-en-ru/') === 0) {
            return self::PROD_BASE_URL . '/ru/ru-en/' . ltrim($path, '/');
        }

        if (strpos($path, '/flashcard-questions-and-sentences/audio/audio-en-ru/') === 0) {
            return self::PROD_BASE_URL . '/ru/ru-en/packs/assest' . $path;
        }

        if (strpos($path, 'flashcard-questions-and-sentences/audio/audio-en-ru/') === 0) {
            return self::PROD_BASE_URL . '/ru/ru-en/packs/assest/' . ltrim($path, '/');
        }

        if (strpos($path, '/') === 0) {
            return self::PROD_BASE_URL . $path;
        }

        return self::FLASHCARDS_PHRASES_AUDIO_BASE . '/' . ltrim($path, '/');
    }

    private function normalizeAbsoluteUrl(string $url): string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if ($host === 'localhost' || $host === '127.0.0.1' || $host === 'db') {
            $path = (string) parse_url($url, PHP_URL_PATH);
            if ($path !== '') {
                return self::PROD_BASE_URL . $path;
            }
        }

        return $url;
    }

    private function urlExists(string $url): bool
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_USERAGENT, 'DropyCRM-FlashCardsIntegrity/1.0');

            curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);

            if (($httpCode >= 200 && $httpCode < 400) || $httpCode === 401 || $httpCode === 403) {
                return true;
            }

            return false;
        }

        $headers = @get_headers($url);
        if (!is_array($headers) || empty($headers[0])) {
            return false;
        }

        if (preg_match('/\\s(\\d{3})\\s/', (string) $headers[0], $matches) !== 1) {
            return false;
        }

        $httpCode = (int) ($matches[1] ?? 0);
        return ($httpCode >= 200 && $httpCode < 400) || $httpCode === 401 || $httpCode === 403;
    }
}
