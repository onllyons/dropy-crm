<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Flash-cards V2 Lesson" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Flash-cards V2 Lesson" />

                <main class="p-4 md:p-6">
                    @php
                        $lesson = $detail['lesson'] ?? null;
                        $copyRows = collect($detail['items'] ?? collect())
                            ->map(function ($item) {
                                $from = trim((string) ($item->text_from ?? ''));
                                $to = trim((string) ($item->text_to ?? ''));
                                if ($from === '' && $to === '') {
                                    return null;
                                }

                                return $from . ' - ' . $to;
                            })
                            ->filter()
                            ->values();
                        $copyText = 'Title lesson: ' . trim((string) ($lesson->title ?? '-'));
                        if ($copyRows->isNotEmpty()) {
                            $copyText .= PHP_EOL . $copyRows->implode(PHP_EOL);
                        }
                    @endphp

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold">{{ $detail['lesson']->title ?? 'Untitled lesson' }}</h1>
                                <p class="mt-2 text-sm text-slate-600">Toate item-urile din <code>flashcard_items</code> pentru această lecție.</p>
                        </div>
                        <a href="{{ route('flash-cards.v2') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            <i class="fa-solid fa-arrow-left"></i>
                            Back to Flash-cards V2
                        </a>
                    </div>
                </div>

                @if (!empty($error))
                    <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        {{ $error }}
                    </div>
                @endif

                    @if (!empty($detail['lesson']))
                        @php
                            $lesson = $detail['lesson'];
                            $lessonIsActive = (int) ($lesson->is_active ?? 0) === 1;
                            $moduleIsActive = (int) ($lesson->module_is_active ?? 0) === 1;
                        @endphp

                        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="grid gap-2 text-sm text-slate-700 md:grid-cols-2">
                                <div><span class="font-semibold">Lesson ID:</span> {{ $lesson->id ?? '-' }}</div>
                                <div><span class="font-semibold">Module ID:</span> {{ $lesson->module_id ?? '-' }}</div>
                                <div><span class="font-semibold">Module:</span> {{ $lesson->module_title ?? '-' }}</div>
                                <div><span class="font-semibold">Module slug:</span> {{ $lesson->module_slug ?? '-' }}</div>
                                <div><span class="font-semibold">URL:</span> <span class="break-all">{{ $lesson->url ?? '-' }}</span></div>
                                <div><span class="font-semibold">Type:</span> {{ $lesson->lesson_type ?? '-' }}</div>
                                <div><span class="font-semibold">Level:</span> {{ $lesson->level ?? '-' }}</div>
                                <div><span class="font-semibold">Sort:</span> {{ $lesson->sort_order ?? '-' }}</div>
                                <div>
                                    <span class="font-semibold">Lesson active:</span>
                                    <span class="{{ $lessonIsActive ? 'rounded-full border border-green-200 bg-green-50 px-2 py-0.5 text-xs font-semibold text-green-700' : 'rounded-full border border-red-200 bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700' }}">
                                        {{ $lesson->is_active ?? 0 }}
                                    </span>
                                </div>
                                <div>
                                    <span class="font-semibold">Module active:</span>
                                    <span class="{{ $moduleIsActive ? 'rounded-full border border-green-200 bg-green-50 px-2 py-0.5 text-xs font-semibold text-green-700' : 'rounded-full border border-red-200 bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700' }}">
                                        {{ $lesson->module_is_active ?? 0 }}
                                    </span>
                                </div>
                            </div>
                        </section>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="text-xs font-semibold text-slate-500">Total items</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((int) (($detail['summary']['items'] ?? 0))) }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="text-xs font-semibold text-slate-500">Items cu audio</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ number_format((int) (($detail['summary']['with_audio'] ?? 0))) }}</div>
                            </div>
                        </div>

                        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <h2 class="text-lg font-semibold text-slate-800">Items by type</h2>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @forelse (($detail['itemsByType'] ?? collect()) as $type => $count)
                                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700">
                                        {{ $type }}: {{ number_format((int) $count) }}
                                    </span>
                                @empty
                                    <span class="text-sm text-slate-500">No type data.</span>
                                @endforelse
                            </div>
                        </section>

                        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                                <h2 class="text-lg font-semibold text-slate-800">Items table</h2>
                                <div class="flex flex-wrap items-center gap-2">
                                    <button
                                        type="button"
                                        id="flashcards-lesson-copy-btn"
                                        class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                        data-copy-text="{{ e($copyText) }}"
                                    >
                                        <i class="fa-regular fa-copy"></i>
                                        Copy title + texts
                                    </button>
                                    <span id="flashcards-lesson-copy-feedback" class="text-xs text-slate-500 hidden">Copied!</span>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                        <tr>
                                            <th class="px-3 py-2 text-left">ID</th>
                                            <th class="px-3 py-2 text-left">Type</th>
                                            <th class="px-3 py-2 text-left">Text from</th>
                                            <th class="px-3 py-2 text-left">Text to</th>
                                            <th class="px-3 py-2 text-left">IPA</th>
                                            <th class="px-3 py-2 text-left">Audio</th>
                                            <th class="px-3 py-2 text-left">Created</th>
                                            <th class="px-3 py-2 text-left">Updated</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse (($detail['items'] ?? collect()) as $item)
                                            @php
                                                $textFrom = trim((string) ($item->text_from ?? ''));
                                                $textTo = trim((string) ($item->text_to ?? ''));
                                                $ipa = trim((string) ($item->ipa ?? ''));
                                                $updateUrl = route('flash-cards.v2.item.inline-update', ['itemId' => $item->id]);
                                            @endphp
                                            <tr class="align-top">
                                                <td class="px-3 py-2 font-semibold text-slate-800">{{ $item->id ?? '-' }}</td>
                                                <td class="px-3 py-2 text-slate-700">{{ $item->type ?? '-' }}</td>
                                                <td class="px-3 py-2 text-slate-700">
                                                    <button
                                                        type="button"
                                                        class="flashcards-inline-edit w-full rounded border border-transparent px-2 py-1 text-left hover:border-slate-200 hover:bg-slate-50"
                                                        data-field="text_from"
                                                        data-value="{{ e($textFrom) }}"
                                                        data-update-url="{{ $updateUrl }}"
                                                        title="Click to edit and click outside to save"
                                                    >
                                                        {{ $textFrom !== '' ? $textFrom : '-' }}
                                                    </button>
                                                </td>
                                                <td class="px-3 py-2 text-slate-700">
                                                    <button
                                                        type="button"
                                                        class="flashcards-inline-edit w-full rounded border border-transparent px-2 py-1 text-left hover:border-slate-200 hover:bg-slate-50"
                                                        data-field="text_to"
                                                        data-value="{{ e($textTo) }}"
                                                        data-update-url="{{ $updateUrl }}"
                                                        title="Click to edit and click outside to save"
                                                    >
                                                        {{ $textTo !== '' ? $textTo : '-' }}
                                                    </button>
                                                </td>
                                                <td class="px-3 py-2 text-slate-700">
                                                    <button
                                                        type="button"
                                                        class="flashcards-inline-edit w-full rounded border border-transparent px-2 py-1 text-left hover:border-slate-200 hover:bg-slate-50"
                                                        data-field="ipa"
                                                        data-value="{{ e($ipa) }}"
                                                        data-update-url="{{ $updateUrl }}"
                                                        title="Click to edit and click outside to save"
                                                    >
                                                        {{ $ipa !== '' ? $ipa : '-' }}
                                                    </button>
                                                </td>
                                                <td class="px-3 py-2 text-slate-700 break-all">{{ $item->audio ?? '-' }}</td>
                                                <td class="px-3 py-2 text-slate-700">{{ $item->created_at ?? '-' }}</td>
                                                <td class="px-3 py-2 text-slate-700">{{ $item->updated_at ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="px-3 py-8 text-center text-sm text-slate-500">No items for this lesson.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    @endif
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
        <script>
            (function () {
                const csrfToken = @json(csrf_token());
                const btn = document.querySelector('#flashcards-lesson-copy-btn');
                const feedback = document.querySelector('#flashcards-lesson-copy-feedback');

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

                const showSuccess = function (message) {
                    if (window.toastr) {
                        toastr['success'](message);
                        return;
                    }
                    console.log(message);
                };

                const showError = function (message) {
                    if (window.toastr) {
                        toastr['error'](message);
                        return;
                    }
                    console.error(message);
                };

                const applyEditedVisualState = function (button) {
                    if (!button) {
                        return;
                    }

                    if (button.dataset.edited === '1') {
                        button.classList.add('bg-slate-200', 'border-slate-300');
                        button.classList.remove('border-transparent');
                        return;
                    }

                    button.classList.remove('bg-slate-200', 'border-slate-300');
                    button.classList.add('border-transparent');
                };

                if (btn && navigator.clipboard) {
                    btn.addEventListener('click', function () {
                        const text = btn.getAttribute('data-copy-text');
                        if (!text) {
                            return;
                        }

                        navigator.clipboard.writeText(text)
                            .then(function () {
                                if (feedback) {
                                    feedback.textContent = 'Copiat!';
                                    feedback.classList.remove('hidden');
                                    setTimeout(function () {
                                        feedback.classList.add('hidden');
                                    }, 1200);
                                }
                            })
                            .catch(function (error) {
                                console.error(error);
                            });
                    });
                }

                const startInlineEdit = function (button) {
                    if (!button || button.dataset.editing === '1') {
                        return;
                    }

                    const updateUrl = button.dataset.updateUrl || '';
                    const field = button.dataset.field || '';
                    if (updateUrl === '' || (field !== 'text_from' && field !== 'text_to' && field !== 'ipa')) {
                        return;
                    }

                    const originalValue = button.dataset.value || '';
                    button.dataset.editing = '1';

                    const input = document.createElement('input');
                    input.type = 'text';
                    input.value = originalValue;
                    input.className = 'w-full rounded border border-slate-300 px-2 py-1 text-sm text-slate-700 outline-none focus:border-slate-400';
                    input.autocomplete = 'off';

                    button.replaceWith(input);
                    input.focus();
                    input.select();

                    let handled = false;

                    const restoreButton = function (nextValue) {
                        button.dataset.value = nextValue;
                        button.textContent = nextValue !== '' ? nextValue : '-';
                        button.dataset.editing = '0';
                        applyEditedVisualState(button);
                        input.replaceWith(button);
                    };

                    const cancelEdit = function () {
                        if (handled) {
                            return;
                        }
                        handled = true;
                        restoreButton(originalValue);
                    };

                    const saveEdit = function () {
                        if (handled) {
                            return;
                        }
                        handled = true;

                        const nextValue = input.value.trim();
                        if (nextValue === originalValue) {
                            restoreButton(originalValue);
                            return;
                        }

                        fetch(updateUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                field: field,
                                value: nextValue
                            })
                        })
                            .then(function (response) {
                                return response.json().catch(function () {
                                    return {};
                                }).then(function (json) {
                                    if (!response.ok || !json.ok) {
                                        throw new Error(json.message || 'Save failed.');
                                    }
                                    return json;
                                });
                            })
                            .then(function () {
                                button.dataset.edited = '1';
                                restoreButton(nextValue);
                                showSuccess('Saved.');
                            })
                            .catch(function (error) {
                                restoreButton(originalValue);
                                showError(error && error.message ? error.message : 'Save failed.');
                            });
                    };

                    input.addEventListener('blur', saveEdit);
                    input.addEventListener('keydown', function (event) {
                        if (event.key === 'Enter') {
                            event.preventDefault();
                            saveEdit();
                        }
                        if (event.key === 'Escape') {
                            event.preventDefault();
                            cancelEdit();
                        }
                    });
                };

                document.addEventListener('click', function (event) {
                    const button = event.target.closest('.flashcards-inline-edit');
                    if (!button) {
                        return;
                    }

                    event.preventDefault();
                    startInlineEdit(button);
                });

                document.querySelectorAll('.flashcards-inline-edit').forEach(function (button) {
                    applyEditedVisualState(button);
                });
            })();
        </script>
    </body>
</html>
