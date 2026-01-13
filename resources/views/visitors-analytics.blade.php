<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Visitor analytics" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Visitor analytics" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h1 class="text-2xl font-semibold">Visitor analytics</h1>
                        <p class="mt-2 text-sm text-slate-600">Quick overview of web + app traffic for this tenant.</p>
                    </div>

                    @if (!empty($error))
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ $error }}
                        </div>
                    @endif

                    @php
                        $stats = $stats ?? [];
                        $total = $stats['total'] ?? 0;
                        $webTotal = $stats['web_total'] ?? 0;
                        $appTotal = $stats['app_total'] ?? 0;
                        $webUnique = $stats['web_unique'] ?? 0;
                        $appUnique = $stats['app_unique'] ?? 0;
                        $webLast24 = $stats['web_last24'] ?? 0;
                        $appLast24 = $stats['app_last24'] ?? 0;
                    @endphp

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm font-semibold text-slate-700">Traffic snapshot</div>
                            <div class="text-xs text-slate-500">Counts are based on visitorBehaviorAnalytics tables.</div>
                        </div>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Total events</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $total }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Web events</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $webTotal }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">App events</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $appTotal }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Last 24h (web)</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $webLast24 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Last 24h (app)</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $appLast24 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Unique hashes (web)</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $webUnique }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Unique hashes (app)</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $appUnique }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="text-sm font-semibold text-slate-700">Analytics sources</div>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            <a class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="{{ url('/visitors-analytics-web') }}">
                                Website analytics
                                <i class="fa-solid fa-arrow-right text-slate-400"></i>
                            </a>
                            <a class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="{{ url('/visitors-analytics-app') }}">
                                App analytics
                                <i class="fa-solid fa-arrow-right text-slate-400"></i>
                            </a>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 lg:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Top pages (web)</div>
                            <div class="mt-3 space-y-2 text-sm text-slate-600">
                                @forelse ($topPages as $row)
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="truncate">{{ $row->recoveredPage }}</div>
                                        <div class="text-xs font-semibold text-slate-700">{{ $row->count }}</div>
                                    </div>
                                @empty
                                    <div class="text-xs text-slate-500">No data yet.</div>
                                @endforelse
                            </div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Top screens (app)</div>
                            <div class="mt-3 space-y-2 text-sm text-slate-600">
                                @forelse ($topScreens as $row)
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="truncate">{{ $row->screen }}</div>
                                        <div class="text-xs font-semibold text-slate-700">{{ $row->count }}</div>
                                    </div>
                                @empty
                                    <div class="text-xs text-slate-500">No data yet.</div>
                                @endforelse
                            </div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Top countries (web)</div>
                            <div class="mt-3 space-y-2 text-sm text-slate-600">
                                @forelse ($topCountriesWeb as $row)
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="truncate">{{ $row->country }}</div>
                                        <div class="text-xs font-semibold text-slate-700">{{ $row->count }}</div>
                                    </div>
                                @empty
                                    <div class="text-xs text-slate-500">No data yet.</div>
                                @endforelse
                            </div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Top countries (app)</div>
                            <div class="mt-3 space-y-2 text-sm text-slate-600">
                                @forelse ($topCountriesApp as $row)
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="truncate">{{ $row->country }}</div>
                                        <div class="text-xs font-semibold text-slate-700">{{ $row->count }}</div>
                                    </div>
                                @empty
                                    <div class="text-xs text-slate-500">No data yet.</div>
                                @endforelse
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
