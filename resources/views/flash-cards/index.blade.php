<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Flash Cards Analytics" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Flash Cards Analytics" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h1 class="text-2xl font-semibold">Flash Cards Analytics</h1>
                        <p class="mt-2 text-sm text-slate-600">Words + Phrases progress, cu filtre globale, paginare și drill-down pe lecții.</p>
                        <div class="mt-3">
                            <a href="{{ route('flash-cards.debut-integrity') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                <i class="fa-solid fa-shield-halved"></i>
                                debut integrity
                            </a>
                        </div>
                    </div>

                    @if (!empty($error))
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ $error }}
                        </div>
                    @endif

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <form method="get" action="{{ route('flash-cards.index') }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                            <label class="text-xs font-semibold text-slate-600">
                                Period
                                <select name="period" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700">
                                    @foreach (($filters['period_options'] ?? []) as $key => $label)
                                        <option value="{{ $key }}" {{ ($filters['period'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="text-xs font-semibold text-slate-600">
                                Date start
                                <input type="date" name="date_start" value="{{ $filters['date_start'] ?? '' }}" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700" />
                            </label>

                            <label class="text-xs font-semibold text-slate-600">
                                Date end
                                <input type="date" name="date_end" value="{{ $filters['date_end'] ?? '' }}" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700" />
                            </label>

                            <label class="text-xs font-semibold text-slate-600">
                                User ID
                                <input type="number" name="user_id" min="1" step="1" value="{{ $filters['user_id'] ?? '' }}" placeholder="ex: 3141" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700" />
                            </label>

                            <label class="text-xs font-semibold text-slate-600">
                                Categories search
                                <input type="text" name="categories_q" value="{{ $filters['categories_q'] ?? '' }}" placeholder="title/code_name" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700" />
                            </label>

                            <label class="text-xs font-semibold text-slate-600">
                                Words search
                                <input type="text" name="words_q" value="{{ $filters['words_q'] ?? '' }}" placeholder="title/url" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700" />
                            </label>

                            <label class="text-xs font-semibold text-slate-600">
                                Words category
                                <select name="words_category" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700">
                                    <option value="">All</option>
                                    @foreach (($wordsCategoryOptions ?? collect()) as $option)
                                        <option value="{{ $option->url_category }}" {{ ($filters['words_category'] ?? '') === (string) $option->url_category ? 'selected' : '' }}>
                                            {{ $option->title_category }} ({{ $option->url_category }})
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="text-xs font-semibold text-slate-600">
                                Words level
                                <select name="words_level" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700">
                                    <option value="">All</option>
                                    @foreach (($wordsLevelOptions ?? collect()) as $level)
                                        <option value="{{ $level }}" {{ (string) ($filters['words_level'] ?? '') === (string) $level ? 'selected' : '' }}>Level {{ $level }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="text-xs font-semibold text-slate-600">
                                Words finished
                                <select name="words_finished" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700">
                                    <option value="all" {{ ($filters['words_finished'] ?? '') === 'all' ? 'selected' : '' }}>All</option>
                                    <option value="yes" {{ ($filters['words_finished'] ?? '') === 'yes' ? 'selected' : '' }}>Only finished</option>
                                    <option value="no" {{ ($filters['words_finished'] ?? '') === 'no' ? 'selected' : '' }}>Only unfinished</option>
                                </select>
                            </label>

                            <label class="text-xs font-semibold text-slate-600">
                                Phrases search
                                <input type="text" name="phrases_q" value="{{ $filters['phrases_q'] ?? '' }}" placeholder="title/url" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700" />
                            </label>

                            <label class="text-xs font-semibold text-slate-600">
                                Phrases finished
                                <select name="phrases_finished" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700">
                                    <option value="all" {{ ($filters['phrases_finished'] ?? '') === 'all' ? 'selected' : '' }}>All</option>
                                    <option value="yes" {{ ($filters['phrases_finished'] ?? '') === 'yes' ? 'selected' : '' }}>Only finished</option>
                                    <option value="no" {{ ($filters['phrases_finished'] ?? '') === 'no' ? 'selected' : '' }}>Only unfinished</option>
                                </select>
                            </label>

                            <label class="text-xs font-semibold text-slate-600">
                                Categories per page
                                <select name="categories_per_page" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700">
                                    @foreach ([20, 50, 100, 200] as $size)
                                        <option value="{{ $size }}" {{ (int) ($filters['categories_per_page'] ?? 20) === $size ? 'selected' : '' }}>{{ $size }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="text-xs font-semibold text-slate-600">
                                Words per page
                                <select name="words_per_page" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700">
                                    @foreach ([20, 50, 100, 200] as $size)
                                        <option value="{{ $size }}" {{ (int) ($filters['words_per_page'] ?? 20) === $size ? 'selected' : '' }}>{{ $size }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="text-xs font-semibold text-slate-600">
                                Phrases per page
                                <select name="phrases_per_page" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700">
                                    @foreach ([20, 50, 100, 200] as $size)
                                        <option value="{{ $size }}" {{ (int) ($filters['phrases_per_page'] ?? 20) === $size ? 'selected' : '' }}>{{ $size }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="text-xs font-semibold text-slate-600">
                                Users per page
                                <select name="users_per_page" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700">
                                    @foreach ([20, 50, 100, 200] as $size)
                                        <option value="{{ $size }}" {{ (int) ($filters['users_per_page'] ?? 20) === $size ? 'selected' : '' }}>{{ $size }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <div class="md:col-span-2 xl:col-span-4 flex flex-wrap items-center gap-2 pt-1">
                                <button type="submit" class="rounded-lg border border-slate-900 bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                                    Apply filters
                                </button>
                                <a href="{{ route('flash-cards.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    Reset
                                </a>
                                <span class="text-xs text-slate-500">
                                    Range: {{ $filters['date_start'] ?? '-' }} - {{ $filters['date_end'] ?? '-' }}
                                </span>
                            </div>
                        </form>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total lecții Words</div>
                            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format((int) ($kpis['total_words_lessons'] ?? 0)) }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total cuvinte Words</div>
                            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format((int) ($kpis['total_words_items'] ?? 0)) }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total lecții Phrases</div>
                            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format((int) ($kpis['total_phrases_lessons'] ?? 0)) }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total item-uri Phrases</div>
                            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format((int) ($kpis['total_phrases_items'] ?? 0)) }}</div>
                        </div>
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-wide text-emerald-600">Total lecții finalizate</div>
                            <div class="mt-2 text-2xl font-semibold text-emerald-700">{{ number_format((int) ($kpis['total_finished_lessons'] ?? 0)) }}</div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-800">Words Categories</h2>
                                <p class="text-xs text-slate-500">Filtru search: title_category / code_name</p>
                            </div>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-slate-500">
                                        <th class="pb-2 pr-3">ID</th>
                                        <th class="pb-2 pr-3">code_name</th>
                                        <th class="pb-2 pr-3">title_category</th>
                                        <th class="pb-2 pr-3">url_category</th>
                                        <th class="pb-2 pr-3">img_category</th>
                                        <th class="pb-2 pr-3">lessons_count</th>
                                        <th class="pb-2 pr-3">finished_lessons_count</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @if ($wordsCategories)
                                        @forelse ($wordsCategories as $row)
                                            <tr>
                                                <td class="py-2 pr-3 text-slate-600">{{ $row->id }}</td>
                                                <td class="py-2 pr-3 text-slate-700">{{ $row->code_name }}</td>
                                                <td class="py-2 pr-3 text-slate-800">
                                                    @php
                                                        $categoryLinkQuery = request()->query();
                                                        $categoryLinkQuery['words_category'] = $row->url_category;
                                                        $categoryLinkQuery['words_page'] = 1;
                                                    @endphp
                                                    <a href="{{ route('flash-cards.index') . '?' . http_build_query($categoryLinkQuery) }}" class="font-semibold text-sky-700 hover:underline">
                                                        {{ $row->title_category }}
                                                    </a>
                                                </td>
                                                <td class="py-2 pr-3 text-slate-600">{{ $row->url_category }}</td>
                                                <td class="py-2 pr-3 text-slate-600">{{ $row->img_category }}</td>
                                                <td class="py-2 pr-3 text-slate-700">{{ number_format((int) $row->lessons_count) }}</td>
                                                <td class="py-2 pr-3 text-slate-700">
                                                    @if (($filters['user_id'] ?? null) === null)
                                                        <span class="text-slate-400">-</span>
                                                    @else
                                                        {{ number_format((int) ($row->finished_lessons_count ?? 0)) }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="py-3 text-slate-500">No categories found.</td>
                                            </tr>
                                        @endforelse
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        @if ($wordsCategories && method_exists($wordsCategories, 'links'))
                            <div class="mt-4">{{ $wordsCategories->links() }}</div>
                        @endif
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-800">Words Lessons</h2>
                                <p class="text-xs text-slate-500">Finished și scoruri sunt calculate pentru userul selectat.</p>
                            </div>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-slate-500">
                                        <th class="pb-2 pr-3">ID</th>
                                        <th class="pb-2 pr-3">group_category</th>
                                        <th class="pb-2 pr-3">title</th>
                                        <th class="pb-2 pr-3">url</th>
                                        <th class="pb-2 pr-3">level</th>
                                        <th class="pb-2 pr-3">words_count</th>
                                        <th class="pb-2 pr-3">finished</th>
                                        <th class="pb-2 pr-3">score carousel</th>
                                        <th class="pb-2 pr-3">score quiz</th>
                                        <th class="pb-2 pr-3">correct</th>
                                        <th class="pb-2 pr-3">wrong</th>
                                        <th class="pb-2 pr-3">time carousel</th>
                                        <th class="pb-2 pr-3">time quiz</th>
                                        <th class="pb-2 pr-3">end_time</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @if ($wordsLessons)
                                        @forelse ($wordsLessons as $row)
                                            <tr>
                                                <td class="py-2 pr-3 text-slate-600">{{ $row->id }}</td>
                                                <td class="py-2 pr-3 text-slate-700">{{ $row->group_category }}</td>
                                                <td class="py-2 pr-3 text-slate-800">
                                                    @php
                                                        $wordsDetailQuery = request()->query();
                                                    @endphp
                                                    <a href="{{ route('flash-cards.words-lessons.show', ['lessonId' => $row->id]) . '?' . http_build_query($wordsDetailQuery) }}" class="font-semibold text-sky-700 hover:underline">
                                                        {{ $row->title }}
                                                    </a>
                                                </td>
                                                <td class="py-2 pr-3 text-slate-600">{{ $row->url }}</td>
                                                <td class="py-2 pr-3 text-slate-600">{{ $row->category }}</td>
                                                <td class="py-2 pr-3 text-slate-700">{{ number_format((int) $row->words_count) }}</td>
                                                <td class="py-2 pr-3">
                                                    @if (($filters['user_id'] ?? null) === null)
                                                        <span class="text-slate-400">-</span>
                                                    @elseif ((int) ($row->finished ?? 0) === 1)
                                                        <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">Yes</span>
                                                    @else
                                                        <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">No</span>
                                                    @endif
                                                </td>
                                                <td class="py-2 pr-3 text-slate-600">{{ $row->score_carousel !== null ? $row->score_carousel : '-' }}</td>
                                                <td class="py-2 pr-3 text-slate-600">{{ $row->score_quiz !== null ? $row->score_quiz : '-' }}</td>
                                                <td class="py-2 pr-3 text-slate-600">{{ $row->correct_answers !== null ? $row->correct_answers : '-' }}</td>
                                                <td class="py-2 pr-3 text-slate-600">{{ $row->wrong_answers !== null ? $row->wrong_answers : '-' }}</td>
                                                <td class="py-2 pr-3 text-slate-600">{{ $row->time_carousel !== null ? number_format((int) $row->time_carousel) : '-' }}</td>
                                                <td class="py-2 pr-3 text-slate-600">{{ $row->time_quiz !== null ? number_format((int) $row->time_quiz) : '-' }}</td>
                                                <td class="py-2 pr-3 text-slate-600">
                                                    @if (!empty($row->end_time) && (int) $row->end_time > 0)
                                                        {{ date('Y-m-d H:i', (int) $row->end_time) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="14" class="py-3 text-slate-500">No words lessons found.</td>
                                            </tr>
                                        @endforelse
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        @if ($wordsLessons && method_exists($wordsLessons, 'links'))
                            <div class="mt-4">{{ $wordsLessons->links() }}</div>
                        @endif
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-800">Phrases Lessons</h2>
                                <p class="text-xs text-slate-500">Finished și score carousel sunt calculate pentru userul selectat.</p>
                            </div>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-slate-500">
                                        <th class="pb-2 pr-3">ID</th>
                                        <th class="pb-2 pr-3">title</th>
                                        <th class="pb-2 pr-3">url</th>
                                        <th class="pb-2 pr-3">content_count</th>
                                        <th class="pb-2 pr-3">finished</th>
                                        <th class="pb-2 pr-3">score carousel</th>
                                        <th class="pb-2 pr-3">time carousel</th>
                                        <th class="pb-2 pr-3">start_time</th>
                                        <th class="pb-2 pr-3">end_time</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @if ($phrasesLessons)
                                        @forelse ($phrasesLessons as $row)
                                            <tr>
                                                <td class="py-2 pr-3 text-slate-600">{{ $row->id }}</td>
                                                <td class="py-2 pr-3 text-slate-800">
                                                    @php
                                                        $phrasesDetailQuery = request()->query();
                                                    @endphp
                                                    <a href="{{ route('flash-cards.phrases-lessons.show', ['lessonId' => $row->id]) . '?' . http_build_query($phrasesDetailQuery) }}" class="font-semibold text-sky-700 hover:underline">
                                                        {{ $row->title !== '' ? $row->title : '-' }}
                                                    </a>
                                                </td>
                                                <td class="py-2 pr-3 text-slate-600">{{ $row->url }}</td>
                                                <td class="py-2 pr-3 text-slate-700">{{ number_format((int) $row->content_count) }}</td>
                                                <td class="py-2 pr-3">
                                                    @if (($filters['user_id'] ?? null) === null)
                                                        <span class="text-slate-400">-</span>
                                                    @elseif ((int) ($row->finished ?? 0) === 1)
                                                        <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">Yes</span>
                                                    @else
                                                        <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">No</span>
                                                    @endif
                                                </td>
                                                <td class="py-2 pr-3 text-slate-600">{{ $row->score_carousel !== null ? $row->score_carousel : '-' }}</td>
                                                <td class="py-2 pr-3 text-slate-600">{{ $row->time_carousel !== null ? number_format((int) $row->time_carousel) : '-' }}</td>
                                                <td class="py-2 pr-3 text-slate-600">
                                                    @if (!empty($row->start_time) && (int) $row->start_time > 0)
                                                        {{ date('Y-m-d H:i', (int) $row->start_time) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="py-2 pr-3 text-slate-600">
                                                    @if (!empty($row->end_time) && (int) $row->end_time > 0)
                                                        {{ date('Y-m-d H:i', (int) $row->end_time) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="py-3 text-slate-500">No phrases lessons found.</td>
                                            </tr>
                                        @endforelse
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        @if ($phrasesLessons && method_exists($phrasesLessons, 'links'))
                            <div class="mt-4">{{ $phrasesLessons->links() }}</div>
                        @endif
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-800">Users Progress</h2>
                                <p class="text-xs text-slate-500">Sortare desc după progres total (Words% + Phrases%).</p>
                            </div>
                        </div>

                        @php
                            $formatSeconds = function ($seconds) {
                                $seconds = (int) $seconds;
                                if ($seconds <= 0) {
                                    return '0m';
                                }

                                $hours = intdiv($seconds, 3600);
                                $minutes = intdiv($seconds % 3600, 60);
                                $rest = $seconds % 60;

                                if ($hours > 0) {
                                    return $hours . 'h ' . $minutes . 'm';
                                }

                                if ($minutes > 0) {
                                    return $minutes . 'm ' . $rest . 's';
                                }

                                return $rest . 's';
                            };
                        @endphp

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-slate-500">
                                        <th class="pb-2 pr-3">user_id</th>
                                        <th class="pb-2 pr-3">User</th>
                                        <th class="pb-2 pr-3">Words progress</th>
                                        <th class="pb-2 pr-3">Phrases progress</th>
                                        <th class="pb-2 pr-3">Total time words</th>
                                        <th class="pb-2 pr-3">Total time phrases</th>
                                        <th class="pb-2 pr-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @if ($usersProgress)
                                        @forelse ($usersProgress as $row)
                                            <tr>
                                                <td class="py-2 pr-3 text-slate-700">
                                                    <a href="{{ url('/users/' . $row->user_id) }}" class="font-semibold text-sky-700 hover:underline">{{ $row->user_id }}</a>
                                                </td>
                                                <td class="py-2 pr-3 text-slate-700">{{ !empty($row->user_label) ? $row->user_label : '-' }}</td>
                                                <td class="py-2 pr-3 text-slate-600">
                                                    {{ (int) $row->finished_words_lessons }} / {{ (int) $row->total_words_lessons }}
                                                    <span class="ml-1 text-xs text-slate-500">({{ number_format((float) $row->finished_words_percent, 2) }}%)</span>
                                                </td>
                                                <td class="py-2 pr-3 text-slate-600">
                                                    {{ (int) $row->finished_phrases_lessons }} / {{ (int) $row->total_phrases_lessons }}
                                                    <span class="ml-1 text-xs text-slate-500">({{ number_format((float) $row->finished_phrases_percent, 2) }}%)</span>
                                                </td>
                                                <td class="py-2 pr-3 text-slate-600">{{ $formatSeconds($row->total_time_words ?? 0) }}</td>
                                                <td class="py-2 pr-3 text-slate-600">{{ $formatSeconds($row->total_time_phrases ?? 0) }}</td>
                                                <td class="py-2 pr-3 text-slate-600">
                                                    @php
                                                        $onlyUserQuery = request()->query();
                                                        $onlyUserQuery['user_id'] = $row->user_id;
                                                        $onlyUserQuery['categories_page'] = 1;
                                                        $onlyUserQuery['words_page'] = 1;
                                                        $onlyUserQuery['phrases_page'] = 1;
                                                        $onlyUserQuery['users_page'] = 1;
                                                    @endphp
                                                    <a href="{{ route('flash-cards.index') . '?' . http_build_query($onlyUserQuery) }}" class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                                        Filter user
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="py-3 text-slate-500">No users progress found for selected filters.</td>
                                            </tr>
                                        @endforelse
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        @if ($usersProgress && method_exists($usersProgress, 'links'))
                            <div class="mt-4">{{ $usersProgress->links() }}</div>
                        @endif
                    </div>
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
    </body>
</html>
