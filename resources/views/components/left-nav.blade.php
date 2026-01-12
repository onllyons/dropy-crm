<aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full border-r border-slate-200 bg-white transition-transform md:translate-x-0">
    <div class="sidebar-inner">
        <div class="nav-brand flex items-center gap-3">
        <img class="h-9 w-9 rounded-xl" src="https://www.language.onllyons.com/ru/ru-en/dist/images/logo/updadte-icon.png" alt="Dropy CRM logo" />
            <div>
                <div class="text-sm font-semibold uppercase tracking-wide">Dropy CRM</div>
                <div class="text-xs text-slate-500">Workspace</div>
            </div>
        </div>
        <nav class="nav-menu relative text-sm font-medium">
            <div class="nav-accent"></div>
            <div class="space-y-1">
                <div class="nav-group" data-nav-group="home">
                    <a class="{{ request()->is('/') ? 'block rounded-lg bg-slate-900 px-3 py-2 text-white' : 'block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100' }}" href="{{ url('/') }}">
                        <i class="fa-solid fa-house mr-2"></i>
                        Dashboard
                    </a>
                    <a class="{{ request()->is('users') ? 'block rounded-lg bg-slate-900 px-3 py-2 text-white' : 'block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100' }}" href="{{ url('/users') }}">
                        <i class="fa-solid fa-user mr-2"></i>
                        Users
                    </a>
                    <a class="{{ request()->is('course') ? 'block rounded-lg bg-slate-900 px-3 py-2 text-white' : 'block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100' }}" href="{{ url('/course') }}">
                        <i class="fa-solid fa-book mr-2"></i>
                        Course
                    </a>
                    <a class="{{ request()->is('books') ? 'block rounded-lg bg-slate-900 px-3 py-2 text-white' : 'block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100' }}" href="{{ url('/books') }}">
                        <i class="fa-solid fa-book-open mr-2"></i>
                        Books
                    </a>
                    <a class="{{ request()->is('games') ? 'block rounded-lg bg-slate-900 px-3 py-2 text-white' : 'block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100' }}" href="{{ url('/games') }}">
                        <i class="fa-solid fa-gamepad mr-2"></i>
                        Games
                    </a>
                    <a class="{{ request()->is('flash-cards') ? 'block rounded-lg bg-slate-900 px-3 py-2 text-white' : 'block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100' }}" href="{{ url('/flash-cards') }}">
                        <i class="fa-solid fa-clone mr-2"></i>
                        Flash-cards
                    </a>
                </div>
                <div class="nav-group nav-group--hidden hidden" data-nav-group="settings">
                    <a class="{{ request()->is('models') ? 'block rounded-lg bg-slate-900 px-3 py-2 text-white' : 'block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100' }}" href="{{ url('/models') }}">
                        <i class="fa-solid fa-layer-group mr-2"></i>
                        Models
                    </a>
                    <a class="{{ request()->is('datatables') ? 'block rounded-lg bg-slate-900 px-3 py-2 text-white' : 'block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100' }}" href="{{ url('/datatables') }}">
                        <i class="fa-solid fa-table mr-2"></i>
                        DataTables
                    </a>
                </div>
                <div class="nav-group nav-group--hidden hidden" data-nav-group="security">
                    <a class="block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100" href="#">
                        <i class="fa-solid fa-shield-halved mr-2"></i>
                        Security center
                    </a>
                    <a class="block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100" href="#">
                        <i class="fa-solid fa-user-lock mr-2"></i>
                        Access roles
                    </a>
                    <a class="block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100" href="#">
                        <i class="fa-solid fa-key mr-2"></i>
                        API keys
                    </a>
                </div>
            </div>
        </nav>
        <div class="nav-logout mt-auto">
            <div class="nav-switch">
                <button class="nav-switch-btn is-active" type="button" data-nav-toggle="home" aria-label="Home links" title="Home links">
                    <i class="fa-solid fa-house"></i>
                </button>
                <button class="nav-switch-btn" type="button" data-nav-toggle="settings" aria-label="Settings links" title="Settings links">
                    <i class="fa-solid fa-gear"></i>
                </button>
                <button class="nav-switch-btn" type="button" data-nav-toggle="security" aria-label="Security links" title="Security links">
                    <i class="fa-solid fa-shield-halved"></i>
                </button>
            </div>
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
