<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Website/App views" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Website/App views" />

                <main class="p-4 md:p-6">
                    @php
                        $baseQuery = [
                            'range' => $range ?? '30d',
                            'exclude_outliers' => ($excludeOutliers ?? true) ? 1 : 0,
                            'exclude_zero' => ($excludeZero ?? true) ? 1 : 0,
                            'session_gap' => $sessionGapMinutes ?? 30,
                        ];
                        $webUrl = route('users.behavior', array_merge(['id' => $user->id, 'source' => 'web'], $baseQuery));
                        $appUrl = route('users.behavior', array_merge(['id' => $user->id, 'source' => 'app'], $baseQuery));
                    @endphp

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold">Website/App views</h1>
                                <p class="mt-2 text-sm text-slate-600">
                                    Intelligent behavior dashboard for user
                                    <span class="font-semibold text-slate-700">#{{ $user->id }}</span>
                                    ({{ $user->username ?? $user->name ?? 'User' }}).
                                </p>
                                <p class="mt-1 text-xs text-slate-500">Current source: {{ $sourceLabel ?? 'Website' }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex rounded-xl border border-slate-200 bg-slate-50 p-1">
                                    <a class="{{ ($source ?? 'web') === 'web' ? 'rounded-lg border border-slate-900 bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white' : 'rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-xs font-semibold text-slate-600 hover:border-slate-300 hover:bg-white' }}" href="{{ $webUrl }}">Website</a>
                                    <a class="{{ ($source ?? 'web') === 'app' ? 'rounded-lg border border-slate-900 bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white' : 'rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-xs font-semibold text-slate-600 hover:border-slate-300 hover:bg-white' }}" href="{{ $appUrl }}">App</a>
                                </div>
                                <a class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:border-slate-300" href="{{ url('/users/' . $user->id) }}">Back to profile</a>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
                        <form class="flex flex-wrap items-end gap-3" method="get" action="{{ route('users.behavior', ['id' => $user->id]) }}">
                            <input type="hidden" name="source" value="{{ $source ?? 'web' }}" />

                            <div>
                                <label class="text-xs font-semibold text-slate-500" for="range">Range</label>
                                <select class="mt-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-700 focus:border-slate-400 focus:outline-none" id="range" name="range">
                                    @foreach (($rangeOptions ?? []) as $key => $label)
                                        <option value="{{ $key }}" {{ ($range ?? '30d') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-slate-500" for="session_gap">Session gap (minutes)</label>
                                <input class="mt-1 w-32 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-700 focus:border-slate-400 focus:outline-none" id="session_gap" name="session_gap" type="number" min="5" max="180" value="{{ $sessionGapMinutes ?? 30 }}" />
                            </div>

                            <div class="flex flex-wrap items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                                <label class="inline-flex items-center gap-2 text-xs text-slate-600">
                                    <input type="hidden" name="exclude_outliers" value="0" />
                                    <input class="h-3.5 w-3.5 rounded border-slate-300 text-slate-900 focus:ring-slate-400" name="exclude_outliers" type="checkbox" value="1" {{ ($excludeOutliers ?? true) ? 'checked' : '' }} />
                                    Exclude outliers (&gt; {{ $outlierMinutes ?? 30 }}m)
                                </label>
                                <label class="inline-flex items-center gap-2 text-xs text-slate-600">
                                    <input type="hidden" name="exclude_zero" value="0" />
                                    <input class="h-3.5 w-3.5 rounded border-slate-300 text-slate-900 focus:ring-slate-400" name="exclude_zero" type="checkbox" value="1" {{ ($excludeZero ?? true) ? 'checked' : '' }} />
                                    Exclude 0s
                                </label>
                            </div>

                            <button class="rounded-lg border border-slate-200 bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800" type="submit">Apply filters</button>
                        </form>

                        <div class="mt-3 text-xs text-slate-500">
                            Rows kept: <span class="font-semibold text-slate-700">{{ $summary['kept_total'] ?? 0 }}</span> / {{ $summary['raw_total'] ?? 0 }}.
                            Removed by range: {{ $summary['removed_range'] ?? 0 }},
                            zero: {{ $summary['removed_zero'] ?? 0 }},
                            outliers: {{ $summary['removed_outliers'] ?? 0 }}.
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Active days</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['active_days'] ?? 0 }}</div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Sessions (real)</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['total_sessions'] ?? 0 }}</div>
                            <div class="mt-1 text-xs text-slate-500">Gap: {{ $sessionGapMinutes ?? 30 }}m</div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Events</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['total_events'] ?? 0 }}</div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Total time (cleaned)</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['total_time_label'] ?? '0s' }}</div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Top 3 screens by time</div>
                            <div class="mt-2 space-y-1 text-xs text-slate-700">
                                @forelse ($summary['top_screens'] ?? [] as $row)
                                    <div class="truncate">{{ $row['page'] }} <span class="text-slate-500">({{ $row['time'] }})</span></div>
                                @empty
                                    <div class="text-slate-500">No data</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Learning actions</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['learning_total'] ?? 0 }}</div>
                            <div class="mt-1 text-xs text-slate-500">Lesson: {{ $summary['lesson_visits'] ?? 0 }} | Quiz: {{ $summary['quiz_visits'] ?? 0 }}</div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 text-xs text-slate-500">
                        First visit: <span class="font-semibold text-slate-700">{{ $summary['first_visit'] ?? '-' }}</span>
                        <span class="mx-2">|</span>
                        Last visit: <span class="font-semibold text-slate-700">{{ $summary['last_visit'] ?? '-' }}</span>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="text-sm font-semibold text-slate-700">Daily activity summary</div>
                            <div class="text-xs text-slate-500">Grouped by date</div>
                        </div>
                        <div class="mt-3 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-slate-500">
                                        <th class="pb-2">Date</th>
                                        <th class="pb-2">Sessions</th>
                                        <th class="pb-2">Total time</th>
                                        <th class="pb-2">First visit</th>
                                        <th class="pb-2">Last visit</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($dailyRows as $row)
                                        <tr>
                                            <td class="py-2 text-slate-700">{{ $row['date'] }}</td>
                                            <td class="py-2 text-slate-700">{{ $row['sessions'] }}</td>
                                            <td class="py-2 text-slate-700">{{ $row['total_time_label'] }}</td>
                                            <td class="py-2 text-slate-600">{{ $row['first_visit'] }}</td>
                                            <td class="py-2 text-slate-600">{{ $row['last_visit'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="py-3 text-slate-500" colspan="5">No behavior rows found for this user.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="text-sm font-semibold text-slate-700">Page flow map</div>
                            <div class="text-xs text-slate-500">Grouped repeated flows by real sessions</div>
                        </div>
                        <div class="mt-3 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-slate-500">
                                        <th class="pb-2">Flow</th>
                                        <th class="pb-2">Sessions</th>
                                        <th class="pb-2">Share</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($flowRows as $row)
                                        <tr>
                                            <td class="py-2 text-slate-700">{{ $row['flow'] }}</td>
                                            <td class="py-2 text-slate-700">{{ $row['sessions'] }}</td>
                                            <td class="py-2 text-slate-600">{{ $row['share'] }}%</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="py-3 text-slate-500" colspan="3">No flow data available.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-6 lg:grid-cols-3">
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Median time per page</div>
                            <div class="mt-3 overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-left text-slate-500">
                                            <th class="pb-2">Page</th>
                                            <th class="pb-2">Median</th>
                                            <th class="pb-2">Avg</th>
                                            <th class="pb-2">Visits</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse ($avgPerPageRows as $row)
                                            <tr>
                                                <td class="py-2 text-slate-700">{{ $row['page'] }}</td>
                                                <td class="py-2 text-slate-700">{{ $row['median_time_label'] }}</td>
                                                <td class="py-2 text-slate-600">{{ $row['avg_time_label'] }}</td>
                                                <td class="py-2 text-slate-600">{{ $row['visits'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="py-3 text-slate-500" colspan="4">No data.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Pages with highest time</div>
                            <div class="mt-3 overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-left text-slate-500">
                                            <th class="pb-2">Page</th>
                                            <th class="pb-2">Total</th>
                                            <th class="pb-2">Visits</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse ($topTimeRows as $row)
                                            <tr>
                                                <td class="py-2 text-slate-700">{{ $row['page'] }}</td>
                                                <td class="py-2 text-slate-700">{{ $row['total_time_label'] }}</td>
                                                <td class="py-2 text-slate-600">{{ $row['visits'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="py-3 text-slate-500" colspan="3">No data.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Bounce pages (&lt; 5s)</div>
                            <div class="mt-3 overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-left text-slate-500">
                                            <th class="pb-2">Page</th>
                                            <th class="pb-2">Bounce</th>
                                            <th class="pb-2">Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse ($bounceRows as $row)
                                            <tr>
                                                <td class="py-2 text-slate-700">{{ $row['page'] }}</td>
                                                <td class="py-2 text-slate-700">{{ $row['bounce_count'] }}</td>
                                                <td class="py-2 text-slate-600">{{ $row['bounce_rate'] }}%</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="py-3 text-slate-500" colspan="3">No bounce pages.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="text-sm font-semibold text-slate-700">Device & context</div>
                        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                            @php
                                $contextBlocks = [
                                    ['title' => 'Country', 'rows' => $countryRows],
                                    ['title' => 'Language', 'rows' => $languageRows],
                                    ['title' => 'Operating system', 'rows' => $osRows],
                                    ['title' => 'Device', 'rows' => $deviceRows],
                                    ['title' => 'Mobile vs Desktop', 'rows' => $formFactorRows],
                                ];
                            @endphp
                            @foreach ($contextBlocks as $block)
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $block['title'] }}</div>
                                    <div class="mt-2 overflow-x-auto">
                                        <table class="min-w-full text-xs">
                                            <tbody class="divide-y divide-slate-200">
                                                @forelse ($block['rows'] as $row)
                                                    <tr>
                                                        <td class="py-1.5 pr-2 text-slate-700">{{ $row['value'] }}</td>
                                                        <td class="py-1.5 pr-2 text-right font-semibold text-slate-700">{{ $row['count'] }}</td>
                                                        <td class="py-1.5 text-right text-slate-500">{{ $row['share'] }}%</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td class="py-2 text-slate-500">No data</td>
                                                        <td></td>
                                                        <td></td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
    </body>
</html>
