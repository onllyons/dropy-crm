<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Data grouped by URL" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Data grouped by URL" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h1 class="text-2xl font-semibold">Data grouped by URL</h1>
                        <p class="mt-2 text-sm text-slate-600">Searches all rows without date limits and groups matches by URL/screen value.</p>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                        <form method="GET" action="{{ route('visitors-analytics-grouped-url') }}" class="flex flex-wrap items-end gap-3">
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500" for="source">Source</label>
                                <select id="source" name="source" class="mt-1 w-72 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700">
                                    @foreach (($sourceOptions ?? []) as $key => $label)
                                        <option value="{{ $key }}" {{ ($source ?? 'web') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="min-w-[280px] flex-1">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500" for="q">Search value</label>
                                <input id="q" name="q" type="text" value="{{ $query ?? '' }}" placeholder="Example: /conjugation or home" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700" />
                            </div>
                            <button type="submit" class="rounded-lg border border-slate-900 bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                                Search
                            </button>
                        </form>
                        <div class="mt-3 text-xs text-slate-500">
                            Search pattern uses LIKE: <span class="font-semibold">%query%</span>
                        </div>
                    </div>

                    @if (!empty($error))
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ $error }}
                        </div>
                    @endif

                    @if (!empty($result))
                        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Total rows</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((int) ($result['total_rows'] ?? 0)) }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Total stay (seconds)</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((float) ($result['total_seconds'] ?? 0), 2, '.', ',') }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Total stay (hours)</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((float) ($result['total_hours'] ?? 0), 2, '.', ',') }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Search context</div>
                                <div class="mt-2 text-sm font-semibold text-slate-700">{{ $result['source_label'] ?? '-' }}</div>
                                <div class="mt-1 text-xs text-slate-500">Column: {{ $result['search_column'] ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-slate-700">Grouped results</div>
                                    <div class="text-xs text-slate-500">
                                        Showing top {{ number_format((int) ($result['group_limit'] ?? 0)) }} groups by row count.
                                    </div>
                                </div>
                                <div class="text-xs text-slate-500">
                                    Total distinct groups: {{ number_format((int) ($result['total_groups'] ?? 0)) }}
                                </div>
                            </div>

                            <div class="mt-3 overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-slate-200 text-left text-slate-500">
                                            <th class="pb-2 pr-3">Recovered page / Screen</th>
                                            <th class="pb-2 pr-3">Rows</th>
                                            <th class="pb-2 pr-3">Total seconds</th>
                                            <th class="pb-2 pr-3">Total hours</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse (($result['groups'] ?? []) as $row)
                                            <tr>
                                                <td class="py-2 pr-3 text-slate-700">{{ $row->grouped_value !== '' ? $row->grouped_value : '-' }}</td>
                                                <td class="py-2 pr-3 text-slate-600">{{ number_format((int) ($row->rows_count ?? 0)) }}</td>
                                                <td class="py-2 pr-3 text-slate-600">{{ number_format((float) ($row->total_seconds ?? 0), 2, '.', ',') }}</td>
                                                <td class="py-2 pr-3 text-slate-600">{{ number_format((float) ($row->total_hours ?? 0), 2, '.', ',') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="py-3 text-slate-500">No grouped results for current search.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    @if (empty($result) && empty($error))
                        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 text-sm text-slate-600">
                            Select source + type search value, then click <span class="font-semibold">Search</span>.
                        </div>
                    @endif
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
    </body>
</html>
