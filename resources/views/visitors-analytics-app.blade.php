<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="App analytics" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="App analytics" />

                <main class="p-4 md:p-6">
                    @php
                        $isPaginator = $rows instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator || $rows instanceof \Illuminate\Contracts\Pagination\Paginator;
                        $rowsCollection = $isPaginator ? collect($rows->items()) : collect($rows);
                        $rowsTotal = $isPaginator ? $rows->total() : $rowsCollection->count();
                        $rowsFrom = $isPaginator ? ($rows->firstItem() ?? 0) : ($rowsCollection->isEmpty() ? 0 : 1);
                        $rowsTo = $isPaginator ? ($rows->lastItem() ?? 0) : $rowsCollection->count();
                        $perPage = isset($perPage) ? (int) $perPage : 250;
                        $perPageOptions = $perPageOptions ?? [100, 250, 500, 1000];
                    @endphp

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h1 class="text-2xl font-semibold">App analytics</h1>
                    </div>

                    @if (!empty($error))
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ $error }}
                        </div>
                    @endif

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-slate-700">App rows (paginated)</div>
                                <div class="text-xs text-slate-500">
                                    visitorBehaviorAnalyticsApp table | rows {{ $rowsFrom }}-{{ $rowsTo }} / {{ $rowsTotal }}
                                </div>
                            </div>
                            <form method="GET" action="{{ url('/visitors-analytics-app') }}" class="flex items-center gap-2">
                                <label for="per_page" class="text-xs text-slate-600">Rows/page</label>
                                <select id="per_page" name="per_page" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs text-slate-700" onchange="this.form.submit()">
                                    @foreach ($perPageOptions as $option)
                                        <option value="{{ $option }}" {{ $perPage === (int) $option ? 'selected' : '' }}>{{ $option }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>

                        <div class="mt-3 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-slate-500">
                                        <th class="pb-2">ID</th>
                                        <th class="pb-2">Hash</th>
                                        <th class="pb-2">IP</th>
                                        <th class="pb-2">User</th>
                                        <th class="pb-2">Screen</th>
                                        <th class="pb-2">Country</th>
                                        <th class="pb-2">Region</th>
                                        <th class="pb-2">City</th>
                                        <th class="pb-2">Timezone</th>
                                        <th class="pb-2">OS version</th>
                                        <th class="pb-2">Device</th>
                                        <th class="pb-2">OS</th>
                                        <th class="pb-2">Window width</th>
                                        <th class="pb-2">Language</th>
                                        <th class="pb-2">Stay length</th>
                                        <th class="pb-2">Last screen</th>
                                        <th class="pb-2">Version</th>
                                        <th class="pb-2">Date</th>
                                        <th class="pb-2">Time</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($rowsCollection as $row)
                                        @php
                                            $timeValue = $row->time ?? null;
                                            $timeLabel = is_numeric($timeValue) ? date('Y-m-d H:i', (int) $timeValue) : ($timeValue ?? '-');
                                        @endphp
                                        <tr>
                                            <td class="py-2 text-slate-700">{{ $row->id }}</td>
                                            <td class="py-2 text-slate-600">{{ $row->hash ?? '-' }}</td>
                                            <td class="py-2 text-slate-600">{{ $row->ipAddress ?? '-' }}</td>
                                            <td class="py-2 text-slate-600">
                                                @php
                                                    $user = $users[$row->user_id] ?? null;
                                                    $userLabel = $user ? ($user->username ?? $row->user_id) : ($row->user_id ?? '-');
                                                @endphp
                                                @if (!empty($row->user_id))
                                                    <a class="font-semibold text-slate-700 hover:underline" href="{{ url('/users/' . $row->user_id) }}">{{ $userLabel }}</a>
                                                    @if (!empty($user) && !empty($user->name))
                                                        <div class="text-xs text-slate-500">{{ $user->name }}</div>
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="py-2 text-slate-600">{{ $row->screen ?? '-' }}</td>
                                            <td class="py-2 text-slate-600">{{ $row->country ?? '-' }}</td>
                                            <td class="py-2 text-slate-600">{{ $row->region ?? '-' }}</td>
                                            <td class="py-2 text-slate-600">{{ $row->city ?? '-' }}</td>
                                            <td class="py-2 text-slate-600">{{ $row->timezone ?? '-' }}</td>
                                            <td class="py-2 text-slate-600">{{ $row->osVersion ?? '-' }}</td>
                                            <td class="py-2 text-slate-600">{{ $row->deviceName ?? '-' }}</td>
                                            <td class="py-2 text-slate-600">{{ $row->operatingSystem ?? '-' }}</td>
                                            <td class="py-2 text-slate-600">{{ $row->windowWidth ?? '-' }}</td>
                                            <td class="py-2 text-slate-600">{{ $row->language ?? '-' }}</td>
                                            <td class="py-2 text-slate-600">{{ $row->lengthStayOnScreen ?? '-' }}</td>
                                            <td class="py-2 text-slate-600">{{ $row->lastScreen ?? '-' }}</td>
                                            <td class="py-2 text-slate-600">{{ $row->version ?? '-' }}</td>
                                            <td class="py-2 text-slate-600">{{ $row->date ?? '-' }}</td>
                                            <td class="py-2 text-slate-600">{{ $timeLabel }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="py-3 text-slate-500" colspan="19">No rows found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($isPaginator)
                            <div class="mt-4">
                                {{ $rows->links() }}
                            </div>
                        @endif
                    </div>
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
    </body>
</html>
