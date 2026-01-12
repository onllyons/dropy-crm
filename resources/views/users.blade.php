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
                                <p class="mt-2 text-sm text-slate-600">Snapshot and data quality overview.</p>
                            </div>
                        </div>
                    </div>

                    @if ($error)
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ $error }}
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
                                <div class="text-xs font-semibold text-slate-500">Profile access</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $userStats->profile_access ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Missing email</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $userStats->missing_email ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Missing bio</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $userStats->missing_bio ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Missing image</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $userStats->missing_image ?? 0 }}</div>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-4 lg:grid-cols-2">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-sm font-semibold text-slate-700">Recent users (last 10)</div>
                                <div class="mt-3 overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead>
                                            <tr class="text-left text-slate-500">
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
                                                    $timeValue = $user->time ?? null;
                                                    $timeLabel = is_numeric($timeValue) ? date('Y-m-d', (int) $timeValue) : ($timeValue ?? '-');
                                                @endphp
                                                <tr>
                                                    <td class="py-2">
                                                        <div class="font-semibold text-slate-700">#{{ $user->id }}</div>
                                                        <div class="text-xs text-slate-500">{{ $user->username }}</div>
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

                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-sm font-semibold text-slate-700">Levels distribution</div>
                                <div class="mt-3 space-y-2 text-sm">
                                    @forelse ($levelDistribution as $level)
                                        <div class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2">
                                            <div class="text-slate-700">{{ $level->level ?? 'unknown' }}</div>
                                            <div class="text-slate-500">x{{ $level->count }}</div>
                                        </div>
                                    @empty
                                        <div class="text-sm text-slate-500">No level data.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
    </body>
</html>
