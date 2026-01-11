<header class="sticky top-0 z-30 border-b border-slate-200 bg-white/70 backdrop-blur-2xl">
    <div class="flex items-center justify-between px-4 py-3 md:px-6">
        <div class="flex items-center gap-3">
            <button id="menuButton" class="rounded-lg border border-slate-200 p-2 text-slate-700 md:hidden" type="button" aria-label="Open menu">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <div class="text-lg font-semibold">{{ $title ?? 'Dashboard' }}</div>
        </div>
        <div class="flex items-center gap-3">
            <input class="hidden w-64 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm md:block" type="text" placeholder="Search..." />
            <button id="offcanvasOpen" class="user-card-top" type="button" aria-label="Open account panel">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-xs font-semibold text-white">
                    {{ strtoupper(substr(Auth::user()->username ?? 'U', 0, 1)) }}
                </div>
                <div class="hidden max-w-[10ch] truncate text-sm font-semibold text-slate-700 md:block">
                    {{ Auth::user()->username ?? 'User' }}
                </div>
            </button>
        </div>
    </div>
</header>
