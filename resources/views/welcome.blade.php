<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Dropy CRM" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen md:flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="content-offset flex-1">
                <x-top-nav title="Welcome" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h1 class="text-3xl font-semibold">hello world</h1>
                        <p class="mt-2 text-slate-600">Aici pornim CRM-ul tau. Sidebar si topbar sunt responsive.</p>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Leads</div>
                            <div class="mt-3 text-2xl font-semibold">24</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Tasks</div>
                            <div class="mt-3 text-2xl font-semibold">8</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Deals</div>
                            <div class="mt-3 text-2xl font-semibold">$12,300</div>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <x-script-components />
        <script>

    </body>
</html>
