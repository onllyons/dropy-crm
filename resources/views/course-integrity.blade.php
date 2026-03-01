<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Course Integrity Debut" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Course Integrity Debut" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold">Course Integrity Debut</h1>
                                <p class="mt-2 text-sm text-slate-600">Orphan checks: course/category, carousel/course, tests/course, duplicate lesson URLs.</p>
                            </div>
                            <a class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="{{ url('/course') }}">
                                Back to course
                                <i class="fa-solid fa-arrow-right text-slate-400"></i>
                            </a>
                        </div>
                    </div>

                    @if (!empty($error))
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ $error }}
                        </div>
                    @endif

                    <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
                        <div class="text-sm font-semibold text-rose-800">0) Media file_path debut (PRIORITY)</div>
                        <div class="mt-1 text-xs text-rose-700">
                            video expected: <span class="font-semibold">.../course/video-lessons/...</span> ·
                            audio expected: <span class="font-semibold">.../course/audio-lessons/...</span>
                        </div>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="rounded-xl border border-rose-200 bg-white p-3">
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-rose-500">Video rows (variant=v)</div>
                                <div class="mt-1 text-xl font-semibold text-rose-800">{{ number_format((int) ($videoRowsCount ?? 0)) }}</div>
                            </div>
                            <div class="rounded-xl border border-rose-200 bg-white p-3">
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-rose-500">Audio rows (variant=a)</div>
                                <div class="mt-1 text-xl font-semibold text-rose-800">{{ number_format((int) ($audioRowsCount ?? 0)) }}</div>
                            </div>
                            <div class="rounded-xl border border-rose-200 bg-white p-3">
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-rose-500">Missing file_path (v+a)</div>
                                <div class="mt-1 text-xl font-semibold text-rose-800">{{ number_format((int) (($videoMissingPathCount ?? 0) + ($audioMissingPathCount ?? 0))) }}</div>
                                <div class="mt-1 text-[11px] text-rose-600">v: {{ (int) ($videoMissingPathCount ?? 0) }} · a: {{ (int) ($audioMissingPathCount ?? 0) }}</div>
                            </div>
                            <div class="rounded-xl border border-rose-200 bg-white p-3">
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-rose-500">Invalid path format (v+a)</div>
                                <div class="mt-1 text-xl font-semibold text-rose-800">{{ number_format((int) (($videoInvalidPathCount ?? 0) + ($audioInvalidPathCount ?? 0))) }}</div>
                                <div class="mt-1 text-[11px] text-rose-600">v: {{ (int) ($videoInvalidPathCount ?? 0) }} · a: {{ (int) ($audioInvalidPathCount ?? 0) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-semibold text-slate-700">0.1) course_carousel media problems list</div>
                        <div class="mt-1 text-xs text-slate-500">Rows with missing or invalid <span class="font-semibold">file_path</span>. Total: {{ number_format((int) ($mediaPathProblemsCount ?? 0)) }}</div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-slate-500">
                                        <th class="pb-2 pr-3">ID</th>
                                        <th class="pb-2 pr-3">variant</th>
                                        <th class="pb-2 pr-3">issue</th>
                                        <th class="pb-2 pr-3">course_url</th>
                                        <th class="pb-2 pr-3">series</th>
                                        <th class="pb-2 pr-3">file_path</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse (($mediaPathProblems ?? collect()) as $row)
                                        <tr>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->id ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->variant ?? '-' }}</td>
                                            <td class="py-2 pr-3">
                                                @php
                                                    $issue = (string) ($row->issue ?? '');
                                                @endphp
                                                @if ($issue === 'missing_file_path')
                                                    <span class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-2 py-1 text-xs font-semibold text-red-700">missing_file_path</span>
                                                @elseif ($issue === 'invalid_video_path')
                                                    <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">invalid_video_path</span>
                                                @elseif ($issue === 'invalid_audio_path')
                                                    <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">invalid_audio_path</span>
                                                @else
                                                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-600">{{ $issue !== '' ? $issue : '-' }}</span>
                                                @endif
                                            </td>
                                            <td class="py-2 pr-3 text-slate-700">{{ $row->course_url !== null && $row->course_url !== '' ? $row->course_url : '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->series ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">
                                                @if (!empty($row->file_path))
                                                    <span class="inline-block max-w-[420px] truncate" title="{{ $row->file_path }}">{{ $row->file_path }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-3 text-slate-500">No media path problems found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Lessons without category</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((int) ($lessonsWithoutCategoryCount ?? 0)) }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Carousel without lesson</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((int) ($carouselWithoutLessonCount ?? 0)) }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Tests without lesson</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((int) ($testsWithoutLessonCount ?? 0)) }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Duplicate lesson URLs</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((int) ($duplicateLessonUrlsCount ?? 0)) }}</div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-semibold text-slate-700">1) Lecții fără categorie (course fără category_course)</div>
                        <div class="mt-1 text-xs text-slate-500">A) listă · B) număr: {{ number_format((int) ($lessonsWithoutCategoryCount ?? 0)) }}</div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-slate-500">
                                        <th class="pb-2 pr-3">ID</th>
                                        <th class="pb-2 pr-3">category_url</th>
                                        <th class="pb-2 pr-3">url</th>
                                        <th class="pb-2 pr-3">title</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse (($lessonsWithoutCategory ?? collect()) as $row)
                                        <tr>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->id ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->category_url !== null && $row->category_url !== '' ? $row->category_url : '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->url ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-700">{{ $row->title ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-3 text-slate-500">No orphan lessons found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-semibold text-slate-700">3) Carousel fără lecție (course_carousel fără course)</div>
                        <div class="mt-1 text-xs text-slate-500">A) listă · B) număr: {{ number_format((int) ($carouselWithoutLessonCount ?? 0)) }}</div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-slate-500">
                                        <th class="pb-2 pr-3">ID</th>
                                        <th class="pb-2 pr-3">course_url</th>
                                        <th class="pb-2 pr-3">series</th>
                                        <th class="pb-2 pr-3">base_title</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse (($carouselWithoutLesson ?? collect()) as $row)
                                        <tr>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->id ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->course_url !== null && $row->course_url !== '' ? $row->course_url : '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->series ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-700">{{ $row->base_title ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-3 text-slate-500">No orphan carousel rows found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-semibold text-slate-700">4) Quiz/Test fără lecție (course_test fără course)</div>
                        <div class="mt-1 text-xs text-slate-500">A) listă · B) număr: {{ number_format((int) ($testsWithoutLessonCount ?? 0)) }}</div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-slate-500">
                                        <th class="pb-2 pr-3">ID</th>
                                        <th class="pb-2 pr-3">course_url</th>
                                        <th class="pb-2 pr-3">series</th>
                                        <th class="pb-2 pr-3">v1</th>
                                        <th class="pb-2 pr-3">correct</th>
                                        <th class="pb-2 pr-3">variant</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse (($testsWithoutLesson ?? collect()) as $row)
                                        <tr>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->id ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->course_url !== null && $row->course_url !== '' ? $row->course_url : '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->series ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-700">{{ $row->v1 ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->correct ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->variant ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-3 text-slate-500">No orphan test rows found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-semibold text-slate-700">5.2) URL-uri de lecții duplicate (course.url)</div>
                        <div class="mt-1 text-xs text-slate-500">Număr: {{ number_format((int) ($duplicateLessonUrlsCount ?? 0)) }}</div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-slate-500">
                                        <th class="pb-2 pr-3">url</th>
                                        <th class="pb-2 pr-3">cnt</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse (($duplicateLessonUrls ?? collect()) as $row)
                                        <tr>
                                            <td class="py-2 pr-3 text-slate-700">{{ $row->url ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->cnt ?? 0 }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="py-3 text-slate-500">No duplicate lesson URLs found.</td>
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
