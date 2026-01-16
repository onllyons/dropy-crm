<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Dialog" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Dialog" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold">Dialog</h1>
                                <p class="mt-2 text-sm text-slate-600">Overview for read_dialog and activity.</p>
                            </div>
                            <div class="text-xs font-semibold text-slate-500">
                                DB: {{ session('tenant_db', config('dropy.tenants.default')) }}
                            </div>
                        </div>
                    </div>

                    @if ($error)
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ $error }}
                        </div>
                    @endif

                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Total dialogs</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['books'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Categories</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['categories'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Dialogs with images</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['withImages'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Dialogs with audio</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['withAudio'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Dialogs with subtitles</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['withSubtitles'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Bookmarks total</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['bookmarksTotal'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Reads total</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['readsTotal'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Unique readers</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['readerUsers'] ?? 0 }}</div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm font-semibold text-slate-700">Categories overview</div>
                            <div class="text-xs text-slate-500">Dialogs, bookmarks, reads by category.</div>
                        </div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-slate-500">
                                        <th class="pb-2">Category</th>
                                        <th class="pb-2">Code</th>
                                        <th class="pb-2">Dialogs</th>
                                        <th class="pb-2">Bookmarks</th>
                                        <th class="pb-2">Reads</th>
                                        <th class="pb-2">Updated</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($categoryStats as $category)
                                        @php
                                            $categoryTime = $category->time ?? null;
                                            $categoryDate = is_numeric($categoryTime) ? date('Y-m-d', (int) $categoryTime) : '-';
                                        @endphp
                                        <tr>
                                            <td class="py-2 font-semibold text-slate-700">{{ $category->title }}</td>
                                            <td class="py-2 text-slate-600">{{ $category->code }}</td>
                                            <td class="py-2 text-slate-600">{{ $category->bookCount }}</td>
                                            <td class="py-2 text-slate-600">{{ $category->bookmarkCount }}</td>
                                            <td class="py-2 text-slate-600">{{ $category->readCount }}</td>
                                            <td class="py-2 text-slate-600">{{ $categoryDate }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="py-3 text-slate-500" colspan="6">No categories found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 lg:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Top dialogs by bookmarks</div>
                            <div class="mt-4 space-y-3">
                                @forelse ($topBookmarked as $dialog)
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="text-sm font-semibold text-slate-700">{{ $dialog->title }}</div>
                                            <div class="text-xs font-semibold text-slate-600">Bookmarks: {{ $dialog->count }}</div>
                                        </div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $dialog->url }}</div>
                                    </div>
                                @empty
                                    <div class="text-sm text-slate-500">No bookmark data.</div>
                                @endforelse
                            </div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Top dialogs by reads</div>
                            <div class="mt-4 space-y-3">
                                @forelse ($topRead as $dialog)
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="text-sm font-semibold text-slate-700">{{ $dialog->title }}</div>
                                            <div class="text-xs font-semibold text-slate-600">Reads: {{ $dialog->count }}</div>
                                        </div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $dialog->url }}</div>
                                    </div>
                                @empty
                                    <div class="text-sm text-slate-500">No read data.</div>
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
