<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Flash Cards Debut Integrity" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Flash Cards Debut Integrity" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold">debut integrity</h1>
                                <p class="mt-2 text-sm text-slate-600">
                                    Quick check pentru audio files:
                                    <span class="font-semibold">cardsLearnWordsContent.word_audio_mp3</span>
                                    +
                                    <span class="font-semibold">flashcards_question_sentences_content.word_audio</span>.
                                </p>
                            </div>
                            <a href="{{ route('flash-cards.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                <i class="fa-solid fa-arrow-left"></i>
                                Back to flash-cards
                            </a>
                        </div>
                    </div>

                    @if (!empty($error))
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ $error }}
                        </div>
                    @endif

                    <div class="mt-6 rounded-2xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
                        <div class="text-sm font-semibold text-indigo-900">Words audio integrity (word_audio_mp3)</div>
                        <div class="mt-1 text-xs text-indigo-800">
                            Expected base: <span class="font-semibold">https://www.language.onllyons.com/ru/ru-en/packs/assest/game-card-word/content/audio_file/en-to-ru/</span>
                        </div>
                        <form method="get" action="{{ route('flash-cards.debut-integrity') }}" class="mt-3 flex flex-wrap items-end gap-3 rounded-xl border border-indigo-200 bg-white p-3">
                            <label class="text-xs font-semibold text-slate-700">
                                Real check (HTTP)
                                <input type="hidden" name="real_check" value="0" />
                                <input type="checkbox" name="real_check" value="1" class="ml-2 rounded border-slate-300" {{ !empty($wordsReport['realCheckEnabled']) ? 'checked' : '' }} />
                            </label>
                            <label class="text-xs font-semibold text-slate-700">
                                Rows limit
                                <input type="number" min="10" max="50000" name="real_check_limit" value="{{ (int) ($wordsReport['realCheckLimit'] ?? 500) }}" class="ml-2 w-24 rounded border border-slate-300 bg-white px-2 py-1 text-xs font-normal text-slate-700" />
                            </label>
                            <label class="text-xs font-semibold text-slate-700">
                                Start after ID
                                <input type="number" min="0" step="1" name="start_after_id" value="{{ (int) ($wordsReport['startAfterId'] ?? 0) }}" class="ml-2 w-28 rounded border border-slate-300 bg-white px-2 py-1 text-xs font-normal text-slate-700" />
                            </label>
                            <button type="submit" class="rounded-lg border border-indigo-200 bg-indigo-100 px-3 py-1.5 text-xs font-semibold text-indigo-900 hover:bg-indigo-200">
                                Run check
                            </button>
                        </form>

                        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="rounded-xl border border-indigo-200 bg-white p-3">
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-indigo-500">Total rows</div>
                                <div class="mt-1 text-xl font-semibold text-indigo-900">{{ number_format((int) ($wordsReport['totalRows'] ?? 0)) }}</div>
                            </div>
                            <div class="rounded-xl border border-indigo-200 bg-white p-3">
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-indigo-500">Missing word_audio_mp3</div>
                                <div class="mt-1 text-xl font-semibold text-indigo-900">{{ number_format((int) ($wordsReport['missingPathCount'] ?? 0)) }}</div>
                            </div>
                            <div class="rounded-xl border border-indigo-200 bg-white p-3">
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-indigo-500">Checked rows (HTTP)</div>
                                <div class="mt-1 text-xl font-semibold text-indigo-900">{{ number_format((int) ($wordsReport['checkedRowsCount'] ?? 0)) }}</div>
                                <div class="mt-1 text-[11px] text-indigo-700">
                                    IDs: {{ $wordsReport['checkedMinId'] ?? '-' }} - {{ $wordsReport['checkedMaxId'] ?? '-' }}
                                </div>
                            </div>
                            <div class="rounded-xl border border-red-200 bg-red-50 p-3">
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-red-500">Missing on server</div>
                                <div class="mt-1 text-xl font-semibold text-red-700">{{ number_format((int) ($wordsReport['missingOnServerCount'] ?? 0)) }}</div>
                            </div>
                        </div>
                    </div>

                    @php
                        $nextWordsStart = (int) ($wordsReport['nextStartAfterId'] ?? 0);
                        $nextPhrasesStart = (int) ($phrasesReport['nextStartAfterId'] ?? 0);
                        $nextBatchStart = max($nextWordsStart, $nextPhrasesStart);
                    @endphp
                    <div class="mt-3">
                        <a href="{{ route('flash-cards.debut-integrity', ['real_check' => !empty($wordsReport['realCheckEnabled']) ? 1 : 0, 'real_check_limit' => (int) ($wordsReport['realCheckLimit'] ?? 500), 'start_after_id' => $nextBatchStart]) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                            <i class="fa-solid fa-forward-step"></i>
                            Next batch
                        </a>
                        <span class="ml-2 text-xs text-slate-500">next start_after_id: {{ $nextBatchStart }}</span>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-semibold text-slate-700">Words rows missing on server</div>
                        <div class="mt-1 text-xs text-slate-500">Only rows checked in current run. Empty <span class="font-semibold">word_audio_mp3</span> is not included here.</div>

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-slate-500">
                                        <th class="pb-2 pr-3">ID</th>
                                        <th class="pb-2 pr-3">url_display</th>
                                        <th class="pb-2 pr-3">word_en</th>
                                        <th class="pb-2 pr-3">word_ru</th>
                                        <th class="pb-2 pr-3">word_audio_mp3</th>
                                        <th class="pb-2 pr-3">resolved URL</th>
                                        <th class="pb-2 pr-3">issue</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse (($wordsReport['missingOnServerRows'] ?? collect()) as $row)
                                        <tr>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->id ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->url_display ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-800">{{ $row->word_en ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-700">{{ $row->word_ru ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->word_audio_mp3 ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">
                                                @if (!empty($row->check_url))
                                                    <a href="{{ $row->check_url }}" target="_blank" rel="noreferrer" class="inline-block max-w-[480px] truncate text-sky-600 hover:underline" title="{{ $row->check_url }}">
                                                        {{ $row->check_url }}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="py-2 pr-3">
                                                <span class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-2 py-1 text-xs font-semibold text-red-700">{{ $row->issue ?? 'missing_on_server' }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-3 text-slate-500">No missing words audio files detected in current checked batch.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                        <div class="text-sm font-semibold text-emerald-900">Phrases/Questions audio integrity (word_audio)</div>
                        <div class="mt-1 text-xs text-emerald-800">
                            Expected base: <span class="font-semibold">https://www.language.onllyons.com/ru/ru-en/packs/assest/flashcard-questions-and-sentences/audio/audio-en-ru/</span>
                        </div>

                        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="rounded-xl border border-emerald-200 bg-white p-3">
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-emerald-600">Total rows</div>
                                <div class="mt-1 text-xl font-semibold text-emerald-900">{{ number_format((int) ($phrasesReport['totalRows'] ?? 0)) }}</div>
                            </div>
                            <div class="rounded-xl border border-emerald-200 bg-white p-3">
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-emerald-600">Missing word_audio</div>
                                <div class="mt-1 text-xl font-semibold text-emerald-900">{{ number_format((int) ($phrasesReport['missingPathCount'] ?? 0)) }}</div>
                            </div>
                            <div class="rounded-xl border border-emerald-200 bg-white p-3">
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-emerald-600">Checked rows (HTTP)</div>
                                <div class="mt-1 text-xl font-semibold text-emerald-900">{{ number_format((int) ($phrasesReport['checkedRowsCount'] ?? 0)) }}</div>
                                <div class="mt-1 text-[11px] text-emerald-700">
                                    IDs: {{ $phrasesReport['checkedMinId'] ?? '-' }} - {{ $phrasesReport['checkedMaxId'] ?? '-' }}
                                </div>
                            </div>
                            <div class="rounded-xl border border-red-200 bg-red-50 p-3">
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-red-500">Missing on server</div>
                                <div class="mt-1 text-xl font-semibold text-red-700">{{ number_format((int) ($phrasesReport['missingOnServerCount'] ?? 0)) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-semibold text-slate-700">Phrases/Questions rows missing on server</div>
                        <div class="mt-1 text-xs text-slate-500">Only rows checked in current run. Empty <span class="font-semibold">word_audio</span> is not included here.</div>

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-slate-500">
                                        <th class="pb-2 pr-3">ID</th>
                                        <th class="pb-2 pr-3">url_display</th>
                                        <th class="pb-2 pr-3">word_en</th>
                                        <th class="pb-2 pr-3">word_ru</th>
                                        <th class="pb-2 pr-3">word_audio</th>
                                        <th class="pb-2 pr-3">resolved URL</th>
                                        <th class="pb-2 pr-3">issue</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse (($phrasesReport['missingOnServerRows'] ?? collect()) as $row)
                                        <tr>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->id ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->url_display ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-800">{{ $row->word_en ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-700">{{ $row->word_ru ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->word_audio ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">
                                                @if (!empty($row->check_url))
                                                    <a href="{{ $row->check_url }}" target="_blank" rel="noreferrer" class="inline-block max-w-[480px] truncate text-sky-600 hover:underline" title="{{ $row->check_url }}">
                                                        {{ $row->check_url }}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="py-2 pr-3">
                                                <span class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-2 py-1 text-xs font-semibold text-red-700">{{ $row->issue ?? 'missing_on_server' }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-3 text-slate-500">No missing phrases/questions audio files detected in current checked batch.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
    </body>
</html>
