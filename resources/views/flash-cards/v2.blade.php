<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Flash-cards V2" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Flash-cards V2" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold">Flash-cards V2</h1>
                                <p class="mt-2 text-sm text-slate-600">Categorie (module) cu lecții afișate dedesubt, pe carduri.</p>
                            </div>
                            <div>
                                <a href="{{ route('flash-cards.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fa-solid fa-arrow-left"></i>
                                    Back to Flash-cards
                                </a>
                            </div>
                        </div>
                    </div>

                    @if (!empty($error))
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ $error }}
                        </div>
                    @endif

                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Total categories</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((int) (($summary['modules'] ?? 0))) }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Total lessons</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((int) (($summary['lessons'] ?? 0))) }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Active categories</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((int) (($summary['active_modules'] ?? 0))) }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Active lessons</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((int) (($summary['active_lessons'] ?? 0))) }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Total words</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((int) (($summary['items'] ?? 0))) }}</div>
                        </div>
                        <div class="rounded-2xl border border-amber-300 bg-amber-50 p-4">
                            <div class="text-xs font-semibold text-amber-700">Duplicate groups (global)</div>
                            <div class="mt-2 text-xl font-semibold text-amber-700">{{ number_format((int) (($summary['reused_groups_global'] ?? 0))) }}</div>
                        </div>
                        <div class="rounded-2xl border border-amber-300 bg-amber-50 p-4">
                            <div class="text-xs font-semibold text-amber-700">Duplicate extra rows</div>
                            <div class="mt-2 text-xl font-semibold text-amber-700">{{ number_format((int) (($summary['reused_rows_global'] ?? 0))) }}</div>
                        </div>
                    </div>

                    @if (($globalReusedGroups ?? collect())->isNotEmpty())
                        <section class="mt-6 rounded-2xl border border-amber-300 bg-amber-50 p-5 shadow-sm">
                            <h2 class="text-lg font-semibold text-amber-700">Duplicates Across Entire Table (smart)</h2>
                            <p class="mt-1 text-sm text-amber-700">
                                Repetări globale în toate modulele/lecțiile după combinația normalizată <code>type + text_from + text_to</code>.
                            </p>

                            <div class="mt-4 overflow-x-auto rounded-xl border border-amber-200 bg-white">
                                <table class="min-w-full divide-y divide-amber-100 text-sm">
                                    <thead class="bg-amber-50 text-xs uppercase tracking-wide text-amber-700">
                                        <tr>
                                            <th class="px-3 py-2 text-left">Type (norm)</th>
                                            <th class="px-3 py-2 text-left">Type (raw)</th>
                                            <th class="px-3 py-2 text-left">Text from</th>
                                            <th class="px-3 py-2 text-left">Text to</th>
                                            <th class="px-3 py-2 text-left">Count</th>
                                            <th class="px-3 py-2 text-left">Lessons</th>
                                            <th class="px-3 py-2 text-left">Modules</th>
                                            <th class="px-3 py-2 text-left">AI</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-amber-100">
                                        @foreach (($globalReusedGroups ?? collect()) as $row)
                                        @php
                                            $lessonIds = collect(explode(',', trim((string) ($row->lesson_ids ?? ''))))
                                                ->map(fn($id) => trim($id))
                                                ->filter()
                                                ->values();
                                            $moduleIds = collect(explode(',', trim((string) ($row->module_ids ?? ''))))
                                                ->map(fn($id) => trim($id))
                                                ->filter()
                                                ->values();
                                            $gptPayload = [
                                                'type_norm' => trim((string) ($row->type ?? '')),
                                                'text_from' => trim((string) ($row->text_from ?? '')),
                                                'text_to' => trim((string) ($row->text_to ?? '')),
                                                'lesson_ids' => $lessonIds->map(function ($id) {
                                                    return (int) $id;
                                                })->values()->all(),
                                            ];
                                        @endphp
                                        <tr class="align-top">
                                            <td class="px-3 py-2 text-amber-700">{{ trim((string) ($row->type ?? '')) !== '' ? $row->type : '-' }}</td>
                                            <td class="px-3 py-2 text-amber-700">{{ trim((string) ($row->raw_types ?? '')) !== '' ? $row->raw_types : '-' }}</td>
                                            <td class="px-3 py-2 text-amber-700">{{ trim((string) ($row->text_from ?? '')) !== '' ? $row->text_from : '-' }}</td>
                                            <td class="px-3 py-2 text-amber-700">{{ trim((string) ($row->text_to ?? '')) !== '' ? $row->text_to : '-' }}</td>
                                            <td class="px-3 py-2 font-semibold text-amber-700">{{ number_format((int) ($row->total_count ?? 0)) }}</td>
                                            <td class="px-3 py-2 text-amber-700">
                                                @if ($lessonIds->isEmpty())
                                                    -
                                                @else
                                                    @foreach ($lessonIds as $lessonId)
                                                        <a class="text-sky-600 hover:underline" target="_blank" href="{{ route('flash-cards.v2.lesson', ['lessonId' => $lessonId]) }}">
                                                            #{{ $lessonId }}
                                                        </a>@if (!$loop->last), @endif
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-amber-700">
                                                @if ($moduleIds->isEmpty())
                                                    -
                                                @else
                                                    @foreach ($moduleIds as $moduleId)
                                                        <a class="text-sky-600 hover:underline" target="_blank" href="{{ route('flash-cards.v2.module', ['moduleId' => $moduleId]) }}">
                                                            #{{ $moduleId }}
                                                        </a>@if (!$loop->last), @endif
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-amber-700">
                                                @if ($lessonIds->count() >= 2)
                                                    <button
                                                        type="button"
                                                        class="js-dup-ai-open rounded-lg border border-amber-300 bg-white px-2.5 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-50"
                                                        data-gpt-payload-b64="{{ base64_encode((string) json_encode($gptPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}"
                                                    >
                                                        Send to GPT
                                                    </button>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    @endif

                    <div class="mt-6 space-y-4">
                        @forelse (($modules ?? collect()) as $module)
                            @php
                                $moduleIsActive = (int) ($module->is_active ?? 0) === 1;
                                $moduleDetailUrl = route('flash-cards.v2.module', ['moduleId' => $module->id]);
                                $lessonsCount = (int) ($module->lessons_count ?? 0);
                                $hasLessons = $lessonsCount > 0;
                            @endphp
                            <section class="{{ $hasLessons ? 'rounded-2xl border border-slate-200 bg-white p-5' : 'rounded-2xl border border-red-300 bg-red-50 p-5' }}">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <a href="{{ $moduleDetailUrl }}" class="text-lg font-semibold text-slate-800 hover:text-sky-700 hover:underline">
                                            {{ $module->title ?? 'Untitled category' }}
                                        </a>
                                        <div class="mt-1 text-xs text-slate-500">
                                            ID: {{ $module->id ?? '-' }} · Slug: {{ $module->slug ?? '-' }} · Sort: {{ $module->sort_order ?? '-' }}
                                        </div>
                                        @if (trim((string) ($module->description ?? '')) !== '')
                                            <div class="mt-2 text-sm text-slate-600">{{ $module->description }}</div>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap gap-2 text-xs">
                                        <span class="{{ $moduleIsActive ? 'rounded-full border border-green-200 bg-green-50 px-3 py-1 font-semibold text-green-700' : 'rounded-full border border-red-200 bg-red-50 px-3 py-1 font-semibold text-red-700' }}">
                                            is_active: {{ $module->is_active ?? 0 }}
                                        </span>
                                        <span class="{{ $hasLessons ? 'rounded-full border border-slate-200 bg-slate-50 px-3 py-1 font-semibold text-slate-700' : 'rounded-full border border-red-300 bg-red-100 px-3 py-1 font-semibold text-red-700' }}">Lessons: {{ number_format($lessonsCount) }}</span>
                                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 font-semibold text-slate-700">Active lessons: {{ number_format((int) ($module->active_lessons_count ?? 0)) }}</span>
                                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 font-semibold text-slate-700">Words: {{ number_format((int) ($module->items_count ?? 0)) }}</span>
                                        @if (!$hasLessons)
                                            <span class="rounded-full border border-red-300 bg-red-100 px-3 py-1 font-semibold text-red-700">
                                                Missing lessons
                                            </span>
                                        @endif
                                        <a href="{{ $moduleDetailUrl }}" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-1 font-semibold text-slate-700 hover:bg-slate-50">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                            Open category
                                        </a>
                                    </div>
                                </div>

                                <div class="mt-4 grid gap-2 md:grid-cols-2">
                                    @forelse (($module->lessons ?? collect()) as $lesson)
                                        @php
                                            $lessonIsActive = (int) ($lesson->is_active ?? 0) === 1;
                                            $lessonDetailUrl = route('flash-cards.v2.lesson', ['lessonId' => $lesson->id]);
                                        @endphp
                                        <article class="rounded-lg border border-slate-200 p-3">
                                            <div class="flex flex-wrap items-start justify-between gap-2">
                                                <div class="min-w-0">
                                                    <a href="{{ $lessonDetailUrl }}" class="text-sm font-semibold text-slate-800 hover:text-sky-700 hover:underline">
                                                        {{ $lesson->title ?? 'Untitled lesson' }} (ID {{ $lesson->id ?? '-' }})
                                                    </a>
                                                    <a href="{{ $lessonDetailUrl }}" class="mt-1 block break-all text-xs text-slate-500 hover:text-sky-700 hover:underline">{{ $lesson->url ?? '-' }}</a>
                                                </div>
                                                <span class="{{ $lessonIsActive ? 'rounded-full border border-green-200 bg-green-50 px-2 py-0.5 text-[11px] font-semibold text-green-700' : 'rounded-full border border-red-200 bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-700' }}">
                                                    {{ $lesson->is_active ?? 0 }}
                                                </span>
                                            </div>
                                            <div class="mt-2 text-xs text-slate-500">
                                                Type: {{ $lesson->lesson_type ?? '-' }} · Level: {{ $lesson->level ?? '-' }} · Sort: {{ $lesson->sort_order ?? '-' }}
                                            </div>
                                            <div class="mt-3">
                                                <a href="{{ $lessonDetailUrl }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                                    Open lesson
                                                </a>
                                            </div>
                                        </article>
                                    @empty
                                        <div class="rounded-lg border border-red-300 bg-red-100 px-3 py-2 text-sm font-semibold text-red-700">
                                            No lessons for this category.
                                        </div>
                                    @endforelse
                                </div>
                            </section>
                        @empty
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 text-sm text-slate-500">
                                No categories found.
                            </div>
                        @endforelse
                    </div>
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
        <div id="dupGptModalOverlay" class="fixed inset-0 z-50 hidden bg-slate-900/50 p-4">
            <div class="mx-auto flex h-full max-w-5xl items-start justify-center pt-8">
                <div id="dupGptModal" class="w-full rounded-2xl border border-slate-200 bg-white shadow-xl">
                    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                        <div>
                            <h3 class="text-base font-semibold text-slate-800">Send duplicate row to GPT</h3>
                            <p class="text-xs text-slate-500">Preview data, confirm send, receive UPDATE SQL.</p>
                        </div>
                        <button id="dupGptCloseButton" type="button" class="rounded-lg border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">Close</button>
                    </div>

                    <div class="grid gap-4 p-4 lg:grid-cols-2">
                        <section class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <div class="mb-2 text-sm font-semibold text-slate-700">Request preview</div>
                            <pre id="dupGptPreviewText" class="max-h-96 overflow-auto whitespace-pre-wrap break-words rounded-lg border border-slate-200 bg-white p-3 text-xs text-slate-700">No data.</pre>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <button id="dupGptConfirmButton" type="button" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">Confirm send</button>
                                <span id="dupGptLoadingLabel" class="hidden text-xs text-slate-500">Processing...</span>
                            </div>
                        </section>

                        <section class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <div class="mb-2 text-sm font-semibold text-slate-700">GPT response</div>
                            <div id="dupGptResultMeta" class="mb-2 text-xs text-slate-600">No response yet.</div>
                            <textarea id="dupGptSqlOutput" class="h-48 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700" readonly></textarea>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <button id="dupGptCopySqlButton" type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Copy SQL</button>
                            </div>
                            <pre id="dupGptRawJson" class="mt-3 max-h-40 overflow-auto whitespace-pre-wrap break-words rounded-lg border border-slate-200 bg-white p-3 text-[11px] text-slate-600"></pre>
                        </section>
                    </div>
                </div>
            </div>
        </div>
        <script>
            (function () {
                const csrfToken = @json(csrf_token());
                const previewEndpoint = @json(route('flash-cards.v2.duplicates.gpt-preview'));
                const askEndpoint = @json(route('flash-cards.v2.duplicates.gpt-ask'));
                const overlay = document.getElementById('dupGptModalOverlay');
                const closeButton = document.getElementById('dupGptCloseButton');
                const previewText = document.getElementById('dupGptPreviewText');
                const confirmButton = document.getElementById('dupGptConfirmButton');
                const loadingLabel = document.getElementById('dupGptLoadingLabel');
                const resultMeta = document.getElementById('dupGptResultMeta');
                const sqlOutput = document.getElementById('dupGptSqlOutput');
                const rawJson = document.getElementById('dupGptRawJson');
                const copySqlButton = document.getElementById('dupGptCopySqlButton');
                let activePayload = null;

                if (!overlay || !previewText || !confirmButton) {
                    return;
                }

                if (window.toastr) {
                    toastr.options = {
                        closeButton: false,
                        debug: false,
                        newestOnTop: false,
                        progressBar: true,
                        positionClass: 'toast-top-right',
                        preventDuplicates: false,
                        onclick: null,
                        showDuration: '300',
                        hideDuration: '1000',
                        timeOut: '5000',
                        extendedTimeOut: '1000',
                        showEasing: 'swing',
                        hideEasing: 'linear',
                        showMethod: 'fadeIn',
                        hideMethod: 'fadeOut'
                    };
                }

                const showError = function (text) {
                    if (window.toastr) {
                        toastr['error'](text);
                        return;
                    }
                    console.error(text);
                };

                const showSuccess = function (text) {
                    if (window.toastr) {
                        toastr['success'](text);
                        return;
                    }
                    console.log(text);
                };

                const setLoading = function (isLoading) {
                    loadingLabel.classList.toggle('hidden', !isLoading);
                    confirmButton.disabled = isLoading;
                    confirmButton.classList.toggle('opacity-60', isLoading);
                };

                const postJson = function (url, payload) {
                    return fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify(payload)
                    }).then(function (response) {
                        return response.json().catch(function () {
                            return {};
                        }).then(function (json) {
                            if (!response.ok || !json.ok) {
                                throw new Error(json.message || ('Request failed (' + response.status + ')'));
                            }
                            return json;
                        });
                    });
                };

                const decodeBase64Utf8 = function (value) {
                    const binary = atob(value);
                    if (typeof TextDecoder !== 'undefined') {
                        const bytes = Uint8Array.from(binary, function (char) {
                            return char.charCodeAt(0);
                        });
                        return new TextDecoder().decode(bytes);
                    }

                    return decodeURIComponent(escape(binary));
                };

                const openModal = function () {
                    overlay.classList.remove('hidden');
                };

                const closeModal = function () {
                    overlay.classList.add('hidden');
                };

                const resetResultPanel = function () {
                    resultMeta.textContent = 'No response yet.';
                    sqlOutput.value = '';
                    rawJson.textContent = '';
                };

                document.querySelectorAll('.js-dup-ai-open').forEach(function (button) {
                    button.addEventListener('click', function () {
                        const payloadB64 = button.getAttribute('data-gpt-payload-b64') || '';
                        if (payloadB64 === '') {
                            showError('Missing payload.');
                            return;
                        }

                        let payload;
                        try {
                            payload = JSON.parse(decodeBase64Utf8(payloadB64));
                        } catch (error) {
                            showError('Invalid payload JSON.');
                            return;
                        }

                        activePayload = payload;
                        openModal();
                        resetResultPanel();
                        previewText.textContent = 'Loading preview...';
                        setLoading(true);

                        postJson(previewEndpoint, payload)
                            .then(function (json) {
                                previewText.textContent = JSON.stringify(json.preview || {}, null, 2);
                            })
                            .catch(function (error) {
                                previewText.textContent = 'Preview error: ' + (error && error.message ? error.message : 'Unknown error');
                                showError(error && error.message ? error.message : 'Preview failed.');
                            })
                            .finally(function () {
                                setLoading(false);
                            });
                    });
                });

                confirmButton.addEventListener('click', function () {
                    if (!activePayload) {
                        showError('No active payload.');
                        return;
                    }

                    resetResultPanel();
                    resultMeta.textContent = 'Waiting for GPT...';
                    setLoading(true);

                    postJson(askEndpoint, activePayload)
                        .then(function (json) {
                            const result = json.result || {};
                            resultMeta.textContent = 'model: ' + (result.model || '-') + ' | keep_item_id: ' + (result.keep_item_id || '-') + ' | update_item_id: ' + (result.update_item_id || '-');
                            sqlOutput.value = result.sql || '';
                            rawJson.textContent = JSON.stringify(result.raw_model_response || {}, null, 2);
                            showSuccess('GPT response received.');
                        })
                        .catch(function (error) {
                            resultMeta.textContent = 'Error';
                            rawJson.textContent = error && error.message ? error.message : 'Unknown error';
                            showError(error && error.message ? error.message : 'GPT request failed.');
                        })
                        .finally(function () {
                            setLoading(false);
                        });
                });

                if (copySqlButton) {
                    copySqlButton.addEventListener('click', function () {
                        if (!navigator.clipboard || !sqlOutput.value) {
                            return;
                        }

                        navigator.clipboard.writeText(sqlOutput.value)
                            .then(function () {
                                showSuccess('SQL copied.');
                            })
                            .catch(function () {
                                showError('Unable to copy SQL.');
                            });
                    });
                }

                if (closeButton) {
                    closeButton.addEventListener('click', closeModal);
                }

                overlay.addEventListener('click', function (event) {
                    if (event.target === overlay) {
                        closeModal();
                    }
                });
            })();
        </script>
    </body>
</html>
