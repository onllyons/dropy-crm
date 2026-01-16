<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Games" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Games" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h1 class="text-2xl font-semibold">Games</h1>
                        <p class="mt-2 text-sm text-slate-600">Pagina pentru jocuri.</p>
                        <div class="mt-4">
                            <a class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="{{ url('/games-table-name') }}">
                                Название таблиц из игр
                                <i class="fa-solid fa-arrow-right text-slate-400"></i>
                            </a>
                            <a class="mt-3 inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="{{ url('/games-rules-display') }}">
                                Rules display game
                                <i class="fa-solid fa-arrow-right text-slate-400"></i>
                            </a>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
    </body>
</html>
