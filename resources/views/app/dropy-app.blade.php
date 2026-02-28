<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Dropy: App" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Dropy: App" />

                <main class="p-4 md:p-6">
                    @php
                        $prMapTableExists = !empty($prMapSchema['table_exists']);
                        $prColumns = $prMapSchema['columns'] ?? [];
                        $prMapCanManage = $prMapTableExists
                            && !empty($prColumns['id'])
                            && !empty($prColumns['pr_code'])
                            && !empty($prColumns['blogger_name']);
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

                    @if ($errors->any())
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            <div class="font-semibold">Validation error</div>
                            <ul class="mt-1 list-disc pl-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h1 class="text-2xl font-semibold">Dropy: App</h1>
                        <p class="mt-2 text-sm text-slate-600">Template page for Dropy app information.</p>

                        <div class="mt-5">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Menu</div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <a class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="{{ url('/structure-in-app') }}">
                                    Structure in app
                                    <i class="fa-solid fa-arrow-right text-slate-400"></i>
                                </a>
                                <a class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="{{ url('/app-update-info') }}">
                                    App update info
                                    <i class="fa-solid fa-arrow-right text-slate-400"></i>
                                </a>
                                <a class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="{{ url('/excluded-mobile-screens') }}">
                                    excluded mobile screens
                                    <i class="fa-solid fa-arrow-right text-slate-400"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-semibold text-slate-700">Download link clicks (website -&gt; app)</div>
                        <div class="mt-1 text-xs text-slate-500">Table: <span class="font-semibold">clickOfApp</span> | Column date source: <span class="font-semibold">timedate</span></div>

                        @if (!empty($clickStatsError))
                            <div class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                {{ $clickStatsError }}
                            </div>
                        @endif

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Today ({{ $clickStats['today_date'] ?? now()->toDateString() }})</div>
                                <div class="mt-2 text-2xl font-semibold text-slate-800">{{ number_format((int) ($clickStats['today_rows'] ?? 0)) }}</div>
                                <div class="mt-1 text-xs text-slate-500">Rows today</div>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">All Time</div>
                                <div class="mt-2 text-2xl font-semibold text-slate-800">{{ number_format((int) ($clickStats['total_rows'] ?? 0)) }}</div>
                                <div class="mt-1 text-xs text-slate-500">Total rows</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-slate-700">PR mapping manager (insert + edit, delete)</div>
                                <div class="mt-1 text-xs text-slate-500">Table: <span class="font-semibold">blogger_pr_map</span></div>
                            </div>
                            @if ($prMapCanManage)
                                <button id="openPrMapCreateModal" type="button" class="inline-flex items-center rounded-lg border border-sky-600 bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-700">
                                    Add mapping
                                </button>
                            @endif
                        </div>

                        @if (!empty($prMapRowsError))
                            <div class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                {{ $prMapRowsError }}
                            </div>
                        @endif

                        @if (!$prMapTableExists)
                            <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-700">
                                Table <span class="font-semibold">blogger_pr_map</span> does not exist in tenant DB.
                            </div>
                        @elseif (!$prMapCanManage)
                            <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-700">
                                Missing required columns in <span class="font-semibold">blogger_pr_map</span>. Required: <span class="font-semibold">id, pr_code, blogger_name</span>.
                            </div>
                        @else
                            <div class="mt-4 overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-slate-200 text-left text-slate-500">
                                            <th class="pb-2 pr-3">ID</th>
                                            <th class="pb-2 pr-3">PR code</th>
                                            <th class="pb-2 pr-3">Blogger</th>
                                            @if (!empty($prColumns['campaign_name']))
                                                <th class="pb-2 pr-3">Campaign</th>
                                            @endif
                                            @if (!empty($prColumns['platform']))
                                                <th class="pb-2 pr-3">Platform</th>
                                            @endif
                                            @if (!empty($prColumns['profile_url']))
                                                <th class="pb-2 pr-3">Profile URL</th>
                                            @endif
                                            @if (!empty($prColumns['is_active']))
                                                <th class="pb-2 pr-3">Status</th>
                                            @endif
                                            <th class="pb-2 pr-3">Updated</th>
                                            <th class="pb-2 pr-3">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse (($prMapRows ?? collect()) as $row)
                                            <tr>
                                                <td class="py-2 pr-3 text-slate-600">{{ $row->id ?? '-' }}</td>
                                                <td class="py-2 pr-3 font-semibold text-slate-700">{{ $row->pr_code ?? '-' }}</td>
                                                <td class="py-2 pr-3 text-slate-600">{{ $row->blogger_name ?? '-' }}</td>
                                                @if (!empty($prColumns['campaign_name']))
                                                    <td class="py-2 pr-3 text-slate-600">{{ $row->campaign_name ?? '-' }}</td>
                                                @endif
                                                @if (!empty($prColumns['platform']))
                                                    <td class="py-2 pr-3 text-slate-600">{{ $row->platform ?? '-' }}</td>
                                                @endif
                                                @if (!empty($prColumns['profile_url']))
                                                    <td class="py-2 pr-3 text-slate-600">
                                                        @if (!empty($row->profile_url))
                                                            <a class="inline-block max-w-[220px] truncate text-sky-600 hover:underline" href="{{ $row->profile_url }}" target="_blank" rel="noopener noreferrer" title="{{ $row->profile_url }}">
                                                                {{ $row->profile_url }}
                                                            </a>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                @endif
                                                @if (!empty($prColumns['is_active']))
                                                    <td class="py-2 pr-3">
                                                        @if ((int) ($row->is_active ?? 0) === 1)
                                                            <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">Active</span>
                                                        @else
                                                            <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">Inactive</span>
                                                        @endif
                                                    </td>
                                                @endif
                                                <td class="py-2 pr-3 text-slate-600">{{ $row->updated_at ?? '-' }}</td>
                                                <td class="py-2 pr-3">
                                                    <div class="flex items-center gap-2">
                                                        <button
                                                            type="button"
                                                            class="js-pr-edit-btn inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100"
                                                            data-update-url="{{ route('dropy-app.pr-map.update', ['id' => (int) ($row->id ?? 0)]) }}"
                                                            data-id="{{ $row->id ?? '' }}"
                                                            data-pr-code="{{ $row->pr_code ?? '' }}"
                                                            data-blogger-name="{{ $row->blogger_name ?? '' }}"
                                                            data-campaign-name="{{ $row->campaign_name ?? '' }}"
                                                            data-platform="{{ $row->platform ?? '' }}"
                                                            data-profile-url="{{ $row->profile_url ?? '' }}"
                                                            data-links-json="{{ $row->links_json ?? '' }}"
                                                            data-notes="{{ $row->notes ?? '' }}"
                                                            data-is-active="{{ isset($row->is_active) ? (int) $row->is_active : 1 }}"
                                                        >
                                                            Edit
                                                        </button>
                                                        <form method="post" action="{{ route('dropy-app.pr-map.destroy', ['id' => (int) ($row->id ?? 0)]) }}" class="js-pr-delete-form">
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
                                                <td colspan="9" class="py-3 text-slate-500">No mapping rows found in blogger_pr_map.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-semibold text-slate-700">PR activity grouped by code (`pr=`)</div>
                        <div class="mt-1 text-xs text-slate-500">
                            Table: <span class="font-semibold">emailAtiveClick</span> | Grouped by extracted query param <span class="font-semibold">pr</span>
                        </div>

                        @if (!empty($emailActiveClickPrGroupsError))
                            <div class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                {{ $emailActiveClickPrGroupsError }}
                            </div>
                        @endif

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-slate-500">
                                        <th class="pb-2 pr-3">PR code</th>
                                        <th class="pb-2 pr-3">Rows</th>
                                        <th class="pb-2 pr-3">Unknown users</th>
                                        <th class="pb-2 pr-3">Blogger</th>
                                        <th class="pb-2 pr-3">Campaign</th>
                                        <th class="pb-2 pr-3">Platform</th>
                                        <th class="pb-2 pr-3">Map status</th>
                                        <th class="pb-2 pr-3">Last time</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse (($emailActiveClickPrGroups ?? collect()) as $row)
                                        <tr>
                                            <td class="py-2 pr-3 font-semibold text-slate-700">{{ $row->pr_code ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ number_format((int) ($row->rows_count ?? 0)) }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ number_format((int) ($row->unknown_users ?? 0)) }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->blogger_name ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->campaign_name ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->platform ?? '-' }}</td>
                                            <td class="py-2 pr-3">
                                                @if ((int) ($row->map_found ?? 0) === 1)
                                                    @if (($row->is_active ?? null) === null)
                                                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-600">Mapped</span>
                                                    @elseif ((int) $row->is_active === 1)
                                                        <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">Active</span>
                                                    @else
                                                        <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">Inactive</span>
                                                    @endif
                                                @else
                                                    <span class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-2 py-1 text-xs font-semibold text-red-700">Unmapped</span>
                                                @endif
                                            </td>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->last_time ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="py-3 text-slate-500">No rows with `pr=` found in emailAtiveClick.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if ($prMapCanManage)
                        <div id="prMapCreateModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
                            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-5 shadow-lg">
                                <div class="mb-4 flex items-center justify-between gap-3">
                                    <div class="text-base font-semibold text-slate-800">Add PR mapping</div>
                                    <button type="button" class="js-modal-close rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-100" data-modal-id="prMapCreateModal">
                                        Close
                                    </button>
                                </div>
                                <form class="space-y-3" method="post" action="{{ route('dropy-app.pr-map.store') }}">
                                    @csrf
                                    <input type="hidden" name="form_mode" value="create" />
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">PR code</label>
                                        <input id="create_pr_code" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none" type="text" name="pr_code" value="{{ old('pr_code') }}" placeholder="ex: 8 or 42" pattern="[1-9][0-9]{0,5}" inputmode="numeric" maxlength="6" autocomplete="off" required />
                                        <div class="mt-2 rounded-lg border border-slate-200 bg-slate-50 p-3">
                                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Generated links</div>
                                            <div class="mt-2 space-y-2">
                                                <div class="flex items-center gap-2">
                                                    <input id="create_course_url" class="w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs text-slate-700 focus:border-slate-400 focus:outline-none" type="text" readonly value="https://www.language.onllyons.com/course/?pr=" />
                                                    <button type="button" class="js-copy-pr-url inline-flex items-center rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100" data-target-id="create_course_url">Copy</button>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <input id="create_app_url" class="w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs text-slate-700 focus:border-slate-400 focus:outline-none" type="text" readonly value="https://www.language.onllyons.com/app/?pr=" />
                                                    <button type="button" class="js-copy-pr-url inline-flex items-center rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100" data-target-id="create_app_url">Copy</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Blogger name</label>
                                        <input class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none" type="text" name="blogger_name" value="{{ old('blogger_name') }}" placeholder="ex: Ana Popescu" required />
                                    </div>
                                    @if (!empty($prColumns['campaign_name']))
                                        <div>
                                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Campaign name</label>
                                            <input class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none" type="text" name="campaign_name" value="{{ old('campaign_name') }}" placeholder="ex: Spring 2026 Promo" />
                                        </div>
                                    @endif
                                    @if (!empty($prColumns['platform']))
                                        <div>
                                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Platform</label>
                                            <input class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none" type="text" name="platform" value="{{ old('platform') }}" placeholder="ex: instagram / tiktok / youtube" />
                                        </div>
                                    @endif
                                    @if (!empty($prColumns['profile_url']))
                                        <div>
                                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Main profile URL</label>
                                            <input class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none" type="text" name="profile_url" value="{{ old('profile_url') }}" placeholder="ex: https://instagram.com/ana" />
                                        </div>
                                    @endif
                                    @if (!empty($prColumns['links_json']))
                                        <div>
                                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Links</label>
                                            <div id="create_links_rows" class="space-y-2"></div>
                                            <button id="create_add_link" type="button" class="mt-2 inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                                + Add link
                                            </button>
                                            <input id="create_links_json" type="hidden" name="links_json" value="{{ old('links_json') }}" />
                                        </div>
                                    @endif
                                    @if (!empty($prColumns['notes']))
                                        <div>
                                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Notes</label>
                                            <textarea class="h-20 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none" name="notes" placeholder="ex: personal account + business account, CPA deal">{{ old('notes') }}</textarea>
                                        </div>
                                    @endif
                                    @if (!empty($prColumns['is_active']))
                                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                            <input class="rounded border-slate-300" type="checkbox" name="is_active" value="1" checked />
                                            Active
                                        </label>
                                    @endif
                                    <div class="pt-2">
                                        <button class="inline-flex items-center rounded-lg border border-sky-600 bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-700" type="submit">
                                            Add mapping
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div id="prMapEditModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
                            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-5 shadow-lg">
                                <div class="mb-4 flex items-center justify-between gap-3">
                                    <div class="text-base font-semibold text-slate-800">Edit PR mapping</div>
                                    <button type="button" class="js-modal-close rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-100" data-modal-id="prMapEditModal">
                                        Close
                                    </button>
                                </div>
                                <form id="prMapUpdateForm" class="space-y-3" method="post" action="{{ url('/dropy-app/pr-map/__ID__') }}" data-action-template="{{ url('/dropy-app/pr-map/__ID__') }}">
                                    @csrf
                                    <input type="hidden" name="form_mode" value="update" />
                                    <input id="update_edit_id" type="hidden" name="edit_id" value="" />
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">PR code</label>
                                        <input id="update_pr_code" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none" type="text" name="pr_code" value="" placeholder="ex: 8 or 42" pattern="[1-9][0-9]{0,5}" inputmode="numeric" maxlength="6" autocomplete="off" required />
                                        <div class="mt-2 rounded-lg border border-slate-200 bg-slate-50 p-3">
                                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Generated links</div>
                                            <div class="mt-2 space-y-2">
                                                <div class="flex items-center gap-2">
                                                    <input id="update_course_url" class="w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs text-slate-700 focus:border-slate-400 focus:outline-none" type="text" readonly value="https://www.language.onllyons.com/course/?pr=" />
                                                    <button type="button" class="js-copy-pr-url inline-flex items-center rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100" data-target-id="update_course_url">Copy</button>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <input id="update_app_url" class="w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs text-slate-700 focus:border-slate-400 focus:outline-none" type="text" readonly value="https://www.language.onllyons.com/app/?pr=" />
                                                    <button type="button" class="js-copy-pr-url inline-flex items-center rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100" data-target-id="update_app_url">Copy</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Blogger name</label>
                                        <input id="update_blogger_name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none" type="text" name="blogger_name" value="" placeholder="ex: Ana Popescu" required />
                                    </div>
                                    @if (!empty($prColumns['campaign_name']))
                                        <div>
                                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Campaign name</label>
                                            <input id="update_campaign_name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none" type="text" name="campaign_name" value="" placeholder="ex: Spring 2026 Promo" />
                                        </div>
                                    @endif
                                    @if (!empty($prColumns['platform']))
                                        <div>
                                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Platform</label>
                                            <input id="update_platform" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none" type="text" name="platform" value="" placeholder="ex: instagram / tiktok / youtube" />
                                        </div>
                                    @endif
                                    @if (!empty($prColumns['profile_url']))
                                        <div>
                                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Main profile URL</label>
                                            <input id="update_profile_url" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none" type="text" name="profile_url" value="" placeholder="ex: https://instagram.com/ana" />
                                        </div>
                                    @endif
                                    @if (!empty($prColumns['links_json']))
                                        <div>
                                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Links</label>
                                            <div id="update_links_rows" class="space-y-2"></div>
                                            <button id="update_add_link" type="button" class="mt-2 inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                                + Add link
                                            </button>
                                            <input id="update_links_json" type="hidden" name="links_json" value="" />
                                        </div>
                                    @endif
                                    @if (!empty($prColumns['notes']))
                                        <div>
                                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Notes</label>
                                            <textarea id="update_notes" class="h-20 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none" name="notes" placeholder="ex: personal account + business account, CPA deal"></textarea>
                                        </div>
                                    @endif
                                    @if (!empty($prColumns['is_active']))
                                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                            <input id="update_is_active" class="rounded border-slate-300" type="checkbox" name="is_active" value="1" checked />
                                            Active
                                        </label>
                                    @endif
                                    <div class="pt-2">
                                        <button id="pr-map-update-submit" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" type="submit">
                                            Save changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-semibold text-slate-700">Grouped URLs from blogger activity</div>
                        <div class="mt-1 text-xs text-slate-500">
                            Table: <span class="font-semibold">emailAtiveClick</span> | Grouped by normalized <span class="font-semibold">urlPage</span> (without <span class="font-semibold">https://www.language.onllyons.com</span>)
                        </div>

                        @if (!empty($emailActiveClickGroupsError))
                            <div class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                {{ $emailActiveClickGroupsError }}
                            </div>
                        @endif

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-slate-500">
                                        <th class="pb-2 pr-3 w-[200px]">URL (normalized)</th>
                                        <th class="pb-2 pr-3">Rows</th>
                                        <th class="pb-2 pr-3">Unknown users (user_id=0)</th>
                                        <th class="pb-2 pr-3">First time</th>
                                        <th class="pb-2 pr-3">Last time</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse (($emailActiveClickGroups ?? collect()) as $row)
                                        <tr>
                                            <td class="py-2 pr-3 text-slate-700">
                                                <div class="max-w-[200px] truncate" title="{{ $row->grouped_url ?? '/' }}">
                                                    {{ $row->grouped_url ?? '/' }}
                                                </div>
                                            </td>
                                            <td class="py-2 pr-3 text-slate-600">{{ number_format((int) ($row->rows_count ?? 0)) }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ number_format((int) ($row->unknown_users ?? 0)) }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->first_time ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->last_time ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-3 text-slate-500">No rows found in emailAtiveClick.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                </main>
            </div>
        </div>

        <x-script-components />
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var createModal = document.getElementById('prMapCreateModal');
                var editModal = document.getElementById('prMapEditModal');
                var openCreateButton = document.getElementById('openPrMapCreateModal');
                var createPrCodeInput = document.getElementById('create_pr_code');
                var updatePrCodeInput = document.getElementById('update_pr_code');
                var createLinksRows = document.getElementById('create_links_rows');
                var updateLinksRows = document.getElementById('update_links_rows');
                var createLinksJsonField = document.getElementById('create_links_json');
                var updateLinksJsonField = document.getElementById('update_links_json');
                var createAddLinkButton = document.getElementById('create_add_link');
                var updateAddLinkButton = document.getElementById('update_add_link');
                var updateForm = document.getElementById('prMapUpdateForm');
                var actionTemplate = updateForm ? (updateForm.getAttribute('data-action-template') || '') : '';
                var publicBaseUrl = 'https://www.language.onllyons.com';

                function openModal(modal) {
                    if (!modal) {
                        return;
                    }
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }

                function closeModal(modal) {
                    if (!modal) {
                        return;
                    }
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }

                function sanitizePrCodeRaw(value) {
                    var digitsOnly = String(value || '').replace(/[^0-9]/g, '');
                    var withoutLeadingZero = digitsOnly.replace(/^0+/, '');
                    return withoutLeadingZero.slice(0, 6);
                }

                function normalizePrCode(value) {
                    return sanitizePrCodeRaw(value);
                }

                function buildSharedUrl(path, prCode) {
                    var cleanCode = normalizePrCode(prCode);
                    return publicBaseUrl + path + '?pr=' + encodeURIComponent(cleanCode);
                }

                function refreshGeneratedUrls(prCode, prefix) {
                    var courseField = document.getElementById(prefix + '_course_url');
                    var appField = document.getElementById(prefix + '_app_url');
                    if (courseField) {
                        courseField.value = buildSharedUrl('/course/', prCode);
                    }
                    if (appField) {
                        appField.value = buildSharedUrl('/app/', prCode);
                    }
                }

                function copyText(value) {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        return navigator.clipboard.writeText(value);
                    }

                    var temp = document.createElement('textarea');
                    temp.value = value;
                    temp.setAttribute('readonly', 'readonly');
                    temp.style.position = 'absolute';
                    temp.style.left = '-9999px';
                    document.body.appendChild(temp);
                    temp.select();
                    document.execCommand('copy');
                    document.body.removeChild(temp);
                    return Promise.resolve();
                }

                function getLinksContainer(prefix) {
                    return prefix === 'create' ? createLinksRows : updateLinksRows;
                }

                function getLinksHiddenField(prefix) {
                    return prefix === 'create' ? createLinksJsonField : updateLinksJsonField;
                }

                function parseLinksJson(raw) {
                    if (!raw) {
                        return [];
                    }

                    try {
                        var parsed = JSON.parse(raw);
                        if (!parsed || typeof parsed !== 'object') {
                            return [];
                        }

                        if (Array.isArray(parsed)) {
                            return parsed
                                .map(function (item) {
                                    if (!item || typeof item !== 'object') {
                                        return null;
                                    }
                                    var key = String(item.key || '').trim();
                                    var url = String(item.url || '').trim();
                                    return key || url ? { key: key, url: url } : null;
                                })
                                .filter(Boolean);
                        }

                        return Object.keys(parsed).map(function (key) {
                            return {
                                key: String(key).trim(),
                                url: String(parsed[key] || '').trim(),
                            };
                        }).filter(function (item) {
                            return item.key || item.url;
                        });
                    } catch (e) {
                        return [];
                    }
                }

                function syncLinksJson(prefix) {
                    var container = getLinksContainer(prefix);
                    var hiddenField = getLinksHiddenField(prefix);
                    if (!container || !hiddenField) {
                        return;
                    }

                    var rows = container.querySelectorAll('[data-link-row]');
                    var obj = {};
                    rows.forEach(function (row) {
                        var keyInput = row.querySelector('[data-link-key]');
                        var urlInput = row.querySelector('[data-link-url]');
                        var key = keyInput ? String(keyInput.value || '').trim() : '';
                        var url = urlInput ? String(urlInput.value || '').trim() : '';

                        if (key !== '' && url !== '') {
                            obj[key] = url;
                        }
                    });

                    hiddenField.value = Object.keys(obj).length > 0 ? JSON.stringify(obj) : '';
                }

                function appendLinkRow(prefix, keyValue, urlValue) {
                    var container = getLinksContainer(prefix);
                    if (!container) {
                        return;
                    }

                    var row = document.createElement('div');
                    row.className = 'flex items-center gap-2';
                    row.setAttribute('data-link-row', '1');

                    var keyInput = document.createElement('input');
                    keyInput.type = 'text';
                    keyInput.placeholder = 'key (ex: instagram)';
                    keyInput.className = 'w-48 rounded-lg border border-slate-300 px-2 py-1.5 text-xs focus:border-slate-400 focus:outline-none';
                    keyInput.setAttribute('data-link-key', '1');
                    keyInput.value = keyValue || '';

                    var urlInput = document.createElement('input');
                    urlInput.type = 'text';
                    urlInput.placeholder = 'URL (ex: https://...)';
                    urlInput.className = 'w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs focus:border-slate-400 focus:outline-none';
                    urlInput.setAttribute('data-link-url', '1');
                    urlInput.value = urlValue || '';

                    var removeButton = document.createElement('button');
                    removeButton.type = 'button';
                    removeButton.className = 'inline-flex items-center rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100';
                    removeButton.textContent = '-';

                    keyInput.addEventListener('input', function () {
                        syncLinksJson(prefix);
                    });
                    urlInput.addEventListener('input', function () {
                        syncLinksJson(prefix);
                    });
                    removeButton.addEventListener('click', function () {
                        row.remove();
                        if (container.querySelectorAll('[data-link-row]').length === 0) {
                            appendLinkRow(prefix, '', '');
                        }
                        syncLinksJson(prefix);
                    });

                    row.appendChild(keyInput);
                    row.appendChild(urlInput);
                    row.appendChild(removeButton);
                    container.appendChild(row);
                    syncLinksJson(prefix);
                }

                function loadLinksBuilder(prefix, rawJson) {
                    var container = getLinksContainer(prefix);
                    if (!container) {
                        return;
                    }

                    container.innerHTML = '';
                    var pairs = parseLinksJson(rawJson);
                    if (pairs.length === 0) {
                        appendLinkRow(prefix, '', '');
                        return;
                    }

                    pairs.forEach(function (pair) {
                        appendLinkRow(prefix, pair.key || '', pair.url || '');
                    });
                    syncLinksJson(prefix);
                }

                if (openCreateButton && createModal) {
                    openCreateButton.addEventListener('click', function () {
                        openModal(createModal);
                    });
                }

                document.querySelectorAll('.js-modal-close').forEach(function (button) {
                    button.addEventListener('click', function () {
                        closeModal(document.getElementById(button.getAttribute('data-modal-id') || ''));
                    });
                });

                [createModal, editModal].forEach(function (modal) {
                    if (!modal) {
                        return;
                    }
                    modal.addEventListener('click', function (event) {
                        if (event.target === modal) {
                            closeModal(modal);
                        }
                    });
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key !== 'Escape') {
                        return;
                    }
                    closeModal(createModal);
                    closeModal(editModal);
                });

                if (createPrCodeInput) {
                    createPrCodeInput.addEventListener('input', function () {
                        var cleaned = sanitizePrCodeRaw(createPrCodeInput.value);
                        if (createPrCodeInput.value !== cleaned) {
                            createPrCodeInput.value = cleaned;
                        }
                        refreshGeneratedUrls(cleaned, 'create');
                    });
                    createPrCodeInput.value = sanitizePrCodeRaw(createPrCodeInput.value);
                    refreshGeneratedUrls(createPrCodeInput.value, 'create');
                }
                if (updatePrCodeInput) {
                    updatePrCodeInput.addEventListener('input', function () {
                        var cleaned = sanitizePrCodeRaw(updatePrCodeInput.value);
                        if (updatePrCodeInput.value !== cleaned) {
                            updatePrCodeInput.value = cleaned;
                        }
                        refreshGeneratedUrls(cleaned, 'update');
                    });
                    updatePrCodeInput.value = sanitizePrCodeRaw(updatePrCodeInput.value);
                    refreshGeneratedUrls(updatePrCodeInput.value, 'update');
                }

                if (createAddLinkButton) {
                    createAddLinkButton.addEventListener('click', function () {
                        appendLinkRow('create', '', '');
                    });
                }
                if (updateAddLinkButton) {
                    updateAddLinkButton.addEventListener('click', function () {
                        appendLinkRow('update', '', '');
                    });
                }

                loadLinksBuilder('create', createLinksJsonField ? createLinksJsonField.value : '');
                loadLinksBuilder('update', updateLinksJsonField ? updateLinksJsonField.value : '');

                document.querySelectorAll('.js-copy-pr-url').forEach(function (button) {
                    button.addEventListener('click', function () {
                        var targetId = button.getAttribute('data-target-id') || '';
                        var sourceField = document.getElementById(targetId);
                        if (!sourceField) {
                            return;
                        }

                        var textToCopy = sourceField.value || '';
                        copyText(textToCopy).then(function () {
                            var oldLabel = button.textContent;
                            button.textContent = 'Copied';
                            setTimeout(function () {
                                button.textContent = oldLabel;
                            }, 1200);
                        }).catch(function () {
                            button.textContent = 'Error';
                            setTimeout(function () {
                                button.textContent = 'Copy';
                            }, 1200);
                        });
                    });
                });

                document.querySelectorAll('.js-pr-delete-form').forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        var ok = window.confirm('Delete this PR mapping?');
                        if (!ok) {
                            event.preventDefault();
                        }
                    });
                });

                if (!updateForm) {
                    return;
                }

                var updateEditId = document.getElementById('update_edit_id');
                var fieldPrCode = document.getElementById('update_pr_code');
                var fieldBloggerName = document.getElementById('update_blogger_name');
                var fieldCampaignName = document.getElementById('update_campaign_name');
                var fieldPlatform = document.getElementById('update_platform');
                var fieldProfileUrl = document.getElementById('update_profile_url');
                var fieldLinksJson = document.getElementById('update_links_json');
                var fieldNotes = document.getElementById('update_notes');
                var fieldIsActive = document.getElementById('update_is_active');

                function applyUpdateFormData(data) {
                    if (!data || !data.id) {
                        return;
                    }

                    if (data.updateUrl) {
                        updateForm.setAttribute('action', data.updateUrl);
                    } else if (actionTemplate) {
                        updateForm.setAttribute('action', actionTemplate.replace('__ID__', data.id));
                    }
                    if (updateEditId) {
                        updateEditId.value = data.id;
                    }
                    if (fieldPrCode) {
                        fieldPrCode.value = sanitizePrCodeRaw(data.prCode || '');
                        refreshGeneratedUrls(fieldPrCode.value, 'update');
                    }
                    if (fieldBloggerName) {
                        fieldBloggerName.value = data.bloggerName || '';
                    }
                    if (fieldCampaignName) {
                        fieldCampaignName.value = data.campaignName || '';
                    }
                    if (fieldPlatform) {
                        fieldPlatform.value = data.platform || '';
                    }
                    if (fieldProfileUrl) {
                        fieldProfileUrl.value = data.profileUrl || '';
                    }
                    if (fieldLinksJson) {
                        fieldLinksJson.value = data.linksJson || '';
                        loadLinksBuilder('update', fieldLinksJson.value);
                    }
                    if (fieldNotes) {
                        fieldNotes.value = data.notes || '';
                    }
                    if (fieldIsActive) {
                        if (data.isActive === undefined || data.isActive === null || data.isActive === '') {
                            fieldIsActive.checked = true;
                        } else {
                            fieldIsActive.checked = String(data.isActive) === '1';
                        }
                    }
                }

                document.querySelectorAll('.js-pr-edit-btn').forEach(function (button) {
                    button.addEventListener('click', function () {
                        applyUpdateFormData({
                            id: button.getAttribute('data-id') || '',
                            updateUrl: button.getAttribute('data-update-url') || '',
                            prCode: button.getAttribute('data-pr-code') || '',
                            bloggerName: button.getAttribute('data-blogger-name') || '',
                            campaignName: button.getAttribute('data-campaign-name') || '',
                            platform: button.getAttribute('data-platform') || '',
                            profileUrl: button.getAttribute('data-profile-url') || '',
                            linksJson: button.getAttribute('data-links-json') || '',
                            notes: button.getAttribute('data-notes') || '',
                            isActive: button.getAttribute('data-is-active') || '0'
                        });
                        openModal(editModal);
                    });
                });

                var oldFormMode = @json(old('form_mode'));
                var oldEditId = @json(old('edit_id'));
                if (oldFormMode === 'create') {
                    openModal(createModal);
                } else if (oldFormMode === 'update' && oldEditId) {
                    applyUpdateFormData({
                        id: String(oldEditId),
                        updateUrl: actionTemplate ? actionTemplate.replace('__ID__', String(oldEditId)) : '',
                        prCode: @json(old('pr_code')),
                        bloggerName: @json(old('blogger_name')),
                        campaignName: @json(old('campaign_name')),
                        platform: @json(old('platform')),
                        profileUrl: @json(old('profile_url')),
                        linksJson: @json(old('links_json')),
                        notes: @json(old('notes')),
                        isActive: @json(old('is_active', 1))
                    });
                    openModal(editModal);
                }
            });
        </script>
        <x-offcanvas-right />
    </body>
</html>
