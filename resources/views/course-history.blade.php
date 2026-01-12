<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Course history" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Course history" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold">Course history</h1>
                                <p class="mt-2 text-sm text-slate-600">Ultimele 200 de inregistrari din course_history.</p>
                            </div>
                            <div class="text-xs font-semibold text-slate-500">
                                DB: {{ session('tenant_db', config('dropy.tenants.default')) }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Started lessons</div>
                            <div class="mt-3 text-2xl font-semibold">{{ $startedCount ?? 0 }}</div>
                            <div class="mt-1 text-xs text-slate-500">Lectii cu start_time valid.</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Completed lessons</div>
                            <div class="mt-3 text-2xl font-semibold">{{ $completedCount ?? 0 }}</div>
                            <div class="mt-1 text-xs text-slate-500">Start/end + progres complet.</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Never started</div>
                            <div class="mt-3 text-2xl font-semibold">{{ $neverStartedCount ?? 0 }}</div>
                            <div class="mt-1 text-xs text-slate-500">Lectii fara istoric.</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">In progress</div>
                            <div class="mt-3 text-2xl font-semibold">{{ $inProgressCount ?? 0 }}</div>
                            <div class="mt-1 text-xs text-slate-500">Started minus completed.</div>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Active users 7d</div>
                            <div class="mt-3 text-2xl font-semibold">{{ $activeUsers7d ?? 0 }}</div>
                            <div class="mt-1 text-xs text-slate-500">Start/end in ultimele 7 zile.</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Active users 30d</div>
                            <div class="mt-3 text-2xl font-semibold">{{ $activeUsers30d ?? 0 }}</div>
                            <div class="mt-1 text-xs text-slate-500">Start/end in ultimele 30 zile.</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Total time (hours)</div>
                            <div class="mt-3 text-2xl font-semibold">{{ round(($totalTimeSeconds ?? 0) / 3600, 1) }}</div>
                            <div class="mt-1 text-xs text-slate-500">Suma time_study.</div>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 lg:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Top lessons by activity</div>
                            <div class="mt-3 space-y-2 text-sm">
                                @forelse ($topLessons as $item)
                                    @php $course = $courses->get($item->course_id); @endphp
                                    <div class="flex items-start justify-between gap-3 rounded-lg border border-slate-200 px-3 py-2">
                                        <div>
                                            <div class="font-semibold text-slate-700">{{ $course->title ?? 'Course #'.$item->course_id }}</div>
                                            @if ($course)
                                                <div class="text-xs text-slate-500">{{ $course->url }}</div>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-500">x{{ $item->count }}</div>
                                    </div>
                                @empty
                                    <div class="text-sm text-slate-500">No activity data.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Categories with 0 activity</div>
                            <div class="mt-3 space-y-2 text-sm">
                                @forelse ($inactiveCategories as $cat)
                                    <div class="rounded-lg border border-slate-200 px-3 py-2">
                                        <div class="font-semibold text-slate-700">{{ $cat->title }}</div>
                                        <div class="text-xs text-slate-500">{{ $cat->code }}</div>
                                    </div>
                                @empty
                                    <div class="text-sm text-slate-500">All categories have activity.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>


                    @if ($error)
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ $error }}
                        </div>
                    @endif

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-slate-500">
                                        <th class="pb-3">User</th>
                                        <th class="pb-3">Course</th>
                                        <th class="pb-3">Slides</th>
                                        <th class="pb-3">Quizzes</th>
                                        <th class="pb-3">Time (sec)</th>
                                        <th class="pb-3">Start</th>
                                        <th class="pb-3">End</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($history as $row)
                                        @php
                                            $course = $courses->get($row->course_id);
                                            $user = $users->get($row->user_id);
                                        @endphp
                                        <tr>
                                            <td class="py-3">
                                                <div class="font-semibold text-slate-700">#{{ $row->user_id }}</div>
                                                @if ($user)
                                                    <div class="text-xs text-slate-500">
                                                        {{ $user->username ?? $user->name ?? $user->email ?? 'User' }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="py-3">
                                                @if ($course)
                                                    <div class="font-semibold text-slate-700">{{ $course->title }}</div>
                                                    <div class="text-xs text-slate-500">{{ $course->url }}</div>
                                                    <div class="text-[11px] text-slate-400">Category: {{ $course->category_url }}</div>
                                                @else
                                                    <div class="text-slate-500">Course #{{ $row->course_id }}</div>
                                                @endif
                                            </td>
                                            <td class="py-3 text-slate-600">{{ $row->slides_study }}</td>
                                            <td class="py-3 text-slate-600">{{ $row->quizzes_study }}</td>
                                            <td class="py-3 text-slate-600">{{ $row->time_study }}</td>
                                            <td class="py-3 text-slate-600">
                                                {{ $row->start_time ? date('Y-m-d H:i', (int) $row->start_time) : '-' }}
                                            </td>
                                            <td class="py-3 text-slate-600">
                                                {{ $row->end_time ? date('Y-m-d H:i', (int) $row->end_time) : '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="py-4 text-slate-500" colspan="7">No history data.</td>
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
