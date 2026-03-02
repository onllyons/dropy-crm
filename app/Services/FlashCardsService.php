<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class FlashCardsService
{
    private const PROD_BASE_URL = 'https://www.language.onllyons.com';
    private const FLASHCARDS_AUDIO_BASE = 'https://www.language.onllyons.com/ru/ru-en/packs/assest/game-card-word/content/audio_file/en-to-ru';
    private const FLASHCARDS_PHRASES_AUDIO_BASE = 'https://www.language.onllyons.com/ru/ru-en/packs/assest/flashcard-questions-and-sentences/audio/audio-en-ru';

    public function getDebutIntegrityReport(bool $realCheckEnabled = true, int $realCheckLimit = 500): array
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

        $realCheckLimit = max(10, min($realCheckLimit, 5000));
        $checkedRowsCount = 0;
        $missingOnServerRows = collect();

        if ($realCheckEnabled) {
            $rowsToCheck = (clone $base)
                ->select('id', 'url_display', 'word_en', 'word_ru', 'word_audio_mp3')
                ->whereRaw("TRIM(COALESCE(word_audio_mp3, '')) <> ''")
                ->orderBy('id')
                ->limit($realCheckLimit)
                ->get();

            $checkedRowsCount = $rowsToCheck->count();
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
            'checkedRowsCount' => (int) $checkedRowsCount,
            'missingOnServerCount' => (int) $missingOnServerRows->count(),
            'missingOnServerRows' => $missingOnServerRows,
        ];
    }

    public function getPhrasesDebutIntegrityReport(bool $realCheckEnabled = true, int $realCheckLimit = 500): array
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

        $realCheckLimit = max(10, min($realCheckLimit, 5000));
        $checkedRowsCount = 0;
        $missingOnServerRows = collect();

        if ($realCheckEnabled) {
            $rowsToCheck = (clone $base)
                ->select('id', 'url_display', 'word_en', 'word_ru', 'word_audio')
                ->whereRaw("TRIM(COALESCE(word_audio, '')) <> ''")
                ->orderBy('id')
                ->limit($realCheckLimit)
                ->get();

            $checkedRowsCount = $rowsToCheck->count();
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
            'checkedRowsCount' => (int) $checkedRowsCount,
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
