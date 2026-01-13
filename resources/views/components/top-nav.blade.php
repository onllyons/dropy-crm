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
            <div class="relative">
                <button id="topLinksButton" class="rounded-lg border border-slate-200 bg-white p-2 text-slate-700 transition hover:border-slate-300" type="button" aria-haspopup="true" aria-expanded="false" aria-label="Open quick links">
                    <i class="fa-solid fa-link"></i>
                </button>
                <div id="topLinksMenu" class="absolute right-0 mt-2 hidden max-h-[70vh] w-[23rem] overflow-y-auto rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
                    <div class="px-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">For App</div>
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://play.google.com/store/apps/details?id=com.onllyons.language" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-brands fa-google-play"></i>
                            </span>
                            <span class="mt-2 text-[11px]">App url Google Play</span>
                        </a>
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://apps.apple.com/lv/app/language/id6479242055" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-brands fa-apple"></i>
                            </span>
                            <span class="mt-2 text-[11px]">App url Apple Play</span>
                        </a>
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://play.google.com/console/u/0/developers/6724588665964932042/app-list" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-store"></i>
                            </span>
                            <span class="mt-2 text-[11px]">play.google.com</span>
                        </a>
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://developer.apple.com/account" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-gear"></i>
                            </span>
                            <span class="mt-2 text-[11px]">developer.apple.com</span>
                        </a>
                    </div>

                    <div class="mt-4 px-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Google</div>
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://tagmanager.google.com/#/home" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-tags"></i>
                            </span>
                            <span class="mt-2 text-[11px]">Tag manager</span>
                        </a>
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://www.google.com/recaptcha/admin/site/572348548" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-shield-halved"></i>
                            </span>
                            <span class="mt-2 text-[11px]">reCaptcha</span>
                        </a>
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://console.cloud.google.com/apis/credentials?project=auth-language-onllyons&supportedpurview=project" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-key"></i>
                            </span>
                            <span class="mt-2 text-[11px]">Google registration</span>
                        </a>
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://www.google.com/adsense/new/u/0/pub-9513914713165936/onboarding" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-coins"></i>
                            </span>
                            <span class="mt-2 text-[11px]">Adsense</span>
                        </a>
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://analytics.google.com/analytics/web/#/p337319825/reports/intelligenthome" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-chart-line"></i>
                            </span>
                            <span class="mt-2 text-[11px]">Analytics</span>
                        </a>
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://search.google.com/search-console?resource_id=https%3A%2F%2Flanguage.onllyons.com%2F" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-magnifying-glass-chart"></i>
                            </span>
                            <span class="mt-2 text-[11px]">Search console</span>
                        </a>
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://search.google.com/test/mobile-friendly?utm_source=mft&utm_medium=redirect&utm_campaign=mft-redirect" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-mobile-screen"></i>
                            </span>
                            <span class="mt-2 text-[11px]">Search test</span>
                        </a>
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://optimize.google.com/optimize/home/#/accounts" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-wand-magic-sparkles"></i>
                            </span>
                            <span class="mt-2 text-[11px]">Optimize</span>
                        </a>
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://pagespeed.web.dev/" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-gauge-high"></i>
                            </span>
                            <span class="mt-2 text-[11px]">Pagespeed</span>
                        </a>
                    </div>

                    <div class="mt-4 px-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Yandex</div>
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://webmaster.yandex.com/site/https:www.language.onllyons.com:443/dashboard/" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-compass"></i>
                            </span>
                            <span class="mt-2 text-[11px]">webmaster.yandex</span>
                        </a>
                    </div>

                    <div class="mt-4 px-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Audio</div>
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://murf.ai/studio" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-microphone"></i>
                            </span>
                            <span class="mt-2 text-[11px]">murf</span>
                        </a>
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://elevenlabs.io/app/speech-synthesis/text-to-speech" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-wave-square"></i>
                            </span>
                            <span class="mt-2 text-[11px]">elevenlabs ai</span>
                        </a>
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://play.ht/app/audio-files?tab=ultra" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-headphones"></i>
                            </span>
                            <span class="mt-2 text-[11px]">play.ht</span>
                        </a>
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://www.happyscribe.com/v2/7213057/folders/workspace" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-file-audio"></i>
                            </span>
                            <span class="mt-2 text-[11px]">Audio to txt</span>
                        </a>
                    </div>

                    <div class="mt-4 px-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Text</div>
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="/ru/ru-en/management/additional-files/packs/other_pages/replace.php" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-repeat"></i>
                            </span>
                            <span class="mt-2 text-[11px]">Replace</span>
                        </a>
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="/ru/ru-en/management/additional-files/packs/other_pages/uppercaser.php" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-text-height"></i>
                            </span>
                            <span class="mt-2 text-[11px]">Lower/upper</span>
                        </a>
                    </div>

                    <div class="mt-4 px-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Video</div>
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://getyarn.io/yarn-find?text=Thank%20you%20very%20much." target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-clapperboard"></i>
                            </span>
                            <span class="mt-2 text-[11px]">getyarn</span>
                        </a>
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://www.playphrase.me/#/search?q=real+to" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-video"></i>
                            </span>
                            <span class="mt-2 text-[11px]">playphrase</span>
                        </a>
                    </div>

                    <div class="mt-4 px-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Images</div>
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://appscreens.com/user/studio" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-image"></i>
                            </span>
                            <span class="mt-2 text-[11px]">Create img</span>
                        </a>
                    </div>

                    <div class="mt-4 px-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Other</div>
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://www.textfixer.com/html/compress-html-compression.php" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-compress"></i>
                            </span>
                            <span class="mt-2 text-[11px]">Compres HTML</span>
                        </a>
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://www.toptal.com/developers/javascript-minifier" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-code"></i>
                            </span>
                            <span class="mt-2 text-[11px]">Compres JS</span>
                        </a>
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://gate2home.com/Romanian-Keyboard" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-keyboard"></i>
                            </span>
                            <span class="mt-2 text-[11px]">Romanian Keyboard</span>
                        </a>
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://www.iloveimg.com/resize-image" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-crop-simple"></i>
                            </span>
                            <span class="mt-2 text-[11px]">resize image</span>
                        </a>
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://tailwindcss.com/docs/installation" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-wind"></i>
                            </span>
                            <span class="mt-2 text-[11px]">tailwindcss</span>
                        </a>
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://getuikit.com/docs/background" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-layer-group"></i>
                            </span>
                            <span class="mt-2 text-[11px]">getuikit</span>
                        </a>
                    </div>

                    <div class="mt-4 px-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Other page from management admin</div>
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="https://language.onllyons.com/ru/ru-en/management/additional-files/packs/other_pages/back-up.php" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-database"></i>
                            </span>
                            <span class="mt-2 text-[11px]">Back Up MySQL</span>
                        </a>
                        <a class="group flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-3 text-center text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="/ru/ru-en/management/additional-files/packs/other/up-w.php" target="_blank" rel="noreferrer">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-sm text-white">
                                <i class="fa-solid fa-briefcase"></i>
                            </span>
                            <span class="mt-2 text-[11px]">postare upwork</span>
                        </a>
                    </div>

                    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-[11px] text-slate-600">
                        <div class="font-semibold text-slate-700">Индивидуальный торговец</div>
                        <div>eds.vid.gov.lv</div>
                        <div>IK Onllyons Language</div>
                        <div>40002207043</div>
                        <div>Novads: Kekavas Novads</div>
                        <div>Novada pilsēta/pagasts: Balozi</div>
                        <div>Ielas nosaukums: Meza Iela</div>
                        <div>Mājas nosaukums/ mājas nr., korpuss: 31</div>
                        <div>Pasta indekss: LV-2128</div>
                        <div>Nekustamā īpašuma objekta (ēkas, dzīvokļa īpašuma vai telpas) kadastra apzīmējums: 80070010127001</div>
                        <div>IK Onllyons Language имеет зарегистрированное структурное подразделение: с регистрационным номером №. 91437091055.</div>
                    </div>
                </div>
            </div>
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
