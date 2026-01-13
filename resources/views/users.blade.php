<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Users" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Users" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold">Users</h1>
                                <a class="mt-2 inline-flex items-center gap-2 rounded-lg border border-sky-200 bg-sky-50 px-3 py-1.5 text-sm font-semibold text-sky-700 transition hover:border-sky-300 hover:bg-sky-100" href="{{ url('/check-users-image') }}">Check users image</a>
                            </div>
                        </div>
                    </div>

                    @if ($error)
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ $error }}
                        </div>
                    @endif

                    @if (config('app.debug') && !empty($debug))
                        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-900">
                            <div class="text-sm font-semibold">Debug time sources</div>
                            <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                <div><span class="font-semibold">PHP now:</span> {{ $debug['php_now'] ?? '-' }} ({{ $debug['php_tz'] ?? '-' }})</div>
                                <div><span class="font-semibold">PHP UTC:</span> {{ $debug['php_utc_now'] ?? '-' }}</div>
                                <div><span class="font-semibold">DB NOW:</span> {{ $debug['db_now'] ?? '-' }}</div>
                                <div><span class="font-semibold">DB CURDATE:</span> {{ $debug['db_curdate'] ?? '-' }}</div>
                                <div><span class="font-semibold">DB tz:</span> {{ $debug['db_session_tz'] ?? '-' }} / {{ $debug['db_system_tz'] ?? '-' }}</div>
                                <div><span class="font-semibold">MAX time:</span> {{ $debug['max_time'] ?? '-' }} ({{ $debug['max_time_dt'] ?? '-' }})</div>
                                <div><span class="font-semibold">MIN time:</span> {{ $debug['min_time'] ?? '-' }} ({{ $debug['min_time_dt'] ?? '-' }})</div>
                                <div><span class="font-semibold">Today count (DB):</span> {{ $debug['db_today_count'] ?? '-' }}</div>
                            </div>
                        </div>
                    @endif

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm font-semibold text-slate-700">Users snapshot</div>
                            <div class="text-xs text-slate-500">Main users table</div>
                        </div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Total users</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $userStats->total ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Verified</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $userStats->verified ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Google users</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $userStats->google ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Apple users</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $userStats->apple ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">New today</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $newToday ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">This week</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $newWeek ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">This month</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $newMonth ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">This year</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $newYear ?? 0 }}</div>
                            </div>
                            @forelse ($levelDistribution as $level)
                                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                    <div class="text-xs font-semibold text-slate-500">Level {{ $level->level ?? 'unknown' }}</div>
                                    <div class="mt-2 text-xl font-semibold text-slate-700">{{ $level->count }}</div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                    <div class="text-xs font-semibold text-slate-500">Levels</div>
                                    <div class="mt-2 text-xl font-semibold text-slate-700">0</div>
                                </div>
                            @endforelse
                        </div>

                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-4">
                        @php
                            $range = $range ?? 'today';
                            $rangeLabel = $rangeLabel ?? 'Today';
                            $search = $search ?? '';
                            $rangeButtonBase = 'rounded-lg border px-3 py-1 text-xs font-semibold transition';
                            $searchParam = $search !== '' ? '&q=' . urlencode($search) : '';
                        @endphp
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-slate-700">Users list</div>
                                <div class="text-xs text-slate-500">Range: {{ $rangeLabel }}. Showing up to 1000 rows.</div>
                                <div class="text-xs text-slate-500">Search fields: name, username, email.</div>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <form class="flex items-center gap-2" method="get" action="{{ url('/users') }}">
                                    <input type="hidden" name="range" value="{{ $range }}" />
                                    <input class="w-52 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-700 focus:border-slate-400 focus:outline-none" name="q" type="text" value="{{ $search }}" placeholder="Search name, username, email" />
                                    <button class="rounded-lg border border-slate-200 bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800" type="submit">Search</button>
                                    @if ($search !== '')
                                        <a class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:border-slate-300" href="{{ url('/users?range=' . $range) }}">Clear</a>
                                    @endif
                                </form>
                                <div class="flex flex-wrap gap-2 rounded-xl border border-slate-200 bg-slate-50 p-2">
                                    <a class="{{ $rangeButtonBase }} {{ $range === 'today' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300' }}" href="{{ url('/users?range=today' . $searchParam) }}">Today</a>
                                    <a class="{{ $rangeButtonBase }} {{ $range === 'yesterday' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300' }}" href="{{ url('/users?range=yesterday' . $searchParam) }}">Yesterday</a>
                                    <a class="{{ $rangeButtonBase }} {{ $range === 'week' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300' }}" href="{{ url('/users?range=week' . $searchParam) }}">This week</a>
                                    <a class="{{ $rangeButtonBase }} {{ $range === 'month' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300' }}" href="{{ url('/users?range=month' . $searchParam) }}">This month</a>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-slate-500">
                                        <th class="pb-2">Avatar</th>
                                        <th class="pb-2">User</th>
                                        <th class="pb-2">Email</th>
                                        <th class="pb-2">Level</th>
                                        <th class="pb-2">Verified</th>
                                        <th class="pb-2">Time</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($recentUsers as $user)
                                        @php
                                            $imageValue = $user->image ?? null;
                                            $imageUrl = $imageValue ? 'https://www.language.onllyons.com/ru/ru-en/dist/images/user-images/' . ltrim($imageValue, '/') : null;
                                            $timeLabel = $user->time_label ?? null;
                                            $timeLabel = $timeLabel ? substr($timeLabel, 0, 10) : '-';
                                        @endphp
                                        <tr>
                                            <td class="py-2">
                                                <a href="{{ url('/users/' . $user->id) }}">
                                                    @if ($imageUrl)
                                                        <img class="h-9 w-9 rounded-full object-cover bg-slate-100" src="{{ $imageUrl }}" alt="{{ $user->username ?? 'User' }}" loading="lazy" />
                                                    @else
                                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-200 text-xs font-semibold text-slate-600">
                                                            {{ strtoupper(substr($user->username ?? 'U', 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </a>
                                            </td>
                                            <td class="py-2">
                                                <a class="block rounded-md px-1 py-0.5 transition hover:bg-slate-50" href="{{ url('/users/' . $user->id) }}">
                                                    <div class="font-semibold text-slate-700">#{{ $user->id }}</div>
                                                    @if (!empty($user->name))
                                                        <div class="text-xs text-slate-500">{{ $user->name }}</div>
                                                    @endif
                                                    <div class="text-xs text-slate-500">{{ $user->username }}</div>
                                                </a>
                                            </td>
                                            <td class="py-2 text-slate-600">{{ $user->email ?? '-' }}</td>
                                            <td class="py-2 text-slate-600">{{ $user->level ?? '-' }}</td>
                                            <td class="py-2 text-slate-600">{{ ($user->verified ?? 0) > 0 ? 'yes' : 'no' }}</td>
                                            <td class="py-2 text-slate-600">{{ $timeLabel }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="py-3 text-slate-500" colspan="5">No users found.</td>
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
