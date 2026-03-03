<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Flash-cards V2 Category" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Flash-cards V2 Category" />

                <main class="p-4 md:p-6">
                    @php
                        $module = $detail['module'] ?? null;
                        $moduleIsActive = (int) ($module->is_active ?? 0) === 1;
                    @endphp

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold">{{ $module->title ?? 'Category' }}</h1>
                                <p class="mt-2 text-sm text-slate-600">Toate lecțiile și toate cuvintele/itemii din această categorie.</p>
                                <div class="mt-2 text-xs text-slate-500">
                                    ID: {{ $module->id ?? '-' }} · Slug: {{ $module->slug ?? '-' }} · Sort: {{ $module->sort_order ?? '-' }}
                                </div>
                                @if (trim((string) ($module->description ?? '')) !== '')
                                    <div class="mt-2 text-sm text-slate-600">{{ $module->description }}</div>
                                @endif
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span class="{{ $moduleIsActive ? 'rounded-full border border-green-200 bg-green-50 px-3 py-1 text-xs font-semibold text-green-700' : 'rounded-full border border-red-200 bg-red-50 px-3 py-1 text-xs font-semibold text-red-700' }}">
                                    is_active: {{ $module->is_active ?? 0 }}
                                </span>
                                <a href="{{ route('flash-cards.v2.module.plain', ['moduleId' => $module->id]) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fa-solid fa-list"></i>
                                    Full list (plain)
                                </a>
                                <a href="{{ route('flash-cards.v2') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fa-solid fa-arrow-left"></i>
                                    Back to categories
                                </a>
                            </div>
                        </div>
                    </div>

                    @if (!empty($error))
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ $error }}
                        </div>
                    @endif

                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Lessons</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((int) (($detail['summary']['lessons'] ?? 0))) }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Active lessons</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((int) (($detail['summary']['active_lessons'] ?? 0))) }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Items</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((int) (($detail['summary']['items'] ?? 0))) }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Items with audio</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((int) (($detail['summary']['items_with_audio'] ?? 0))) }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Duplicate rows</div>
                            <div class="mt-2 text-xl font-semibold {{ (int) (($detail['summary']['duplicate_rows'] ?? 0)) > 0 ? 'text-red-700' : 'text-emerald-700' }}">
                                {{ number_format((int) (($detail['summary']['duplicate_rows'] ?? 0))) }}
                            </div>
                            <div class="mt-1 text-xs text-slate-500">Groups: {{ number_format((int) (($detail['summary']['duplicate_groups'] ?? 0))) }}</div>
                        </div>
                        <div class="rounded-2xl border border-amber-300 bg-amber-50 p-4">
                            <div class="text-xs font-semibold text-amber-700">Duplicates across lessons</div>
                            <div class="mt-2 text-xl font-semibold text-amber-700">
                                {{ number_format((int) (($detail['summary']['reused_groups'] ?? 0))) }}
                            </div>
                            <div class="mt-1 text-xs text-amber-700">Cross-lesson duplicates; review manually.</div>
                        </div>
                        <div class="rounded-2xl border {{ (int) (($detail['summary']['duplicate_rows'] ?? 0)) > 0 ? 'border-red-300 bg-red-50' : 'border-emerald-300 bg-emerald-50' }} p-4">
                            <div class="text-xs font-semibold {{ (int) (($detail['summary']['duplicate_rows'] ?? 0)) > 0 ? 'text-red-700' : 'text-emerald-700' }}">
                                Duplicate status
                            </div>
                            <div class="mt-2 text-sm font-semibold {{ (int) (($detail['summary']['duplicate_rows'] ?? 0)) > 0 ? 'text-red-700' : 'text-emerald-700' }}">
                                {{ (int) (($detail['summary']['duplicate_rows'] ?? 0)) > 0 ? 'Attention: duplicates found in same lesson' : 'OK: no duplicates in same lesson' }}
                            </div>
                        </div>
                    </div>

                    @if ((int) (($detail['summary']['duplicate_groups'] ?? 0)) > 0)
                        <section class="mt-6 rounded-2xl border border-red-300 bg-red-50 p-5 shadow-sm">
                            <h2 class="text-lg font-semibold text-red-700">Duplicate rows details</h2>
                            <p class="mt-1 text-sm text-red-700">
                                Duplicate reale în aceeași lecție: combinația <code>lesson_id + type + text_from + text_to</code>.
                            </p>
                            <p class="mt-1 text-xs text-red-700">
                                Notă: type este normalizat inteligent (<code>question/questions</code>, <code>phrase/phrases</code>, <code>word/words</code>).
                            </p>

                            <div class="mt-4 overflow-x-auto rounded-xl border border-red-200 bg-white">
                                <table class="min-w-full divide-y divide-red-100 text-sm">
                                    <thead class="bg-red-50 text-xs uppercase tracking-wide text-red-700">
                                        <tr>
                                            <th class="px-3 py-2 text-left">Lesson</th>
                                            <th class="px-3 py-2 text-left">Type (norm)</th>
                                            <th class="px-3 py-2 text-left">Type (raw)</th>
                                            <th class="px-3 py-2 text-left">Text from</th>
                                            <th class="px-3 py-2 text-left">Text to</th>
                                            <th class="px-3 py-2 text-left">Count</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-red-100">
                                        @foreach (($detail['duplicateGroups'] ?? collect())->take(30) as $dup)
                                            <tr class="align-top">
                                                <td class="px-3 py-2 text-red-700">
                                                    {{ trim((string) ($dup->lesson_title ?? '')) !== '' ? $dup->lesson_title : 'Lesson' }} ({{ $dup->lesson_id ?? '-' }})
                                                </td>
                                                <td class="px-3 py-2 text-red-700">{{ $dup->type !== '' ? $dup->type : '-' }}</td>
                                                <td class="px-3 py-2 text-red-700">{{ ($dup->raw_types ?? collect())->isNotEmpty() ? ($dup->raw_types->implode(', ')) : '-' }}</td>
                                                <td class="px-3 py-2 text-red-700">{{ $dup->text_from !== '' ? $dup->text_from : '-' }}</td>
                                                <td class="px-3 py-2 text-red-700">{{ $dup->text_to !== '' ? $dup->text_to : '-' }}</td>
                                                <td class="px-3 py-2 font-semibold text-red-700">{{ number_format((int) ($dup->count ?? 0)) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if (($detail['duplicateGroups'] ?? collect())->count() > 30)
                                <div class="mt-3 text-xs text-red-700">
                                    Showing first 30 duplicate groups from {{ number_format((int) (($detail['duplicateGroups'] ?? collect())->count())) }}.
                                </div>
                            @endif
                        </section>
                    @endif

                    @if ((int) (($detail['summary']['reused_groups'] ?? 0)) > 0)
                        <section class="mt-6 rounded-2xl border border-amber-300 bg-amber-50 p-5 shadow-sm">
                            <h2 class="text-lg font-semibold text-amber-700">Duplicates across lessons (informational)</h2>
                            <p class="mt-1 text-sm text-amber-700">
                                Aceste rânduri apar în lecții diferite cu aceeași cheie normalizată <code>type + text_from + text_to</code>. De obicei este normal și nu este tratat ca eroare.
                            </p>

                            <div class="mt-4 overflow-x-auto rounded-xl border border-amber-200 bg-white">
                                <table class="min-w-full divide-y divide-amber-100 text-sm">
                                    <thead class="bg-amber-50 text-xs uppercase tracking-wide text-amber-700">
                                        <tr>
                                            <th class="px-3 py-2 text-left">Type (norm)</th>
                                            <th class="px-3 py-2 text-left">Type (raw)</th>
                                            <th class="px-3 py-2 text-left">Text from</th>
                                            <th class="px-3 py-2 text-left">Text to</th>
                                            <th class="px-3 py-2 text-left">Count</th>
                                            <th class="px-3 py-2 text-left">Lesson IDs</th>
                                            <th class="px-3 py-2 text-left">Lesson titles</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-amber-100">
                                        @foreach (($detail['reusedGroups'] ?? collect())->take(30) as $row)
                                            <tr class="align-top">
                                                <td class="px-3 py-2 text-amber-700">{{ $row->type !== '' ? $row->type : '-' }}</td>
                                                <td class="px-3 py-2 text-amber-700">{{ ($row->raw_types ?? collect())->isNotEmpty() ? ($row->raw_types->implode(', ')) : '-' }}</td>
                                                <td class="px-3 py-2 text-amber-700">{{ $row->text_from !== '' ? $row->text_from : '-' }}</td>
                                                <td class="px-3 py-2 text-amber-700">{{ $row->text_to !== '' ? $row->text_to : '-' }}</td>
                                                <td class="px-3 py-2 font-semibold text-amber-700">{{ number_format((int) ($row->count ?? 0)) }}</td>
                                                <td class="px-3 py-2 text-amber-700">{{ ($row->lesson_ids ?? collect())->implode(', ') }}</td>
                                                <td class="px-3 py-2 text-amber-700">{{ trim((string) ($row->lesson_titles ?? '')) !== '' ? trim((string) $row->lesson_titles) : '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if (($detail['reusedGroups'] ?? collect())->count() > 30)
                                <div class="mt-3 text-xs text-amber-700">
                                    Showing first 30 duplicate groups from {{ number_format((int) (($detail['reusedGroups'] ?? collect())->count())) }}.
                                </div>
                            @endif
                        </section>
                    @endif

                    <div class="mt-6 space-y-4">
                        @forelse (($detail['lessons'] ?? collect()) as $lesson)
                            @php
                                $lessonIsActive = (int) ($lesson->is_active ?? 0) === 1;
                                $lessonDetailUrl = route('flash-cards.v2.lesson', ['lessonId' => $lesson->id]);
                                $itemsCount = (int) ($lesson->items_count ?? 0);
                                $itemsWithAudio = (int) ($lesson->items_with_audio ?? 0);
                                $hasItems = $itemsCount > 0;
                            @endphp
                            <section class="{{ $hasItems ? 'rounded-2xl border border-slate-200 bg-white p-5 shadow-sm' : 'rounded-2xl border border-red-300 bg-red-50 p-5 shadow-sm' }}">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <a href="{{ $lessonDetailUrl }}" class="text-base font-semibold text-slate-800 hover:text-sky-700 hover:underline">
                                            {{ $lesson->title ?? 'Untitled lesson' }} (ID {{ $lesson->id ?? '-' }})
                                        </a>
                                        <div class="mt-1 break-all text-xs text-slate-500">{{ $lesson->url ?? '-' }}</div>
                                        <div class="mt-1 text-xs text-slate-500">
                                            Type: {{ $lesson->lesson_type ?? '-' }} · Level: {{ $lesson->level ?? '-' }} · Sort: {{ $lesson->sort_order ?? '-' }}
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-2 text-xs">
                                        <span class="{{ $lessonIsActive ? 'rounded-full border border-green-200 bg-green-50 px-3 py-1 font-semibold text-green-700' : 'rounded-full border border-red-200 bg-red-50 px-3 py-1 font-semibold text-red-700' }}">
                                            is_active: {{ $lesson->is_active ?? 0 }}
                                        </span>
                                        <span class="{{ $hasItems ? 'rounded-full border border-slate-200 bg-slate-50 px-3 py-1 font-semibold text-slate-700' : 'rounded-full border border-red-300 bg-red-100 px-3 py-1 font-semibold text-red-700' }}">
                                            Items: {{ number_format($itemsCount) }}
                                        </span>
                                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 font-semibold text-slate-700">Audio: {{ number_format($itemsWithAudio) }}</span>
                                        @if (!$hasItems)
                                            <span class="rounded-full border border-red-300 bg-red-100 px-3 py-1 font-semibold text-red-700">
                                                Missing items
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-4 grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                                    @forelse (($lesson->items ?? collect()) as $item)
                                        <article class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                            <div class="font-semibold text-slate-800">
                                                {{ trim((string) ($item->text_from ?? '')) !== '' ? $item->text_from : '-' }}
                                                <span class="text-slate-500">-></span>
                                                {{ trim((string) ($item->text_to ?? '')) !== '' ? $item->text_to : '-' }}
                                            </div>
                                            <div class="mt-1 text-xs text-slate-500">
                                                ID {{ $item->id ?? '-' }} · Type: {{ trim((string) ($item->type ?? '')) !== '' ? $item->type : '-' }}
                                            </div>
                                            @if (trim((string) ($item->ipa ?? '')) !== '')
                                                <div class="mt-1 text-xs text-slate-500">IPA: {{ $item->ipa }}</div>
                                            @endif
                                            @if (trim((string) ($item->audio ?? '')) !== '')
                                                <div class="mt-1 break-all text-xs text-sky-700">{{ $item->audio }}</div>
                                            @endif
                                        </article>
                                    @empty
                                        <div class="rounded-lg border border-red-300 bg-red-100 px-3 py-2 text-sm font-semibold text-red-700">
                                            No items for this lesson.
                                        </div>
                                    @endforelse
                                </div>
                            </section>
                        @empty
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 text-sm text-slate-500 shadow-sm">
                                No lessons found for this category.
                            </div>
                        @endforelse
                    </div>
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
    </body>
</html>
