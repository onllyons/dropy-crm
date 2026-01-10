<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Dropy CRM</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900">
        <div class="min-h-screen md:flex">
            <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full border-r border-slate-200 bg-white p-5 transition-transform md:static md:translate-x-0">
                <div class="mb-8 flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-900 text-white">D</div>
                    <div>
                        <div class="text-sm font-semibold uppercase tracking-wide">Dropy CRM</div>
                        <div class="text-xs text-slate-500">From zero</div>
                    </div>
                </div>
                <nav class="space-y-1 text-sm font-medium">
                    <a class="block rounded-lg bg-slate-900 px-3 py-2 text-white" href="#">Dashboard</a>
                    <a class="block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100" href="#">Clients</a>
                    <a class="block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100" href="#">Deals</a>
                    <a class="block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100" href="#">Tasks</a>
                    <a class="block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100" href="#">Reports</a>
                </nav>
            </aside>

            <div class="flex-1">
                <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
                    <div class="flex items-center justify-between px-4 py-3 md:px-6">
                        <div class="flex items-center gap-3">
                            <button id="menuButton" class="rounded-lg border border-slate-200 p-2 text-slate-700 md:hidden" type="button" aria-label="Open menu">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>
                            <div class="text-lg font-semibold">Welcome</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <input class="hidden w-64 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm md:block" type="text" placeholder="Search..." />
                            <div class="h-9 w-9 rounded-full bg-slate-200"></div>
                        </div>
                    </div>
                </header>

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

        <script>
            const menuButton = document.getElementById('menuButton');
            const sidebar = document.getElementById('sidebar');
            menuButton.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });
        </script>
    </body>
</html>
