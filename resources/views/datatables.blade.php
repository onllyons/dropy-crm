<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="DataTables" />
        <x-style-head-dropy />
        <x-styles-datatables />

    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="DataTables" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h1 class="text-2xl font-semibold">Example table</h1>
                        <p class="mt-2 text-sm text-slate-600">Foloseste acest tabel ca baza si editeaza coloanele/continutul.</p>

                        <div class="mt-6">
                            <table id="datatablesExample" class="display w-full text-sm">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Owner</th>
                                        <th>Updated</th>
                                        <th>Category</th>
                                        <th>Priority</th>
                                        <th>Amount</th>
                                        <th>Region</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td></td>
                                        <td>1</td>
                                        <td>Example A</td>
                                        <td>Active</td>
                                        <td>Alex</td>
                                        <td>2024-03-12</td>
                                        <td>Retail</td>
                                        <td>High</td>
                                        <td>$1,200</td>
                                        <td>EU</td>
                                        <td>First run</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>2</td>
                                        <td>Example B</td>
                                        <td>Archived</td>
                                        <td>Maria</td>
                                        <td>2024-03-03</td>
                                        <td>Wholesale</td>
                                        <td>Low</td>
                                        <td>$300</td>
                                        <td>US</td>
                                        <td>Legacy</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>3</td>
                                        <td>Example C</td>
                                        <td>Draft</td>
                                        <td>Radu</td>
                                        <td>2024-02-25</td>
                                        <td>Services</td>
                                        <td>Medium</td>
                                        <td>$780</td>
                                        <td>APAC</td>
                                        <td>Pending</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>4</td>
                                        <td>Example D</td>
                                        <td>Active</td>
                                        <td>Ioana</td>
                                        <td>2024-02-10</td>
                                        <td>Retail</td>
                                        <td>High</td>
                                        <td>$2,450</td>
                                        <td>EU</td>
                                        <td>Priority client</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>5</td>
                                        <td>Example E</td>
                                        <td>Active</td>
                                        <td>Andrei</td>
                                        <td>2024-01-30</td>
                                        <td>Wholesale</td>
                                        <td>Low</td>
                                        <td>$150</td>
                                        <td>MEA</td>
                                        <td>Small account</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>6</td>
                                        <td>Example F</td>
                                        <td>Archived</td>
                                        <td>Bianca</td>
                                        <td>2024-01-15</td>
                                        <td>Services</td>
                                        <td>Medium</td>
                                        <td>$980</td>
                                        <td>US</td>
                                        <td>Churned</td>
                                    </tr>
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
                $('#datatablesExample').DataTable({
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
