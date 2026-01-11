<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Login" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900">
        <div class="min-h-screen flex items-center justify-center p-4">
            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-3">
                    <img class="h-10 w-10 rounded-xl" src="https://www.language.onllyons.com/ru/ru-en/dist/images/logo/updadte-icon.png" alt="Dropy CRM logo" />
                    <div>
                        <div class="text-sm font-semibold uppercase tracking-wide">Dropy CRM</div>
                        <div class="text-xs text-slate-500">Workspace</div>
                    </div>
                </div>

                <h1 class="mt-6 text-2xl font-semibold">Sign in</h1>
                <p class="mt-1 text-sm text-slate-600">Private CRM access only.</p>

                <form class="mt-6 space-y-4" method="post" action="{{ url('/login') }}">
                    @csrf
                    <div>
                        <label class="text-sm font-semibold text-slate-700" for="username">Username</label>
                        <input class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" id="username" name="username" type="text" placeholder="username" autocomplete="username" value="{{ old('username') }}" />
                        @error('username')
                            <div class="mt-2 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700" for="password">Password</label>
                        <input class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" id="password" name="password" type="password" placeholder="••••••••" autocomplete="current-password" />
                        @error('password')
                            <div class="mt-2 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>
                    <button class="w-full rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white" type="submit">Login</button>
                </form>
            </div>
        </div>

        <x-script-components />
    </body>
</html>
