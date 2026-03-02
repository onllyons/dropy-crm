<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Words Lesson Details" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Words Lesson Details" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold">Words Lesson Details</h1>
                                @if (!empty($detail['lesson']))
                                    <p class="mt-2 text-sm text-slate-600">
                                        {{ $detail['lesson']->title }}
                                        · ID {{ $detail['lesson']->id }}
                                        · URL {{ $detail['lesson']->url }}
                                        · Group {{ $detail['lesson']->group_category }}
                                        · Level {{ $detail['lesson']->category }}
                                    </p>
                                @endif
                            </div>
                            @php
                                $backUrl = route('flash-cards.index');
                                if (!empty($backQuery)) {
                                    $backUrl .= '?' . http_build_query($backQuery);
                                }
                            @endphp
                            <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                <i class="fa-solid fa-arrow-left"></i>
                                Back to flash-cards
                            </a>
                        </div>
                    </div>

                    @if (!empty($error))
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $error }}</div>
                    @endif

                    @if (!empty($detail['lesson']))
                        @php
                            $activeTab = $detail['activeTab'] ?? 'content';
                            $tabCounts = $detail['tabCounts'] ?? ['content' => 0, 'quiz' => 0, 'history' => 0];
                            $tabQuery = request()->query();
                            unset($tabQuery['tab_page']);
                        @endphp

                        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex flex-wrap gap-2">
                                @php
                                    $contentQuery = $tabQuery;
                                    $contentQuery['tab'] = 'content';
                                    $quizQuery = $tabQuery;
                                    $quizQuery['tab'] = 'quiz';
                                    $historyQuery = $tabQuery;
                                    $historyQuery['tab'] = 'history';
                                @endphp
                                <a href="{{ route('flash-cards.words-lessons.show', ['lessonId' => $detail['lesson']->id]) . '?' . http_build_query($contentQuery) }}" class="rounded-lg border px-3 py-2 text-sm font-semibold {{ $activeTab === 'content' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}">
                                    Content ({{ number_format((int) ($tabCounts['content'] ?? 0)) }})
                                </a>
                                <a href="{{ route('flash-cards.words-lessons.show', ['lessonId' => $detail['lesson']->id]) . '?' . http_build_query($quizQuery) }}" class="rounded-lg border px-3 py-2 text-sm font-semibold {{ $activeTab === 'quiz' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}">
                                    Quiz ({{ number_format((int) ($tabCounts['quiz'] ?? 0)) }})
                                </a>
                                <a href="{{ route('flash-cards.words-lessons.show', ['lessonId' => $detail['lesson']->id]) . '?' . http_build_query($historyQuery) }}" class="rounded-lg border px-3 py-2 text-sm font-semibold {{ $activeTab === 'history' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}">
                                    User history ({{ number_format((int) ($tabCounts['history'] ?? 0)) }})
                                </a>
                            </div>

                            <div class="mt-4 overflow-x-auto">
                                @if ($activeTab === 'content')
                                    <table class="min-w-full text-sm">
                                        <thead>
                                            <tr class="border-b border-slate-200 text-left text-slate-500">
                                                <th class="pb-2 pr-3">ID</th>
                                                <th class="pb-2 pr-3">word_en</th>
                                                <th class="pb-2 pr-3">word_ru</th>
                                                <th class="pb-2 pr-3">word_audio</th>
                                                <th class="pb-2 pr-3">word_audio_mp3</th>
                                                <th class="pb-2 pr-3">British</th>
                                                <th class="pb-2 pr-3">American</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @forelse (($detail['rows'] ?? []) as $row)
                                                <tr>
                                                    <td class="py-2 pr-3 text-slate-600">{{ $row->id }}</td>
                                                    <td class="py-2 pr-3 text-slate-800">{{ $row->word_en }}</td>
                                                    <td class="py-2 pr-3 text-slate-700">{{ $row->word_ru }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ $row->word_audio }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ $row->word_audio_mp3 }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ $row->tophoneticsBritish }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ $row->tophoneticsAmerican }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="py-3 text-slate-500">No content rows.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                @elseif ($activeTab === 'quiz')
                                    <table class="min-w-full text-sm">
                                        <thead>
                                            <tr class="border-b border-slate-200 text-left text-slate-500">
                                                <th class="pb-2 pr-3">ID</th>
                                                <th class="pb-2 pr-3">type</th>
                                                <th class="pb-2 pr-3">question</th>
                                                <th class="pb-2 pr-3">answer_1</th>
                                                <th class="pb-2 pr-3">answer_2</th>
                                                <th class="pb-2 pr-3">answer_3</th>
                                                <th class="pb-2 pr-3">answer_4</th>
                                                <th class="pb-2 pr-3">answer_correct</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @forelse (($detail['rows'] ?? []) as $row)
                                                <tr>
                                                    <td class="py-2 pr-3 text-slate-600">{{ $row->id }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ $row->type }}</td>
                                                    <td class="py-2 pr-3 text-slate-800">{{ $row->question }}</td>
                                                    <td class="py-2 pr-3 text-slate-700">{{ $row->answer_1 }}</td>
                                                    <td class="py-2 pr-3 text-slate-700">{{ $row->answer_2 }}</td>
                                                    <td class="py-2 pr-3 text-slate-700">{{ $row->answer_3 }}</td>
                                                    <td class="py-2 pr-3 text-slate-700">{{ $row->answer_4 }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ $row->answer_correct }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="py-3 text-slate-500">No quiz rows.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                @else
                                    <table class="min-w-full text-sm">
                                        <thead>
                                            <tr class="border-b border-slate-200 text-left text-slate-500">
                                                <th class="pb-2 pr-3">ID</th>
                                                <th class="pb-2 pr-3">user_id</th>
                                                <th class="pb-2 pr-3">score_carousel</th>
                                                <th class="pb-2 pr-3">score_quiz</th>
                                                <th class="pb-2 pr-3">correct</th>
                                                <th class="pb-2 pr-3">wrong</th>
                                                <th class="pb-2 pr-3">time_carousel</th>
                                                <th class="pb-2 pr-3">time_quiz</th>
                                                <th class="pb-2 pr-3">start_time</th>
                                                <th class="pb-2 pr-3">end_time</th>
                                                <th class="pb-2 pr-3">wrong_words_json</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @forelse (($detail['rows'] ?? []) as $row)
                                                <tr>
                                                    <td class="py-2 pr-3 text-slate-600">{{ $row->id }}</td>
                                                    <td class="py-2 pr-3 text-slate-700">
                                                        <a href="{{ url('/users/' . $row->user_id) }}" class="text-sky-700 hover:underline">{{ $row->user_id }}</a>
                                                    </td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ $row->score_carousel }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ $row->score_quiz }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ $row->correct_answers }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ $row->wrong_answers }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ number_format((int) $row->time_carousel) }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ number_format((int) $row->time_quiz) }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ !empty($row->start_time) ? date('Y-m-d H:i', (int) $row->start_time) : '-' }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ !empty($row->end_time) ? date('Y-m-d H:i', (int) $row->end_time) : '-' }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">
                                                        @if (!empty($row->wrong_words_pretty))
                                                            <details>
                                                                <summary class="cursor-pointer text-xs font-semibold text-sky-700">view</summary>
                                                                <pre class="mt-2 max-w-[420px] overflow-x-auto rounded bg-slate-50 p-2 text-[11px] text-slate-700">{{ $row->wrong_words_pretty }}</pre>
                                                            </details>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="11" class="py-3 text-slate-500">No history rows.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                @endif
                            </div>

                            @if (!empty($detail['rows']) && method_exists($detail['rows'], 'links'))
                                <div class="mt-4">{{ $detail['rows']->links() }}</div>
                            @endif
                        </div>
                    @endif
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
    </body>
</html>
