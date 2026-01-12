<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Dropy CRM" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Welcome" />

                <main class="p-4 md:p-6">

                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="text-sm font-semibold text-slate-700">Databases</div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($tenantLabels as $dbName => $label)
                                <form method="post" action="{{ route('tenant.switch') }}">
                                    @csrf
                                    <input type="hidden" name="db" value="{{ $dbName }}">
                                    <button class="{{ $tenantDb === $dbName ? 'rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white' : 'rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100' }}" type="submit">
                                        {{ $label }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Users</div>
                            <div class="mt-3 text-2xl font-semibold">{{ $userCount ?? 0 }}</div>
                        </div>
                        <a class="block rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-slate-300 hover:shadow-sm" href="{{ url('/course') }}">
                            <div class="text-sm font-semibold text-slate-700">Course</div>
                            <div class="mt-3 text-sm text-slate-500">Open page</div>
                        </a>
                        <a class="block rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-slate-300 hover:shadow-sm" href="{{ url('/books') }}">
                            <div class="text-sm font-semibold text-slate-700">Books</div>
                            <div class="mt-3 text-sm text-slate-500">Open page</div>
                        </a>
                        <a class="block rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-slate-300 hover:shadow-sm" href="{{ url('/games') }}">
                            <div class="text-sm font-semibold text-slate-700">Games</div>
                            <div class="mt-3 text-sm text-slate-500">Open page</div>
                        </a>
                        <a class="block rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-slate-300 hover:shadow-sm" href="{{ url('/flash-cards') }}">
                            <div class="text-sm font-semibold text-slate-700">Flash-cards</div>
                            <div class="mt-3 text-sm text-slate-500">Open page</div>
                        </a>
                    </div>
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />

    </body>
</html>
