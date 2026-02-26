<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Website analytics" />
        <x-style-head-dropy />
        <x-styles-datatables />
        <style>
            #visitorAnalyticsWebTableWrapper {
                max-width: 100%;
            }

            #visitorAnalyticsWebTable {
                width: 100% !important;
                min-width: 1750px;
            }

            #visitorAnalyticsWebTable td,
            #visitorAnalyticsWebTable th {
                vertical-align: top;
                white-space: nowrap;
            }

            .url-clip {
                display: inline-block;
                max-width: 360px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                vertical-align: top;
            }

            .analytics-user a {
                font-weight: 600;
                color: #0f172a;
                text-decoration: none;
            }

            .analytics-user a:hover {
                text-decoration: underline;
            }

            .analytics-user small {
                display: block;
                margin-top: 2px;
                font-size: 11px;
                color: #64748b;
            }
        </style>
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        @php
            $scope = isset($scope) ? (string) $scope : 'latest_500';
            $scopeOptions = $scopeOptions ?? [
                'latest_500' => 'Latest 500 rows',
                'today' => 'Today',
                'week' => 'This week',
                'month' => 'This month',
                'month_prev' => 'Previous month',
            ];
        @endphp

        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Website analytics" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold">Website analytics</h1>
                                <p class="mt-2 text-sm text-slate-600">Server-side DataTables for visitorBehaviorAnalytics.</p>
                            </div>
                            <div class="text-xs font-semibold text-slate-500">
                                DB: {{ session('tenant_db', config('dropy.tenants.default')) }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5" style=" max-width: 1400px;">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm font-semibold text-slate-700">Rows</div>
                            <div class="text-xs text-slate-500">No full-table load, data is fetched by page.</div>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <label for="scopeFilter" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Range</label>
                            <select id="scopeFilter" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700">
                                @foreach ($scopeOptions as $key => $label)
                                    <option value="{{ $key }}" {{ $scope === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="text-xs text-slate-500">
                                Default is latest 500 rows to keep the page fast.
                            </div>
                        </div>

                        <div id="visitorAnalyticsWebTableWrapper" class="mt-3">
                            <table id="visitorAnalyticsWebTable" class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-slate-500">
                                        <th>ID</th>
                                        <th>IP</th>
                                        <th>User</th>
                                        <th>Recovered page</th>
                                        <th>Country</th>
                                        <th>Region</th>
                                        <th>City</th>
                                        <th>Timezone</th>
                                        <th>Browser version</th>
                                        <th>Device</th>
                                        <th>OS</th>
                                        <th>Window width</th>
                                        <th>Language</th>
                                        <th>Stay length</th>
                                        <th>History</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
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
            (function () {
                const dataEndpoint = @json(route('visitors-analytics-web.data'));
                const initialScope = @json($scope);

                function escapeHtml(value) {
                    return String(value)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');
                }

                function formatText(value) {
                    if (value === null || value === undefined || value === '') {
                        return '-';
                    }

                    return escapeHtml(value);
                }

                $(document).ready(function () {
                    const scopeFilter = $('#scopeFilter');
                    if (!scopeFilter.val()) {
                        scopeFilter.val(initialScope);
                    }

                    const table = $('#visitorAnalyticsWebTable').DataTable({
                        processing: true,
                        serverSide: true,
                        responsive: false,
                        scrollX: true,
                        scrollCollapse: true,
                        autoWidth: false,
                        deferRender: true,
                        searchDelay: 300,
                        pageLength: 50,
                        lengthMenu: [
                            [25, 50, 100, 250, 500],
                            [25, 50, 100, 250, 500]
                        ],
                        order: [[0, 'desc']],
                        ajax: {
                            url: dataEndpoint,
                            type: 'GET',
                            data: function (d) {
                                d.scope = scopeFilter.val() || initialScope;
                            },
                            dataSrc: function (json) {
                                if (json && json.error) {
                                    console.error('DataTables error:', json.error);
                                }

                                return (json && json.data) ? json.data : [];
                            }
                        },
                        dom: 'lBfrtip',
                        oLanguage: { sSearch: '' },
                        columns: [
                            {
                                data: 'id',
                                render: function (data, type) {
                                    if (type !== 'display') {
                                        return data;
                                    }

                                    return formatText(data);
                                }
                            },
                            {
                                data: 'ipAddress',
                                render: function (data, type) {
                                    if (type !== 'display') {
                                        return data;
                                    }

                                    return formatText(data);
                                }
                            },
                            {
                                data: null,
                                render: function (data, type, row) {
                                    if (type !== 'display') {
                                        return row && row.user_id ? row.user_id : '';
                                    }

                                    if (!row || !row.user_id) {
                                        return '-';
                                    }

                                    const userUrl = '/users/' + row.user_id;
                                    const userLabel = formatText(row.user_label || row.user_id);
                                    const userName = row.user_name ? '<small>' + formatText(row.user_name) + '</small>' : '';

                                    return '<div class="analytics-user"><a href="' + userUrl + '">' + userLabel + '</a>' + userName + '</div>';
                                }
                            },
                            {
                                data: 'recoveredPage',
                                render: function (data, type) {
                                    if (type !== 'display') {
                                        return data;
                                    }

                                    const value = data === null || data === undefined || data === '' ? '-' : String(data);
                                    return '<span class="url-clip" title="' + escapeHtml(value) + '">' + escapeHtml(value) + '</span>';
                                }
                            },
                            { data: 'country', render: function (data) { return formatText(data); } },
                            { data: 'region', render: function (data) { return formatText(data); } },
                            { data: 'city', render: function (data) { return formatText(data); } },
                            { data: 'timezone', render: function (data) { return formatText(data); } },
                            { data: 'browserVersion', render: function (data) { return formatText(data); } },
                            { data: 'deviceName', render: function (data) { return formatText(data); } },
                            { data: 'operatingSystem', render: function (data) { return formatText(data); } },
                            { data: 'browserWindowWidth', render: function (data) { return formatText(data); } },
                            { data: 'browserLanguage', render: function (data) { return formatText(data); } },
                            { data: 'lengthStayOnPage', render: function (data) { return formatText(data); } },
                            {
                                data: 'historyToPage',
                                render: function (data, type) {
                                    if (type !== 'display') {
                                        return data;
                                    }

                                    const value = data === null || data === undefined || data === '' ? '-' : String(data);
                                    return '<span class="url-clip" title="' + escapeHtml(value) + '">' + escapeHtml(value) + '</span>';
                                }
                            },
                            { data: 'date', render: function (data) { return formatText(data); } },
                            { data: 'timeLabel', render: function (data) { return formatText(data); } }
                        ],
                        buttons: [
                            {
                                extend: 'copyHtml5',
                                text: 'Copy <i class="fas fa-copy"></i>',
                                titleAttr: 'Copy',
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

                    scopeFilter.on('change', function () {
                        const selectedScope = scopeFilter.val() || initialScope;
                        const nextUrl = new URL(window.location.href);
                        nextUrl.searchParams.set('scope', selectedScope);
                        window.history.replaceState({}, '', nextUrl.toString());
                        table.ajax.reload(null, true);
                    });
                });
            })();
        </script>
    </body>
</html>
