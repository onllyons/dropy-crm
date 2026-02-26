<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Dictionary words" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Dictionary words" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold">Dictionary words</h1>
                                <p class="mt-2 text-sm text-slate-600">
                                    Toate cuvintele din dictionar create cu ChatGPT.
                                </p>
                            </div>
                            <a class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:border-slate-300" href="{{ url('/books') }}">Back to books</a>
                        </div>
                    </div>

                    @if ($error)
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ $error }}
                        </div>
                    @endif

                    <div class="mt-6 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Total words</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $rows->count() }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Users</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $rows->pluck('user_id')->filter()->unique()->count() }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Words with sentence</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $rows->filter(function ($row) { return trim((string) ($row->sentence ?? '')) !== ''; })->count() }}</div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm font-semibold text-slate-700">dictionary_words</div>
                            <div class="text-xs text-slate-500">
                                SELECT id, user_id, original_word, translated_word, ipa, sentence, created_at
                            </div>
                        </div>
                        <div class="mt-3 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-slate-500">
                                        <th class="pb-2">id</th>
                                        <th class="pb-2">user_id</th>
                                        <th class="pb-2">original_word</th>
                                        <th class="pb-2">translated_word</th>
                                        <th class="pb-2">ipa</th>
                                        <th class="pb-2">sentence</th>
                                        <th class="pb-2">created_at</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($rows as $row)
                                        @php
                                            $user = $users[$row->user_id] ?? null;
                                            $userLabel = $user->username ?? $row->user_id;
                                        @endphp
                                        <tr>
                                            <td class="py-2 align-top text-slate-700">{{ $row->id }}</td>
                                            <td class="py-2 align-top text-slate-700">
                                                @if (!empty($row->user_id))
                                                    <a class="font-semibold text-slate-700 hover:underline" href="{{ url('/users/' . $row->user_id) }}">{{ $userLabel }}</a>
                                                    @if (!empty($user) && !empty($user->name))
                                                        <div class="text-xs text-slate-500">{{ $user->name }}</div>
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="py-2 align-top text-slate-700">{{ $row->original_word ?? '-' }}</td>
                                            <td class="py-2 align-top text-slate-700">{{ $row->translated_word ?? '-' }}</td>
                                            <td class="py-2 align-top text-slate-600">{{ $row->ipa ?? '-' }}</td>
                                            <td class="py-2 align-top text-slate-600">
                                                <div class="max-w-xl whitespace-pre-wrap">{{ $row->sentence ?? '-' }}</div>
                                            </td>
                                            <td class="py-2 align-top text-slate-600">{{ $row->created_at ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="py-3 text-slate-500" colspan="7">No dictionary words found.</td>
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
