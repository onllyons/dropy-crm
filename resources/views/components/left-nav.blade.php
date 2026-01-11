<aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full border-r border-slate-200 bg-white transition-transform md:translate-x-0">
    <div class="sidebar-inner">
        <div class="nav-brand flex items-center gap-3">
        <img class="h-9 w-9 rounded-xl" src="https://www.language.onllyons.com/ru/ru-en/dist/images/logo/updadte-icon.png" alt="Dropy CRM logo" />
            <div>
                <div class="text-sm font-semibold uppercase tracking-wide">Dropy CRM</div>
                <div class="text-xs text-slate-500">Workspace</div>
            </div>
        </div>
        <nav class="nav-menu relative space-y-1 text-sm font-medium">
            <div class="nav-accent"></div>
            <a class="{{ request()->is('/') ? 'block rounded-lg bg-slate-900 px-3 py-2 text-white' : 'block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100' }}" href="{{ url('/') }}">
                <i class="fa-solid fa-house mr-2"></i>
                Dashboard
            </a>
            <a class="{{ request()->is('models') ? 'block rounded-lg bg-slate-900 px-3 py-2 text-white' : 'block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100' }}" href="{{ url('/models') }}">
                <i class="fa-solid fa-layer-group mr-2"></i>
                Models
            </a>
            <a class="{{ request()->is('datatables') ? 'block rounded-lg bg-slate-900 px-3 py-2 text-white' : 'block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100' }}" href="{{ url('/datatables') }}">
                <i class="fa-solid fa-table mr-2"></i>
                DataTables
            </a>
        </nav>
        <div class="mt-auto">
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button class="flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" type="submit">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    Logout
                </button>
            </form>
        </div>
    </div>
</aside>
