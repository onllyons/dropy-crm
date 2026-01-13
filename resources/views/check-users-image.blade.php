<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Check users image" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Check users image" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold">Check users image</h1>
                                <p class="mt-2 text-sm text-slate-600">Images where `image` is not default.png.</p>
                            </div>
                            <a class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:border-slate-300" href="{{ url('/users') }}">Back to Users</a>
                        </div>
                    </div>

                    @if ($error)
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ $error }}
                        </div>
                    @endif

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm font-semibold text-slate-700">Images list</div>
                            <div class="text-xs text-slate-500">Total: {{ $users->count() }}</div>
                        </div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            @forelse ($users as $user)
                                @php
                                    $imageValue = $user->image ?? '';
                                    $imageUrl = $imageValue ? 'https://www.language.onllyons.com/ru/ru-en/dist/images/user-images/' . ltrim($imageValue, '/') : null;
                                @endphp
                                <div class="rounded-2xl border border-slate-200 bg-white p-3">
                                    <div class="flex items-center justify-center">
                                        @if ($imageUrl)
                                            <img class="h-52 w-52 rounded-xl object-cover bg-slate-100" src="{{ $imageUrl }}" alt="{{ $user->username ?? 'User' }}" loading="lazy" />
                                        @else
                                            <div class="flex h-52 w-52 items-center justify-center rounded-xl bg-slate-100 text-xs font-semibold text-slate-500">No image</div>
                                        @endif
                                    </div>
                                    <div class="mt-3 text-xs text-slate-500">#{{ $user->id }}</div>
                                    @if (!empty($user->name))
                                        <div class="text-xs text-slate-500">{{ $user->name }}</div>
                                    @endif
                                    <div class="text-sm font-semibold text-slate-700">{{ $user->username ?? '-' }}</div>
                                    <div class="mt-1 break-all text-xs text-slate-500">{{ $user->image ?? '-' }}</div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-6 text-sm text-slate-500">
                                    No images found.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
    </body>
</html>
