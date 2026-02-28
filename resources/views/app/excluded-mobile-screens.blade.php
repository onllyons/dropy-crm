<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Excluded Mobile Screens" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Excluded Mobile Screens" />

                <main class="p-4 md:p-6">
                    @php
                        $screenOptions = $screenOptions ?? [];
                        $uiLangOptions = $uiLangOptions ?? [];
                        $studyLangOptions = $studyLangOptions ?? [];
                        $rules = $rules ?? collect();
                        $hiddenScreens = $hiddenScreens ?? collect();
                        $canManage = !empty($screenOptions);
                    @endphp

                    @if (session('status'))
                        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (!empty($error))
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ $error }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            <div class="font-semibold">Validation error</div>
                            <ul class="mt-1 list-disc pl-4">
                                @foreach ($errors->all() as $validationError)
                                    <li>{{ $validationError }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold text-slate-900">Excluded Mobile Screens</h1>
                                <p class="mt-2 text-sm text-slate-600">Manage app screens hidden by language/study-language rules.</p>
                            </div>
                            <a class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="{{ url('/dropy-app') }}">
                                Back to Dropy: App
                                <i class="fa-solid fa-arrow-right text-slate-400"></i>
                            </a>
                        </div>
                        <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs text-slate-600">
                            <div class="font-semibold text-slate-700">Rule matching</div>
                            <div class="mt-1">(lang = currentLang AND studyLang = currentStudyLang) OR (lang = currentLang AND studyLang = '*') OR (lang = '*' AND studyLang = currentStudyLang)</div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-semibold text-slate-700">Preview for manager</div>
                        <div class="mt-1 text-xs text-slate-500">Check what screens will be hidden for a specific pair currentLang/currentStudyLang.</div>

                        <form method="get" action="{{ route('excluded-mobile-screens.index') }}" class="mt-4 grid gap-3 md:grid-cols-4">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">currentLang</label>
                                <select name="preview_lang" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-400 focus:outline-none" @disabled(empty($uiLangOptions))>
                                    @foreach ($uiLangOptions as $langOption)
                                        <option value="{{ $langOption }}" @selected(($previewLang ?? '') === $langOption)>{{ $langOption }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">studyLang</label>
                                <select name="preview_study_lang" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-400 focus:outline-none" @disabled(empty($studyLangOptions))>
                                    @foreach ($studyLangOptions as $studyLangOption)
                                        <option value="{{ $studyLangOption }}" @selected(($previewStudyLang ?? '') === $studyLangOption)>{{ $studyLangOption }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end">
                                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                    <input type="hidden" name="include_global" value="0" />
                                    <input class="rounded border-slate-300" type="checkbox" name="include_global" value="1" {{ !empty($includeGlobal) ? 'checked' : '' }} />
                                    Include global rule ('*','*')
                                </label>
                            </div>
                            <div class="flex items-end">
                                <button class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" type="submit" @disabled(empty($uiLangOptions) || empty($studyLangOptions))>
                                    Preview
                                </button>
                            </div>
                        </form>

                        <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Result</div>
                            @if (($previewLang ?? '') !== '' && ($previewStudyLang ?? '') !== '')
                                <div class="mt-1 text-sm text-slate-700">
                                    For currentLang=<span class="font-semibold">{{ $previewLang }}</span> and studyLang=<span class="font-semibold">{{ $previewStudyLang }}</span> hidden screens:
                                </div>
                                @if ($hiddenScreens->isEmpty())
                                    <div class="mt-2 text-sm text-slate-500">No screens will be hidden.</div>
                                @else
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach ($hiddenScreens as $screenName)
                                            <span class="inline-flex items-center rounded-full border border-sky-200 bg-sky-50 px-2 py-1 text-xs font-semibold text-sky-700">{{ $screenName }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            @else
                                <div class="mt-2 text-sm text-slate-500">Choose currentLang and studyLang to preview.</div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-semibold text-slate-700">Add rule</div>
                        <div class="mt-1 text-xs text-slate-500">Manager UI for table <span class="font-semibold">excluded_mobile_screens</span>.</div>

                        @if (!$canManage)
                            <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-700">
                                Missing valid screen options. Make sure table <span class="font-semibold">visitorBehaviorAnalyticsApp</span> has screen values or existing rules are present.
                            </div>
                        @else
                            <form method="post" action="{{ route('excluded-mobile-screens.store') }}" class="mt-4 grid gap-3 md:grid-cols-4">
                                @csrf
                                <input type="hidden" name="form_mode" value="create" />
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">screen</label>
                                    <select name="screen" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-400 focus:outline-none" required>
                                        @foreach ($screenOptions as $screenOption)
                                            <option value="{{ $screenOption }}" @selected(old('form_mode') === 'create' && old('screen') === $screenOption)>{{ $screenOption }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">lang <span class="text-slate-400" title="lang='*' means all UI languages">(?)</span></label>
                                    <select name="lang" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-400 focus:outline-none" required>
                                        <option value="*" @selected(old('form_mode') === 'create' ? old('lang', '*') === '*' : true)>Any (*)</option>
                                        @foreach ($uiLangOptions as $langOption)
                                            <option value="{{ $langOption }}" @selected(old('form_mode') === 'create' && old('lang') === $langOption)>{{ $langOption }}</option>
                                        @endforeach
                                    </select>
                                    <div class="mt-1 text-xs text-slate-500">lang='*' = all UI languages</div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">studyLang <span class="text-slate-400" title="studyLang='*' means all study languages">(?)</span></label>
                                    <select name="studyLang" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-400 focus:outline-none" required>
                                        <option value="*" @selected(old('form_mode') === 'create' ? old('studyLang', '*') === '*' : true)>Any (*)</option>
                                        @foreach ($studyLangOptions as $studyLangOption)
                                            <option value="{{ $studyLangOption }}" @selected(old('form_mode') === 'create' && old('studyLang') === $studyLangOption)>{{ $studyLangOption }}</option>
                                        @endforeach
                                    </select>
                                    <div class="mt-1 text-xs text-slate-500">studyLang='*' = all study languages</div>
                                </div>
                                <div class="flex items-end">
                                    <button class="inline-flex items-center rounded-lg border border-sky-600 bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-700" type="submit">
                                        Add rule
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-semibold text-slate-700">Rules list</div>
                        <div class="mt-1 text-xs text-slate-500">Columns: screen, lang, studyLang.</div>

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-slate-500">
                                        <th class="pb-2 pr-3">Screen</th>
                                        <th class="pb-2 pr-3">Lang</th>
                                        <th class="pb-2 pr-3">StudyLang</th>
                                        <th class="pb-2 pr-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($rules as $rule)
                                        <tr>
                                            <td class="py-2 pr-3 font-semibold text-slate-700">{{ $rule->screen }}</td>
                                            <td class="py-2 pr-3 text-slate-600">
                                                @if ($rule->lang === '*')
                                                    <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">Any (*)</span>
                                                @else
                                                    {{ $rule->lang }}
                                                @endif
                                            </td>
                                            <td class="py-2 pr-3 text-slate-600">
                                                @if ($rule->studyLang === '*')
                                                    <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">Any (*)</span>
                                                @else
                                                    {{ $rule->studyLang }}
                                                @endif
                                            </td>
                                            <td class="py-2 pr-3">
                                                <div class="flex items-center gap-2">
                                                    <button
                                                        type="button"
                                                        class="js-open-edit inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100"
                                                        data-id="{{ (int) $rule->id }}"
                                                        data-update-url="{{ route('excluded-mobile-screens.update', ['id' => (int) $rule->id]) }}"
                                                        data-screen="{{ $rule->screen }}"
                                                        data-lang="{{ $rule->lang }}"
                                                        data-study-lang="{{ $rule->studyLang }}"
                                                    >
                                                        Edit
                                                    </button>
                                                    <form method="post" action="{{ route('excluded-mobile-screens.destroy', ['id' => (int) $rule->id]) }}" class="js-delete-rule">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-3 text-slate-500">No rules in excluded_mobile_screens.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if ($canManage)
                        <div id="editRuleModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
                            <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-5 shadow-lg">
                                <div class="mb-4 flex items-center justify-between gap-3">
                                    <div class="text-base font-semibold text-slate-800">Edit rule</div>
                                    <button type="button" class="js-close-edit-modal rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">Close</button>
                                </div>

                                <form id="editRuleForm" method="post" action="{{ url('/excluded-mobile-screens/__ID__') }}" data-action-template="{{ url('/excluded-mobile-screens/__ID__') }}" class="space-y-3">
                                    @csrf
                                    <input type="hidden" name="form_mode" value="update" />
                                    <input id="edit_id" type="hidden" name="edit_id" value="" />

                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">screen</label>
                                        <select id="edit_screen" name="screen" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-400 focus:outline-none" required>
                                            @foreach ($screenOptions as $screenOption)
                                                <option value="{{ $screenOption }}">{{ $screenOption }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">lang</label>
                                        <select id="edit_lang" name="lang" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-400 focus:outline-none" required>
                                            <option value="*">Any (*)</option>
                                            @foreach ($uiLangOptions as $langOption)
                                                <option value="{{ $langOption }}">{{ $langOption }}</option>
                                            @endforeach
                                        </select>
                                        <div class="mt-1 text-xs text-slate-500">lang='*' = all UI languages</div>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">studyLang</label>
                                        <select id="edit_study_lang" name="studyLang" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-400 focus:outline-none" required>
                                            <option value="*">Any (*)</option>
                                            @foreach ($studyLangOptions as $studyLangOption)
                                                <option value="{{ $studyLangOption }}">{{ $studyLangOption }}</option>
                                            @endforeach
                                        </select>
                                        <div class="mt-1 text-xs text-slate-500">studyLang='*' = all study languages</div>
                                    </div>

                                    <div class="pt-2">
                                        <button class="inline-flex items-center rounded-lg border border-sky-600 bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-700" type="submit">
                                            Save changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </main>
            </div>
        </div>

        <x-script-components />
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.js-delete-rule').forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!window.confirm('Delete this rule?')) {
                            event.preventDefault();
                        }
                    });
                });

                var modal = document.getElementById('editRuleModal');
                var closeModalButton = document.querySelector('.js-close-edit-modal');
                var editForm = document.getElementById('editRuleForm');
                var editIdField = document.getElementById('edit_id');
                var editScreen = document.getElementById('edit_screen');
                var editLang = document.getElementById('edit_lang');
                var editStudyLang = document.getElementById('edit_study_lang');

                function openModal() {
                    if (!modal) {
                        return;
                    }
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }

                function closeModal() {
                    if (!modal) {
                        return;
                    }
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }

                if (closeModalButton) {
                    closeModalButton.addEventListener('click', function () {
                        closeModal();
                    });
                }

                if (modal) {
                    modal.addEventListener('click', function (event) {
                        if (event.target === modal) {
                            closeModal();
                        }
                    });
                }

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        closeModal();
                    }
                });

                document.querySelectorAll('.js-open-edit').forEach(function (button) {
                    button.addEventListener('click', function () {
                        if (!editForm) {
                            return;
                        }

                        var id = button.getAttribute('data-id') || '';
                        var updateUrl = button.getAttribute('data-update-url') || '';
                        var screenValue = button.getAttribute('data-screen') || '';
                        var langValue = button.getAttribute('data-lang') || '';
                        var studyLangValue = button.getAttribute('data-study-lang') || '';

                        if (updateUrl) {
                            editForm.setAttribute('action', updateUrl);
                        }
                        if (editIdField) {
                            editIdField.value = id;
                        }
                        if (editScreen) {
                            editScreen.value = screenValue;
                        }
                        if (editLang) {
                            editLang.value = langValue;
                        }
                        if (editStudyLang) {
                            editStudyLang.value = studyLangValue;
                        }

                        openModal();
                    });
                });

                var oldFormMode = @json(old('form_mode'));
                var oldEditId = @json(old('edit_id'));
                if (oldFormMode === 'update' && oldEditId && editForm) {
                    var actionTemplate = editForm.getAttribute('data-action-template') || '';
                    if (actionTemplate !== '') {
                        editForm.setAttribute('action', actionTemplate.replace('__ID__', String(oldEditId)));
                    }
                    if (editIdField) {
                        editIdField.value = String(oldEditId);
                    }
                    if (editScreen) {
                        editScreen.value = @json(old('screen'));
                    }
                    if (editLang) {
                        editLang.value = @json(old('lang'));
                    }
                    if (editStudyLang) {
                        editStudyLang.value = @json(old('studyLang'));
                    }
                    openModal();
                }
             });
         </script>
         <x-offcanvas-right />
     </body>
 </html>
