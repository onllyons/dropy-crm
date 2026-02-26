<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="App update info" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="App update info" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h1 class="text-2xl font-semibold">App update info</h1>
                        <p class="mt-2 text-sm text-slate-600">
                            Pagina pentru informatii despre update-urile aplicatiei (version, release date, changelog, mandatory update).
                        </p>
                    </div>

                    <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-800">Obligatoriu la release nou (React Native)</h2>
                        <p class="mt-2 text-sm text-slate-600">
                            In fisierul
                            <code class="rounded-md border border-pink-200 bg-pink-50 px-2 py-0.5 font-bold text-pink-700">app.json</code>,
                            incrementeaza aceste campuri la fiecare build nou:
                        </p>

                        <div class="mt-4 overflow-x-auto rounded-xl border border-pink-200 bg-pink-50 p-4">
                            <div class="mb-2 inline-flex items-center rounded-md border border-pink-200 bg-white px-2 py-1 text-xs font-bold text-pink-700">
                                app.json
                            </div>
<pre class="text-sm leading-6 text-slate-900"><code class="font-semibold">{
  "version": "1.3.xxx",
  "ios": {
    "buildNumber": "xx"
  },
  "android": {
    "versionCode": xx
  }
}</code></pre>
                        </div>

                        <div class="mt-3 text-xs text-slate-500">
                            version = versiunea aplicatiei, ios.buildNumber si android.versionCode trebuie mereu marite fata de release-ul anterior.
                        </div>

                        <p class="mt-5 text-sm text-slate-600">
                            In fisierul
                            <code class="rounded-md border border-pink-200 bg-pink-50 px-2 py-0.5 font-bold text-pink-700">android/app/build.gradle</code>,
                            actualizeaza si urmatorul bloc:
                        </p>

                        <div class="mt-3 overflow-x-auto rounded-xl border border-pink-200 bg-pink-50 p-4">
                            <div class="mb-2 inline-flex items-center rounded-md border border-pink-200 bg-white px-2 py-1 text-xs font-bold text-pink-700">
                                Android: android/app/build.gradle
                            </div>
<pre class="text-sm leading-6 text-slate-900"><code class="font-semibold">defaultConfig {
        applicationId ....
        minSdkVersion ....
        targetSdkVersion ....
        versionCode xx
        versionName "1.3.xx"
    }</code></pre>
                        </div>

                        <p class="mt-5 text-sm text-slate-600">
                            In fisierul
                            <code class="rounded-md border border-pink-200 bg-pink-50 px-2 py-0.5 font-bold text-pink-700">ios/Dropy/Info.plist</code>,
                            actualizeaza si valorile de versiune:
                        </p>

                        <div class="mt-3 overflow-x-auto rounded-xl border border-pink-200 bg-pink-50 p-4">
                            <div class="mb-2 inline-flex items-center rounded-md border border-pink-200 bg-white px-2 py-1 text-xs font-bold text-pink-700">
                                iOS: ios/Dropy/Info.plist
                            </div>
<pre class="text-sm leading-6 text-slate-900"><code class="font-semibold">&lt;plist version="1.0"&gt;
  &lt;dict&gt;
....
&lt;key&gt;CFBundleShortVersionString&lt;/key&gt;
    &lt;string&gt;1.3.xxx&lt;/string&gt;
&lt;key&gt;CFBundleVersion&lt;/key&gt;
    &lt;string&gt;xxx&lt;/string&gt;
  &lt;/dict&gt;
&lt;/plist&gt;</code></pre>
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl border-2 border-rose-300 bg-gradient-to-r from-rose-50 to-pink-50 p-6 shadow-sm">
                        <div class="inline-flex items-center rounded-full border border-rose-300 bg-white px-3 py-1 text-xs font-extrabold uppercase tracking-wide text-rose-700">
                            Important dupa release
                        </div>
                        <p class="mt-3 text-sm font-semibold text-rose-800">
                            Dupa ce AppStore si Google Play accepta aplicatia, aici schimbi versiunile ca sa afisezi mesaj de update disponibil in app:
                        </p>

                        <div class="mt-4 overflow-x-auto rounded-xl border border-rose-300 bg-rose-950 p-4">
                            <div class="mb-2 inline-flex items-center rounded-md border border-rose-300 bg-rose-100 px-2 py-1 text-xs font-bold text-rose-800">
                                /backend/mobile_app/db.php
                            </div>
<pre class="text-sm leading-6 text-rose-100"><code class="font-semibold">const CURRENT_ANDROID_VERSION = "1.3.xx";
const CURRENT_IOS_VERSION = "1.3.xx";</code></pre>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
    </body>
</html>
