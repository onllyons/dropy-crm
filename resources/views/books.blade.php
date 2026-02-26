<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Books" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Books" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold">Books</h1>
                                <p class="mt-2 text-sm text-slate-600">Overview for read_books and activity.</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <a class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100" href="{{ url('/poetry') }}">
                                        Poetry
                                    </a>
                                    <a class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100" href="{{ url('/dialog') }}">
                                        Dialog
                                    </a>
                                    <a class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100" href="{{ route('books.dictionary-words') }}">
                                        Dictionary words
                                    </a>
                                </div>
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
                            <div class="text-xs font-semibold text-slate-500">Total books</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['books'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Categories</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['categories'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Unique authors</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['authors'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Books with audio</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['withAudio'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Books with subtitles</div>
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
                            <div class="text-xs text-slate-500">Books, bookmarks, reads by category.</div>
                        </div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-slate-500">
                                        <th class="pb-2">Category</th>
                                        <th class="pb-2">Code</th>
                                        <th class="pb-2">Books</th>
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
                            <div class="text-sm font-semibold text-slate-700">Top books by bookmarks</div>
                            <div class="mt-4 space-y-3">
                                @forelse ($topBookmarked as $book)
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="text-sm font-semibold text-slate-700">{{ $book->title }}</div>
                                            <div class="text-xs font-semibold text-slate-600">Bookmarks: {{ $book->count }}</div>
                                        </div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $book->url }}</div>
                                    </div>
                                @empty
                                    <div class="text-sm text-slate-500">No bookmark data.</div>
                                @endforelse
                            </div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Top books by reads</div>
                            <div class="mt-4 space-y-3">
                                @forelse ($topRead as $book)
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="text-sm font-semibold text-slate-700">{{ $book->title }}</div>
                                            <div class="text-xs font-semibold text-slate-600">Reads: {{ $book->count }}</div>
                                        </div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $book->url }}</div>
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
