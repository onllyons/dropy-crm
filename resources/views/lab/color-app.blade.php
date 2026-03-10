<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Color App" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Color App" />

                <main class="p-4 md:p-6">
                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h1 class="text-2xl font-semibold text-slate-900">Color App</h1>
                        <p class="mt-2 text-sm text-slate-600">
                            Simple palette template with copy buttons.
                        </p>
                        <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-700">
                            <div class="font-semibold text-slate-800">Paths</div>
                            <div class="mt-1 font-mono">~/Desktop/Onllyons/packs/theme/tokens.js</div>
                            <div class="font-mono">~/Desktop/Onllyons/packs/css/globalCss.js</div>
                            <div class="mt-2 font-mono">~/Desktop/Onllyons/packs/components/buttons/AnimatedButtonShadow.tsx</div>
                            <div class="font-mono">~/Desktop/Onllyons/packs/components/buttons/AnimatedButtonShadowLoad.tsx</div>
                        </div>
                    </section>

                    <section class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:col-span-2 lg:col-span-3">
                            <h2 class="text-base font-semibold text-slate-800">Color compare (2 inputs)</h2>
                            <p class="mt-1 text-xs text-slate-500">Enter HEX in both inputs to compare colors side by side.</p>

                            <div class="mt-3 grid gap-4 md:grid-cols-2">
                                <div class="rounded-xl border border-slate-200 p-3">
                                    <div class="text-xs font-semibold text-slate-600">Color A</div>
                                    <div class="mt-2 flex gap-2">
                                        <input id="compareHexA" type="text" value="#1C9DF6" class="js-compare-hex w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700" data-preview-id="comparePreviewA" />
                                        <input id="comparePickerA" type="color" value="#1C9DF6" class="js-compare-picker h-10 w-12 cursor-pointer rounded-lg border border-slate-300 bg-white p-1" data-hex-id="compareHexA" />
                                    </div>
                                    <div id="comparePreviewA" class="mt-3 h-32 rounded-lg border border-slate-200" style="background-color: #1C9DF6;"></div>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-3">
                                    <div class="text-xs font-semibold text-slate-600">Color B</div>
                                    <div class="mt-2 flex gap-2">
                                        <input id="compareHexB" type="text" value="#FF4B4B" class="js-compare-hex w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700" data-preview-id="comparePreviewB" />
                                        <input id="comparePickerB" type="color" value="#FF4B4B" class="js-compare-picker h-10 w-12 cursor-pointer rounded-lg border border-slate-300 bg-white p-1" data-hex-id="compareHexB" />
                                    </div>
                                    <div id="comparePreviewB" class="mt-3 h-32 rounded-lg border border-slate-200" style="background-color: #FF4B4B;"></div>
                                </div>
                            </div>
                        </article>

                        @php
                            $palette = [
                                ['name' => 'Macaw', 'hex' => '#1C9DF6'],
                                ['name' => 'Cardinal', 'hex' => '#FF4B4B'],
                                ['name' => 'Bee', 'hex' => '#FFC800'],
                            ];
                        @endphp

                        @foreach ($palette as $color)
                            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="h-36 rounded-xl border border-slate-200" style="background-color: {{ $color['hex'] }};"></div>
                                <div class="mt-3 text-base font-semibold text-slate-800">{{ $color['name'] }}</div>
                                <div class="mt-2 flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                    <span>{{ $color['hex'] }}</span>
                                    <button
                                        type="button"
                                        class="js-copy-hex inline-flex items-center gap-1 rounded-md border border-slate-300 bg-white px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                        data-hex="{{ $color['hex'] }}"
                                    >
                                        <i class="fa-regular fa-copy"></i>
                                        Copy
                                    </button>
                                </div>
                            </article>
                        @endforeach
                    </section>
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
        <script>
            (function () {
                const showToast = function (message) {
                    if (window.toastr) {
                        toastr.success(message);
                        return;
                    }
                    console.log(message);
                };

                const showError = function (message) {
                    if (window.toastr) {
                        toastr.error(message);
                        return;
                    }
                    console.error(message);
                };

                document.querySelectorAll('.js-copy-hex').forEach(function (button) {
                    button.addEventListener('click', function () {
                        const hex = button.getAttribute('data-hex') || '';
                        if (!hex || !navigator.clipboard) {
                            showError('Copy not available.');
                            return;
                        }

                        navigator.clipboard.writeText(hex)
                            .then(function () {
                                showToast('Copied ' + hex);
                            })
                            .catch(function () {
                                showError('Unable to copy HEX.');
                            });
                    });
                });

                const normalizeHex = function (value) {
                    const raw = (value || '').trim();
                    if (!raw) {
                        return null;
                    }

                    const hex = raw.charAt(0) === '#' ? raw : ('#' + raw);
                    const shortMatch = /^#([0-9a-fA-F]{3})$/.exec(hex);
                    if (shortMatch) {
                        const short = shortMatch[1];
                        return ('#' + short[0] + short[0] + short[1] + short[1] + short[2] + short[2]).toUpperCase();
                    }

                    const longMatch = /^#([0-9a-fA-F]{6})$/.exec(hex);
                    if (longMatch) {
                        return ('#' + longMatch[1]).toUpperCase();
                    }

                    return null;
                };

                document.querySelectorAll('.js-compare-hex').forEach(function (input) {
                    input.addEventListener('input', function () {
                        const normalized = normalizeHex(input.value);
                        const previewId = input.getAttribute('data-preview-id') || '';
                        const preview = previewId ? document.getElementById(previewId) : null;
                        const pickerId = input.id === 'compareHexA' ? 'comparePickerA' : 'comparePickerB';
                        const picker = document.getElementById(pickerId);

                        if (!normalized) {
                            input.classList.add('border-red-300', 'bg-red-50');
                            return;
                        }

                        input.classList.remove('border-red-300', 'bg-red-50');
                        if (preview) {
                            preview.style.backgroundColor = normalized;
                        }
                        if (picker) {
                            picker.value = normalized;
                        }
                    });
                });

                document.querySelectorAll('.js-compare-picker').forEach(function (picker) {
                    picker.addEventListener('input', function () {
                        const hexId = picker.getAttribute('data-hex-id') || '';
                        const hexInput = hexId ? document.getElementById(hexId) : null;
                        if (!hexInput) {
                            return;
                        }

                        hexInput.value = picker.value.toUpperCase();
                        hexInput.dispatchEvent(new Event('input'));
                    });
                });
            })();
        </script>
    </body>
</html>
