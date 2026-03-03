<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Flash Card Words" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Flash Card Words" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold">flash-card-words</h1>
                                <p class="mt-2 text-sm text-slate-600">Listă manuală: numele lecției + toate cuvintele din lecție.</p>
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

                    <div class="mt-6 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Categorii</div>
                            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format((int) (($stats['categories'] ?? 0))) }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Lecții</div>
                            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format((int) (($stats['lessons'] ?? 0))) }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cuvinte</div>
                            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format((int) (($stats['words'] ?? 0))) }}</div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-800">Categorie + Lecții</h2>
                        <p class="mt-1 text-xs text-slate-500">Rezumat rapid pentru verificare manuală.</p>

                        <div class="mt-4 space-y-4">
                            @forelse (($outline ?? collect()) as $group)
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-sm font-semibold text-slate-800">{{ $group->group_title }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $group->group_code }}</div>

                                    @if (($group->lessons ?? collect())->isNotEmpty())
                                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-slate-700">
                                            @foreach ($group->lessons as $lessonTitle)
                                                <li>{{ $lessonTitle }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <div class="mt-2 text-sm text-slate-500">No lessons in this category.</div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-sm text-slate-500">No categories found.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse (($lessons ?? collect()) as $lesson)
                            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                @php
                                    $levelValue = (int) ($lesson->category ?? 0);
                                    $levelMap = [
                                        1 => 'Elementary',
                                        2 => 'Intermediate',
                                        3 => 'Hard',
                                    ];
                                    $levelLabel = $levelMap[$levelValue] ?? ('Unknown (' . $levelValue . ')');
                                    $groupTitle = trim((string) ($lesson->group_title ?? ''));
                                    $groupCode = trim((string) ($lesson->group_category ?? ''));
                                    $groupId = is_numeric($lesson->group_category_id ?? null) ? (int) $lesson->group_category_id : null;
                                    if ($groupTitle === '') {
                                        $groupTitle = $groupCode !== '' ? $groupCode : 'Uncategorized';
                                    }
                                    $categoryLabel = $groupTitle;
                                    if ($groupCode !== '' && $groupCode !== $groupTitle) {
                                        $categoryLabel .= ' (' . $groupCode . ')';
                                    }

                                    $copyLines = [];
                                    $copyLines[] = 'Lesson: ' . ((string) ($lesson->title ?? '-') !== '' ? (string) ($lesson->title ?? '-') : '-');
                                    $copyLines[] = 'URL: ' . ((string) ($lesson->url ?? '-') !== '' ? (string) ($lesson->url ?? '-') : '-');
                                    $copyLines[] = 'Category: ' . $categoryLabel;
                                    $copyLines[] = 'Level: ' . $levelLabel;
                                    $copyLines[] = 'Words:';

                                    if (!empty($lesson->words_count)) {
                                        foreach (($lesson->words ?? collect()) as $word) {
                                            $en = trim((string) ($word->word_en ?? ''));
                                            $ru = trim((string) ($word->word_ru ?? ''));
                                            $copyLines[] = ($en !== '' ? $en : '-') . ' - ' . ($ru !== '' ? $ru : '-');
                                        }
                                    } else {
                                        $copyLines[] = 'No words';
                                    }

                                    $copyPayload = implode("\n", $copyLines);
                                @endphp

                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1 space-y-1">
                                        <div class="text-sm font-semibold text-blue-700">
                                            Category{{ $groupId !== null ? ' #' . $groupId : '' }}: {{ $categoryLabel }} - {{ $lesson->title ?? '-' }} (ID {{ $lesson->id ?? '-' }})
                                        </div>
                                        <div class="text-xs font-semibold text-slate-700">
                                            Level {{ $levelValue }}: {{ $levelLabel }}
                                        </div>
                                        <div class="text-xs text-slate-600 break-all">
                                            {{ $lesson->url ?? '-' }}
                                        </div>
                                        <div class="text-xs font-semibold text-slate-700">
                                            Words: {{ number_format((int) ($lesson->words_count ?? 0)) }}
                                        </div>
                                    </div>
                                    <button type="button" class="js-copy-lesson inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                        <i class="fa-solid fa-copy"></i>
                                        Copy
                                    </button>
                                </div>
                                <textarea class="js-copy-text hidden" readonly>{{ $copyPayload }}</textarea>

                                <div class="mt-3">
                                    @if (!empty($lesson->words_count))
                                        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                                            @foreach (($lesson->words ?? collect()) as $word)
                                                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                                    <span class="font-semibold text-slate-800">{{ $word->word_en !== '' ? $word->word_en : '-' }}</span>
                                                    <span class="text-slate-500"> - {{ $word->word_ru !== '' ? $word->word_ru : '-' }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-sm text-slate-500">No words for this lesson.</div>
                                    @endif
                                </div>
                            </section>
                        @empty
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 text-sm text-slate-500 shadow-sm">
                                No lessons found.
                            </div>
                        @endforelse
                    </div>
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
        <script>
            (function () {
                function fallbackCopyText(text) {
                    var textarea = document.createElement('textarea');
                    textarea.value = text;
                    document.body.appendChild(textarea);
                    textarea.focus();
                    textarea.select();
                    try {
                        return document.execCommand('copy');
                    } catch (e) {
                        return false;
                    } finally {
                        document.body.removeChild(textarea);
                    }
                }

                function copyText(text) {
                    if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                        return navigator.clipboard.writeText(text).then(function () {
                            return true;
                        }).catch(function () {
                            return fallbackCopyText(text);
                        });
                    }

                    return Promise.resolve(fallbackCopyText(text));
                }

                var buttons = document.querySelectorAll('.js-copy-lesson');
                for (var i = 0; i < buttons.length; i++) {
                    buttons[i].addEventListener('click', function (event) {
                        var button = event.currentTarget;
                        var section = button.closest('section');
                        if (!section) {
                            return;
                        }

                        var source = section.querySelector('.js-copy-text');
                        if (!source) {
                            return;
                        }

                        var payload = source.value || '';
                        if (payload.trim() === '') {
                            return;
                        }

                        copyText(payload).then(function (ok) {
                            var original = button.innerHTML;
                            if (ok) {
                                button.innerHTML = '<i class=\"fa-solid fa-check\"></i> Copied';
                            } else {
                                button.innerHTML = '<i class=\"fa-solid fa-xmark\"></i> Failed';
                            }

                            setTimeout(function () {
                                button.innerHTML = original;
                            }, 1200);
                        });
                    });
                }
            })();
        </script>
    </body>
</html>
