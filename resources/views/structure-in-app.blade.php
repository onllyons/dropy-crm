<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Structure in app" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Structure in app" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h1 class="text-2xl font-semibold">Structure in app</h1>
                        <p class="mt-3 text-sm text-slate-600">
                            <span class="font-semibold text-slate-700">introduction_start.js</span>
                            is a page in the Dropy React Native project responsible for user registration.
                        </p>
                        <p class="mt-2 text-sm text-slate-600">
                            <span class="font-semibold text-slate-700">components/Welcome.jsx</span>
                            is a modal loaded after user login.
                        </p>
                        <p class="mt-2 text-sm text-slate-600">
                            <span class="font-semibold text-slate-700">components/GiftSubscription.jsx</span>
                            pagina raspunde pentru a afisa un modal despre cadou; la fel acest component afiseaza un gift icon in pagina course.js.
                        </p>
                    </div>
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
    </body>
</html>
