<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="User course history" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="User course history" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold">Course history</h1>
                                <p class="mt-2 text-sm text-slate-600">
                                    Date din <span class="font-semibold text-slate-700">course_history</span> pentru user
                                    <span class="font-semibold text-slate-700">#{{ $user->id }}</span>
                                    ({{ $user->username ?? $user->name ?? 'User' }}).
                                </p>
                            </div>
                            <a class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:border-slate-300" href="{{ url('/users/' . $user->id) }}">Back to profile</a>
                        </div>
                    </div>

                    @if ($error)
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ $error }}
                        </div>
                    @endif

                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Rows</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['total_rows'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Unique courses</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['unique_courses'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Started</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['started_rows'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Completed</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['completed_rows'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Total time_study</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['total_time_label'] ?? '0s' }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Series answers</div>
                            <div class="mt-2 text-sm font-semibold text-slate-700">
                                {{ $summary['series_correct_answers'] ?? 0 }} correct /
                                {{ $summary['series_wrong_answers'] ?? 0 }} wrong
                            </div>
                            <div class="mt-1 text-xs text-slate-500">{{ $summary['series_entries'] ?? 0 }} entries</div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="text-sm font-semibold text-slate-700">course_history rows</div>
                            <div class="text-xs text-slate-500">SELECT id, user_id, course_id, slides_study, quizzes_study, series_data, time_study, start_time, end_time</div>
                        </div>

                        <div class="mt-3 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-slate-500">
                                        <th class="pb-2">id</th>
                                        <th class="pb-2">user_id</th>
                                        <th class="pb-2">course_id</th>
                                        <th class="pb-2">slides_study</th>
                                        <th class="pb-2">quizzes_study</th>
                                        <th class="pb-2">time_study</th>
                                        <th class="pb-2">start_time</th>
                                        <th class="pb-2">end_time</th>
                                        <th class="pb-2">series_data</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($rows as $row)
                                        <tr>
                                            <td class="py-2 align-top text-slate-700">{{ $row['id'] }}</td>
                                            <td class="py-2 align-top text-slate-700">{{ $row['user_id'] }}</td>
                                            <td class="py-2 align-top text-slate-700">{{ $row['course_id'] }}</td>
                                            <td class="py-2 align-top text-slate-700">{{ $row['slides_study'] }}</td>
                                            <td class="py-2 align-top text-slate-700">{{ $row['quizzes_study'] }}</td>
                                            <td class="py-2 align-top text-slate-700">
                                                <div>{{ $row['time_study_seconds'] }}</div>
                                                <div class="text-xs text-slate-500">{{ $row['time_study_label'] }}</div>
                                            </td>
                                            <td class="py-2 align-top text-slate-700">
                                                <div>{{ $row['start_time'] }}</div>
                                                <div class="text-xs text-slate-500">{{ $row['start_time_label'] }}</div>
                                            </td>
                                            <td class="py-2 align-top text-slate-700">
                                                <div>{{ $row['end_time'] }}</div>
                                                <div class="text-xs text-slate-500">{{ $row['end_time_label'] }}</div>
                                            </td>
                                            <td class="py-2 align-top text-slate-700">
                                                @if ($row['series_error'])
                                                    <div class="rounded-lg border border-red-200 bg-red-50 px-2 py-1 text-xs text-red-700">
                                                        {{ $row['series_error'] }}
                                                    </div>
                                                    @if ($row['series_data_raw'] !== '')
                                                        <div class="mt-2 max-w-md break-all text-xs text-slate-500">{{ $row['series_data_raw'] }}</div>
                                                    @endif
                                                @elseif ($row['series_count'] > 0)
                                                    <details class="group rounded-lg border border-slate-200 bg-slate-50 p-2">
                                                        <summary class="cursor-pointer text-xs font-semibold text-slate-700">
                                                            {{ $row['series_count'] }} series
                                                        </summary>
                                                        <div class="mt-2 overflow-x-auto">
                                                            <table class="min-w-[520px] text-xs">
                                                                <thead>
                                                                    <tr class="text-left text-slate-500">
                                                                        <th class="pb-1 pr-3">Series</th>
                                                                        <th class="pb-1 pr-3">time_carousel</th>
                                                                        <th class="pb-1 pr-3">score_carousel</th>
                                                                        <th class="pb-1 pr-3">time_quiz</th>
                                                                        <th class="pb-1 pr-3">correctAnswers</th>
                                                                        <th class="pb-1 pr-3">wrongAnswers</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="divide-y divide-slate-200">
                                                                    @foreach ($row['series_items'] as $seriesItem)
                                                                        <tr>
                                                                            <td class="py-1 pr-3 font-semibold text-slate-700">{{ $seriesItem['series'] }}</td>
                                                                            <td class="py-1 pr-3 text-slate-700">
                                                                                {{ $seriesItem['time_carousel'] }}
                                                                                <span class="text-slate-500">({{ $seriesItem['time_carousel_label'] }})</span>
                                                                            </td>
                                                                            <td class="py-1 pr-3 text-slate-700">{{ $seriesItem['score_carousel'] }}</td>
                                                                            <td class="py-1 pr-3 text-slate-700">
                                                                                {{ $seriesItem['time_quiz'] }}
                                                                                <span class="text-slate-500">({{ $seriesItem['time_quiz_label'] }})</span>
                                                                            </td>
                                                                            <td class="py-1 pr-3 text-emerald-700">{{ $seriesItem['correct_answers'] }}</td>
                                                                            <td class="py-1 pr-3 text-rose-700">{{ $seriesItem['wrong_answers'] }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <div class="mt-2 max-w-md break-all text-[11px] text-slate-500">
                                                            Raw JSON: {{ $row['series_data_raw'] }}
                                                        </div>
                                                    </details>
                                                @else
                                                    <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs text-slate-500">No series data</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="py-3 text-slate-500" colspan="9">Nu exista inregistrari in course_history pentru acest user.</td>
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
