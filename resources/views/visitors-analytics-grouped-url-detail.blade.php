<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Grouped URL detail" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        @php
            $detail = $detail ?? null;
            $rows = $detail['rows'] ?? null;
            $users = $detail['users'] ?? collect();
            $dailyRows = $detail['daily_rows'] ?? collect();
            $topCountries = $detail['top_countries'] ?? collect();
            $topIps = $detail['top_ips'] ?? collect();
            $firstTime = isset($detail['first_time']) && is_numeric($detail['first_time']) ? date('Y-m-d H:i', (int) $detail['first_time']) : '-';
            $lastTime = isset($detail['last_time']) && is_numeric($detail['last_time']) ? date('Y-m-d H:i', (int) $detail['last_time']) : '-';
        @endphp

        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Grouped URL detail" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold">Grouped URL detail</h1>
                                <p class="mt-2 text-sm text-slate-600">
                                    Source: <span class="font-semibold">{{ $detail['source_label'] ?? strtoupper($source ?? 'web') }}</span>
                                    | Value: <span class="font-semibold break-all">{{ $value ?? '-' }}</span>
                                </p>
                            </div>
                            <a href="{{ route('visitors-analytics-grouped-url', ['source' => $source ?? 'web', 'q' => $value ?? '']) }}" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:border-slate-300">
                                Back to grouped search
                            </a>
                        </div>
                    </div>

                    @if (!empty($error))
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ $error }}
                        </div>
                    @endif

                    @if (!empty($detail))
                        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Total rows</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((int) ($detail['total_rows'] ?? 0)) }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Total seconds</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((float) ($detail['total_seconds'] ?? 0), 2, '.', ',') }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Total hours</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((float) ($detail['total_hours'] ?? 0), 2, '.', ',') }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Avg seconds / row</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((float) ($detail['avg_seconds'] ?? 0), 2, '.', ',') }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Unique users</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((int) ($detail['unique_users'] ?? 0)) }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">First / Last seen</div>
                                <div class="mt-2 text-sm font-semibold text-slate-700">{{ $firstTime }}</div>
                                <div class="text-sm text-slate-500">{{ $lastTime }}</div>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-4 lg:grid-cols-3">
                            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                <div class="text-sm font-semibold text-slate-700">Daily activity (latest 60 days)</div>
                                <div class="mt-3 space-y-2 text-sm text-slate-600">
                                    @forelse ($dailyRows as $row)
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="font-medium text-slate-700">{{ $row->day }}</div>
                                            <div class="text-xs text-slate-500">{{ number_format((int) $row->rows_count) }} rows</div>
                                            <div class="text-xs font-semibold text-slate-700">{{ number_format((float) $row->total_hours, 2, '.', ',') }} h</div>
                                        </div>
                                    @empty
                                        <div class="text-xs text-slate-500">No data.</div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                <div class="text-sm font-semibold text-slate-700">Top countries</div>
                                <div class="mt-3 space-y-2 text-sm text-slate-600">
                                    @forelse ($topCountries as $row)
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="truncate">{{ $row->country }}</div>
                                            <div class="text-xs font-semibold text-slate-700">{{ number_format((int) $row->rows_count) }}</div>
                                        </div>
                                    @empty
                                        <div class="text-xs text-slate-500">No data.</div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                <div class="text-sm font-semibold text-slate-700">Top IP addresses</div>
                                <div class="mt-3 space-y-2 text-sm text-slate-600">
                                    @forelse ($topIps as $row)
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="truncate">{{ $row->ipAddress }}</div>
                                            <div class="text-xs font-semibold text-slate-700">{{ number_format((int) $row->rows_count) }}</div>
                                        </div>
                                    @empty
                                        <div class="text-xs text-slate-500">No data.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-slate-700">Raw rows</div>
                                    <div class="text-xs text-slate-500">Latest rows for exact matched value.</div>
                                </div>
                                <form method="GET" action="{{ route('visitors-analytics-grouped-url.detail') }}" class="flex items-center gap-2">
                                    <input type="hidden" name="source" value="{{ $source ?? 'web' }}">
                                    <input type="hidden" name="value" value="{{ $value ?? '' }}">
                                    <label for="per_page" class="text-xs text-slate-600">Rows/page</label>
                                    <select id="per_page" name="per_page" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs text-slate-700" onchange="this.form.submit()">
                                        @foreach (($perPageOptions ?? [50, 100, 250, 500]) as $option)
                                            <option value="{{ $option }}" {{ (int) ($perPage ?? 100) === (int) $option ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>

                            <div class="mt-3 overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        @if (($source ?? 'web') === 'app')
                                            <tr class="border-b border-slate-200 text-left text-slate-500">
                                                <th class="pb-2 pr-3">ID</th>
                                                <th class="pb-2 pr-3">User</th>
                                                <th class="pb-2 pr-3">IP</th>
                                                <th class="pb-2 pr-3">Screen</th>
                                                <th class="pb-2 pr-3">Last screen</th>
                                                <th class="pb-2 pr-3">Country</th>
                                                <th class="pb-2 pr-3">Device / OS</th>
                                                <th class="pb-2 pr-3">Stay sec</th>
                                                <th class="pb-2 pr-3">Time</th>
                                            </tr>
                                        @else
                                            <tr class="border-b border-slate-200 text-left text-slate-500">
                                                <th class="pb-2 pr-3">ID</th>
                                                <th class="pb-2 pr-3">User</th>
                                                <th class="pb-2 pr-3">IP</th>
                                                <th class="pb-2 pr-3">Recovered page</th>
                                                <th class="pb-2 pr-3">History</th>
                                                <th class="pb-2 pr-3">Country</th>
                                                <th class="pb-2 pr-3">Device / OS</th>
                                                <th class="pb-2 pr-3">Stay sec</th>
                                                <th class="pb-2 pr-3">Time</th>
                                            </tr>
                                        @endif
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse (($rows?->items() ?? []) as $row)
                                            @php
                                                $user = $users[$row->user_id] ?? null;
                                                $userLabel = $user ? ($user->username ?? $row->user_id) : ($row->user_id ?? '-');
                                                $timeLabel = is_numeric($row->time ?? null) ? date('Y-m-d H:i', (int) $row->time) : ($row->time ?? '-');
                                            @endphp
                                            @if (($source ?? 'web') === 'app')
                                                <tr>
                                                    <td class="py-2 pr-3 text-slate-700">{{ $row->id }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">
                                                        @if (!empty($row->user_id))
                                                            <a class="font-semibold text-slate-700 hover:underline" href="{{ url('/users/' . $row->user_id) }}">{{ $userLabel }}</a>
                                                            @if (!empty($user) && !empty($user->name))
                                                                <div class="text-xs text-slate-500">{{ $user->name }}</div>
                                                            @endif
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ $row->ipAddress ?? '-' }}</td>
                                                    <td class="py-2 pr-3 text-slate-600 break-all">{{ $row->screen ?? '-' }}</td>
                                                    <td class="py-2 pr-3 text-slate-600 break-all">{{ $row->lastScreen ?? '-' }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ $row->country ?? '-' }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ trim((string) (($row->deviceName ?? '-') . ' / ' . ($row->operatingSystem ?? '-'))) }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ is_numeric($row->lengthStayOnScreen ?? null) ? number_format((float) $row->lengthStayOnScreen, 2, '.', ',') : '-' }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ $timeLabel }}</td>
                                                </tr>
                                            @else
                                                <tr>
                                                    <td class="py-2 pr-3 text-slate-700">{{ $row->id }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">
                                                        @if (!empty($row->user_id))
                                                            <a class="font-semibold text-slate-700 hover:underline" href="{{ url('/users/' . $row->user_id) }}">{{ $userLabel }}</a>
                                                            @if (!empty($user) && !empty($user->name))
                                                                <div class="text-xs text-slate-500">{{ $user->name }}</div>
                                                            @endif
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ $row->ipAddress ?? '-' }}</td>
                                                    <td class="py-2 pr-3 text-slate-600 break-all">{{ $row->recoveredPage ?? '-' }}</td>
                                                    <td class="py-2 pr-3 text-slate-600 break-all">{{ $row->historyToPage ?? '-' }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ $row->country ?? '-' }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ trim((string) (($row->deviceName ?? '-') . ' / ' . ($row->operatingSystem ?? '-'))) }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ is_numeric($row->lengthStayOnPage ?? null) ? number_format((float) $row->lengthStayOnPage, 2, '.', ',') : '-' }}</td>
                                                    <td class="py-2 pr-3 text-slate-600">{{ $timeLabel }}</td>
                                                </tr>
                                            @endif
                                        @empty
                                            <tr>
                                                <td colspan="9" class="py-3 text-slate-500">No rows for this exact value.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($rows)
                                <div class="mt-4">
                                    {{ $rows->links() }}
                                </div>
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
