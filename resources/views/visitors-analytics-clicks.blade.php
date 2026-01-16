<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="App store clicks" />
        <x-style-head-dropy />
        <x-styles-datatables />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="App store clicks" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h1 class="text-2xl font-semibold">App store clicks</h1>
                        <p class="mt-2 text-sm text-slate-600">Tracks users who clicked Google Play or App Store cards.</p>
                    </div>

                    @if (!empty($error))
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ $error }}
                        </div>
                    @endif

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm font-semibold text-slate-700">Clicks snapshot</div>
                            <div class="text-xs text-slate-500">clickOfApp table</div>
                        </div>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Total clicks</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $totalClicks ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Unique users</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $uniqueUsers ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Unique IPs</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $uniqueIps ?? 0 }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm font-semibold text-slate-700">All clicks</div>
                            <div class="text-xs text-slate-500">Latest rows from clickOfApp.</div>
                        </div>
                        <div class="mt-3 overflow-x-auto">
                            <table id="clickOfAppTable" class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-slate-500">
                                        <th class="pb-2"></th>
                                        <th class="pb-2">ID</th>
                                        <th class="pb-2">User</th>
                                        <th class="pb-2">IP</th>
                                        <th class="pb-2">Time</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($rows as $row)
                                        @php
                                            $timeValue = $row->timedate ?? null;
                                            $timeLabel = is_numeric($timeValue) ? date('Y-m-d H:i', (int) $timeValue) : ($timeValue ?? '-');
                                            $user = $users[$row->userId] ?? null;
                                            $userLabel = $user ? ($user->username ?? $row->userId) : ($row->userId ?? '-');
                                        @endphp
                                        <tr>
                                            <td class="py-2"></td>
                                            <td class="py-2 text-slate-700">{{ $row->id }}</td>
                                            <td class="py-2 text-slate-600">
                                                @if (!empty($row->userId))
                                                    <a class="font-semibold text-slate-700 hover:underline" href="{{ url('/users/' . $row->userId) }}">{{ $userLabel }}</a>
                                                    @if (!empty($user) && !empty($user->name))
                                                        <div class="text-xs text-slate-500">{{ $user->name }}</div>
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="py-2 text-slate-600">{{ $row->ip ?? '-' }}</td>
                                            <td class="py-2 text-slate-600">{{ $timeLabel }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="py-3 text-slate-500" colspan="5">No rows found.</td>
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
        <x-offcanvas-right />
        <x-script-datatables />

        <script>
            $(document).ready(function () {
                $('#clickOfAppTable').DataTable({
                    dom: 'lBfrtip',
                    responsive: true,
                    columnDefs: [
                        { targets: 0, className: 'dtr-control', orderable: false }
                    ],
                    responsive: {
                        details: {
                            type: 'column',
                            target: 0
                        }
                    },
                    lengthMenu: [
                        [25, 50, -1],
                        [25, 50, 'All']
                    ],
                    oLanguage: { sSearch: '' },
                    buttons: [
                        {
                            extend: 'copyHtml5',
                            text: 'Copy <i class="fas fa-copy"></i>',
                            titleAttr: 'Copiaza',
                            exportOptions: { columns: ':visible' }
                        },
                        {
                            extend: 'excelHtml5',
                            text: 'Excel <i class="fas fa-file-excel"></i>',
                            titleAttr: 'Export Excel',
                            exportOptions: { columns: ':visible' }
                        },
                        {
                            extend: 'csvHtml5',
                            text: 'CSV <i class="fas fa-file-csv"></i>',
                            titleAttr: 'Export CSV',
                            exportOptions: { columns: ':visible' }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: 'PDF <i class="fas fa-file-pdf"></i>',
                            titleAttr: 'Export PDF',
                            exportOptions: { columns: ':visible' },
                            orientation: 'landscape',
                            pageSize: 'A4'
                        }
                    ]
                });
            });
        </script>
    </body>
</html>
