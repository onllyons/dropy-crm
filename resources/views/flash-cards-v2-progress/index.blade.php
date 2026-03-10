<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Flash-cards V2 Progress" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Flash-cards V2 Progress" />

                <main class="p-4 md:p-6">
                    @php
                        $filters = $progress['filters'] ?? ['q' => '', 'status' => 'all'];
                        $summary = $progress['summary'] ?? [];
                        $users = $progress['users'] ?? collect();
                        $attempts = $progress['attempts'] ?? collect();
                        $focusedUser = $progress['focusedUser'] ?? null;
                        $focusedUserProgress = $progress['focusedUserProgress'] ?? null;
                        $focusedSummary = $focusedUserProgress['summary'] ?? [];
                        $focusedModuleProgress = $focusedUserProgress['moduleProgress'] ?? collect();
                        $userRows = method_exists($users, 'items') ? $users->items() : $users;
                        $attemptRows = method_exists($attempts, 'items') ? $attempts->items() : $attempts;
                        $formatDuration = function ($seconds) {
                            $total = max(0, (int) $seconds);
                            $hours = intdiv($total, 3600);
                            $minutes = intdiv($total % 3600, 60);
                            $rest = $total % 60;

                            if ($hours > 0) {
                                return sprintf('%dh %02dm', $hours, $minutes);
                            }

                            if ($minutes > 0) {
                                return sprintf('%dm %02ds', $minutes, $rest);
                            }

                            return sprintf('%ds', $rest);
                        };
                    @endphp

                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h1 class="text-2xl font-semibold text-slate-900">Flash-cards V2 User Progress</h1>
                                <p class="mt-2 max-w-3xl text-sm text-slate-600">
                                    Progress read directly from <code>flashcard_lesson_attempts</code>. Summary sus, utilizatori grupați la mijloc și încercări recente jos.
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('flash-cards.v2') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fa-solid fa-arrow-left"></i>
                                    Back to Flash-cards V2
                                </a>
                            </div>
                        </div>

                        <form method="get" action="{{ route('flash-cards.v2.progress') }}" class="mt-5 grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 lg:grid-cols-[minmax(0,1fr)_220px_auto]">
                            <label class="block">
                                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">User search</span>
                                <input
                                    type="text"
                                    name="q"
                                    value="{{ $filters['q'] ?? '' }}"
                                    placeholder="user id / username / name"
                                    class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none"
                                />
                            </label>

                            <label class="block">
                                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</span>
                                <select name="status" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none">
                                    @foreach (['all' => 'All', 'completed' => 'Completed', 'in_progress' => 'In progress'] as $value => $label)
                                        <option value="{{ $value }}" @selected(($filters['status'] ?? 'all') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <div class="flex items-end gap-2">
                                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                    Apply
                                </button>
                                <a href="{{ route('flash-cards.v2.progress') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    Reset
                                </a>
                            </div>
                        </form>
                    </section>

                    @if (!empty($error))
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ $error }}
                        </div>
                    @endif

                    <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Users with attempts</div>
                            <div class="mt-2 text-2xl font-semibold text-slate-800">{{ number_format((int) ($summary['users_with_attempts'] ?? 0)) }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Attempts total</div>
                            <div class="mt-2 text-2xl font-semibold text-slate-800">{{ number_format((int) ($summary['attempts_total'] ?? 0)) }}</div>
                        </div>
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Completed attempts</div>
                            <div class="mt-2 text-2xl font-semibold text-emerald-700">{{ number_format((int) ($summary['completed_attempts'] ?? 0)) }}</div>
                        </div>
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-wide text-amber-700">In progress</div>
                            <div class="mt-2 text-2xl font-semibold text-amber-700">{{ number_format((int) ($summary['in_progress_attempts'] ?? 0)) }}</div>
                        </div>
                        <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4 shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-wide text-sky-700">Distinct completed lessons</div>
                            <div class="mt-2 text-2xl font-semibold text-sky-700">{{ number_format((int) ($summary['completed_lessons_distinct'] ?? 0)) }}</div>
                        </div>
                        <div class="rounded-2xl border border-violet-200 bg-violet-50 p-4 shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-wide text-violet-700">Tracked time</div>
                            <div class="mt-2 text-2xl font-semibold text-violet-700">{{ $formatDuration($summary['total_time_seconds'] ?? 0) }}</div>
                            <div class="mt-1 text-xs text-violet-700">Catalog lessons: {{ number_format((int) ($summary['catalog_lessons_total'] ?? 0)) }}</div>
                        </div>
                    </section>

                    @if (is_array($focusedUser) && is_array($focusedUserProgress))
                        <section class="mt-6 rounded-2xl border border-sky-200 bg-white p-5 shadow-sm">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h2 class="text-lg font-semibold text-slate-900">Focused User Progress</h2>
                                    <p class="mt-1 text-sm text-slate-600">
                                        Overview complet pentru
                                        <a href="{{ url('/users/' . (int) ($focusedUser['id'] ?? 0)) }}" class="font-semibold text-sky-700 hover:underline">
                                            {{ trim((string) ($focusedUser['label'] ?? '')) !== '' ? $focusedUser['label'] : ('User #' . (int) ($focusedUser['id'] ?? 0)) }}
                                        </a>
                                        @if (trim((string) ($focusedUser['name'] ?? '')) !== '')
                                            <span class="text-slate-400">·</span> {{ $focusedUser['name'] }}
                                        @endif
                                    </p>
                                </div>
                                <a href="{{ url('/users/' . (int) ($focusedUser['id'] ?? 0)) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fa-solid fa-user"></i>
                                    Open user profile
                                </a>
                            </div>

                            <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Completed lessons</div>
                                    <div class="mt-2 text-xl font-semibold text-slate-800">{{ number_format((int) ($focusedSummary['completed_lessons'] ?? 0)) }}</div>
                                    <div class="mt-1 text-xs text-slate-500">from {{ number_format((int) ($focusedSummary['catalog_lessons_total'] ?? 0)) }}</div>
                                </div>
                                <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-sky-700">Progress</div>
                                    <div class="mt-2 text-xl font-semibold text-sky-700">{{ number_format((float) ($focusedSummary['progress_percent'] ?? 0), 1) }}%</div>
                                    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-white/70">
                                        <div class="h-full rounded-full bg-sky-500" style="width: {{ max(0, min(100, (float) ($focusedSummary['progress_percent'] ?? 0))) }}%;"></div>
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Attempts</div>
                                    <div class="mt-2 text-xl font-semibold text-emerald-700">{{ number_format((int) ($focusedSummary['attempts_total'] ?? 0)) }}</div>
                                    <div class="mt-1 text-xs text-emerald-700">Completed: {{ number_format((int) ($focusedSummary['completed_attempts'] ?? 0)) }}</div>
                                </div>
                                <div class="rounded-2xl border border-violet-200 bg-violet-50 p-4">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-violet-700">Accuracy</div>
                                    <div class="mt-2 text-xl font-semibold text-violet-700">
                                        @if (($focusedSummary['accuracy_percent'] ?? null) !== null)
                                            {{ number_format((float) $focusedSummary['accuracy_percent'], 1) }}%
                                        @else
                                            -
                                        @endif
                                    </div>
                                    <div class="mt-1 text-xs text-violet-700">
                                        {{ number_format((int) ($focusedSummary['answers_correct'] ?? 0)) }}/{{ number_format((int) ($focusedSummary['questions_total'] ?? 0)) }}
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tracked time</div>
                                    <div class="mt-2 text-xl font-semibold text-slate-800">{{ $formatDuration($focusedSummary['total_time_seconds'] ?? 0) }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ trim((string) ($focusedSummary['last_activity_at'] ?? '')) !== '' ? $focusedSummary['last_activity_at'] : 'No activity yet' }}</div>
                                </div>
                            </div>

                            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-sm font-semibold text-slate-700">Progress by module</div>
                                <div class="mt-3 overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead>
                                            <tr class="text-left text-slate-500">
                                                <th class="pb-2">Module</th>
                                                <th class="pb-2">Completed</th>
                                                <th class="pb-2">Attempts</th>
                                                <th class="pb-2">Time</th>
                                                <th class="pb-2">Progress</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @forelse ($focusedModuleProgress as $row)
                                                <tr>
                                                    <td class="py-2 text-slate-700">
                                                        <a href="{{ route('flash-cards.v2.module', ['moduleId' => (int) ($row->module_id ?? 0)]) }}" class="hover:text-sky-700 hover:underline">
                                                            {{ trim((string) ($row->module_title ?? '')) !== '' ? $row->module_title : ('Module #' . (int) ($row->module_id ?? 0)) }}
                                                        </a>
                                                    </td>
                                                    <td class="py-2 text-slate-700">{{ (int) ($row->completed_lessons ?? 0) }}/{{ (int) ($row->lessons_total ?? 0) }}</td>
                                                    <td class="py-2 text-slate-700">{{ number_format((int) ($row->attempts_total ?? 0)) }}</td>
                                                    <td class="py-2 text-slate-700">{{ $formatDuration($row->total_time_seconds ?? 0) }}</td>
                                                    <td class="py-2 text-slate-700">{{ number_format((float) ($row->progress_percent ?? 0), 1) }}%</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="py-3 text-slate-500" colspan="5">No progress by module for this user.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </section>
                    @endif

                    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">Users Overview</h2>
                                <p class="mt-1 text-sm text-slate-600">Sortat după lecții finalizate și activitate recentă.</p>
                            </div>
                        </div>

                        <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="px-4 py-3 text-left">User</th>
                                        <th class="px-4 py-3 text-left">Completed</th>
                                        <th class="px-4 py-3 text-left">Progress</th>
                                        <th class="px-4 py-3 text-left">Attempts</th>
                                        <th class="px-4 py-3 text-left">Accuracy</th>
                                        <th class="px-4 py-3 text-left">Time</th>
                                        <th class="px-4 py-3 text-left">Last activity</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @forelse ($userRows as $row)
                                        <tr class="align-top">
                                            <td class="px-4 py-3">
                                                <div class="font-semibold text-slate-800">
                                                    <a href="{{ url('/users/' . (int) ($row->user_id ?? 0)) }}" class="hover:text-sky-700 hover:underline">
                                                        {{ trim((string) ($row->user_label ?? '')) !== '' ? $row->user_label : ('User #' . (int) ($row->user_id ?? 0)) }}
                                                    </a>
                                                </div>
                                                <div class="mt-1 text-xs text-slate-500">
                                                    ID: {{ (int) ($row->user_id ?? 0) }}
                                                    @if (trim((string) ($row->user_name ?? '')) !== '')
                                                        · {{ $row->user_name }}
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="font-semibold text-slate-800">{{ number_format((int) ($row->completed_lessons ?? 0)) }}</div>
                                                <div class="mt-1 text-xs text-slate-500">from {{ number_format((int) ($summary['catalog_lessons_total'] ?? 0)) }}</div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="font-semibold text-slate-800">{{ number_format((float) ($row->progress_percent ?? 0), 1) }}%</div>
                                                <div class="mt-2 h-2 w-32 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-full rounded-full bg-sky-500" style="width: {{ max(0, min(100, (float) ($row->progress_percent ?? 0))) }}%;"></div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-slate-700">
                                                <div>Total: {{ number_format((int) ($row->attempts_total ?? 0)) }}</div>
                                                <div class="mt-1 text-xs text-emerald-700">Completed: {{ number_format((int) ($row->completed_attempts ?? 0)) }}</div>
                                                <div class="mt-1 text-xs text-amber-700">In progress: {{ number_format((int) ($row->in_progress_attempts ?? 0)) }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-slate-700">
                                                @if ($row->accuracy_percent !== null)
                                                    {{ number_format((float) $row->accuracy_percent, 1) }}%
                                                @else
                                                    <span class="text-slate-400">No quiz yet</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-slate-700">{{ $formatDuration($row->total_time_seconds ?? 0) }}</td>
                                            <td class="px-4 py-3 text-slate-700">
                                                {{ trim((string) ($row->last_activity_at ?? '')) !== '' ? $row->last_activity_at : '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">No user progress records.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if (method_exists($users, 'links'))
                            <div class="mt-4">
                                {{ $users->links() }}
                            </div>
                        @endif
                    </section>

                    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Latest Attempts</h2>
                            <p class="mt-1 text-sm text-slate-600">Ultimele încercări filtrate după user/status.</p>
                        </div>

                        <div class="mt-4 space-y-3">
                            @forelse ($attemptRows as $row)
                                @php
                                    $isCompleted = (string) ($row->status ?? '') === 'completed';
                                    $isInProgress = (string) ($row->status ?? '') === 'in_progress';
                                    $statusClass = $isCompleted
                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                        : ($isInProgress ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-slate-200 bg-slate-50 text-slate-700');
                                @endphp
                                <article class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <a href="{{ url('/users/' . (int) ($row->user_id ?? 0)) }}" class="font-semibold text-slate-800 hover:text-sky-700 hover:underline">
                                                    {{ trim((string) ($row->user_label ?? '')) !== '' ? $row->user_label : ('User #' . (int) ($row->user_id ?? 0)) }}
                                                </a>
                                                <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                                    {{ trim((string) ($row->status ?? '')) !== '' ? $row->status : 'unknown' }}
                                                </span>
                                                <span class="text-xs text-slate-500">Attempt #{{ (int) ($row->attempt_number ?? 0) }}</span>
                                            </div>
                                            <div class="mt-2 text-sm text-slate-700">
                                                <a href="{{ route('flash-cards.v2.lesson', ['lessonId' => (int) ($row->lesson_id ?? 0)]) }}" class="font-semibold hover:text-sky-700 hover:underline">
                                                    {{ trim((string) ($row->lesson_title ?? '')) !== '' ? $row->lesson_title : ('Lesson #' . (int) ($row->lesson_id ?? 0)) }}
                                                </a>
                                                @if ((int) ($row->module_id ?? 0) > 0)
                                                    <span class="text-slate-400">·</span>
                                                    <a href="{{ route('flash-cards.v2.module', ['moduleId' => (int) ($row->module_id ?? 0)]) }}" class="hover:text-sky-700 hover:underline">
                                                        {{ trim((string) ($row->module_title ?? '')) !== '' ? $row->module_title : ('Module #' . (int) ($row->module_id ?? 0)) }}
                                                    </a>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="text-right text-xs text-slate-500">
                                            <div>ID: {{ (int) ($row->id ?? 0) }}</div>
                                            <div class="mt-1">Updated: {{ trim((string) ($row->updated_at ?? '')) !== '' ? $row->updated_at : '-' }}</div>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
                                        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
                                            <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Lesson time</div>
                                            <div class="mt-1 text-sm font-semibold text-slate-800">{{ $formatDuration($row->lesson_time_seconds ?? 0) }}</div>
                                        </div>
                                        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
                                            <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Quiz time</div>
                                            <div class="mt-1 text-sm font-semibold text-slate-800">{{ $formatDuration($row->quiz_time_seconds ?? 0) }}</div>
                                        </div>
                                        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
                                            <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Total time</div>
                                            <div class="mt-1 text-sm font-semibold text-slate-800">{{ $formatDuration($row->total_time_seconds ?? 0) }}</div>
                                        </div>
                                        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
                                            <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Quiz score</div>
                                            <div class="mt-1 text-sm font-semibold text-slate-800">
                                                {{ number_format((int) ($row->answers_correct ?? 0)) }}/{{ number_format((int) ($row->questions_total ?? 0)) }}
                                            </div>
                                        </div>
                                        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
                                            <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Accuracy</div>
                                            <div class="mt-1 text-sm font-semibold text-slate-800">
                                                @if ($row->accuracy_percent !== null)
                                                    {{ number_format((float) $row->accuracy_percent, 1) }}%
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        </div>
                                        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
                                            <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Started / Completed</div>
                                            <div class="mt-1 text-sm text-slate-700">
                                                <div>{{ trim((string) ($row->started_at ?? '')) !== '' ? $row->started_at : '-' }}</div>
                                                <div class="mt-1 text-xs text-slate-500">{{ trim((string) ($row->completed_at ?? '')) !== '' ? $row->completed_at : 'Not completed' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-8 text-center text-sm text-slate-500">
                                    No attempts found for current filters.
                                </div>
                            @endforelse
                        </div>

                        @if (method_exists($attempts, 'links'))
                            <div class="mt-4">
                                {{ $attempts->links() }}
                            </div>
                        @endif
                    </section>
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
    </body>
</html>
