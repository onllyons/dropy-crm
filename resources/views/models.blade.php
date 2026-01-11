<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Models" />
        <x-style-head-dropy />
        <x-styles-datatables />
      
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen md:flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="content-offset flex-1">
                <x-top-nav title="Models" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h1 class="text-3xl font-semibold">Models</h1>
                                <p class="mt-2 text-slate-600">MVP pentru tabs/pills, usor de extins.</p>
                            </div>
                            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Add model</button>
                        </div>

                        <div class="mt-6">
                            <div class="flex flex-wrap gap-2" role="tablist" aria-label="Models tabs">
                                <button class="tab-pill rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white" type="button" role="tab" aria-selected="true" data-tab="all">All</button>
                                <button class="tab-pill rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700" type="button" role="tab" aria-selected="false" data-tab="active">Active</button>
                                <button class="tab-pill rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700" type="button" role="tab" aria-selected="false" data-tab="archived">Archived</button>
                            </div>

                            <div class="mt-4">
                                <div class="tab-panel" data-panel="all">
                                    <div class="rounded-xl border border-slate-200 p-4">
                                        <div class="text-sm font-semibold text-slate-700">All models</div>
                                        <p class="mt-2 text-sm text-slate-600">Lista completa pentru MVP. Aici vor veni carduri sau tabel.</p>
                                    </div>
                                </div>
                                <div class="tab-panel hidden" data-panel="active">
                                    <div class="rounded-xl border border-slate-200 p-4">
                                        <div class="text-sm font-semibold text-slate-700">Active models</div>
                                        <p class="mt-2 text-sm text-slate-600">Modele active. Continut minim pentru inceput.</p>
                                    </div>
                                </div>
                                <div class="tab-panel hidden" data-panel="archived">
                                    <div class="rounded-xl border border-slate-200 p-4">
                                        <div class="text-sm font-semibold text-slate-700">Archived models</div>
                                        <p class="mt-2 text-sm text-slate-600">Modele arhivate. Continut placeholder.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 border-t border-slate-200 pt-8">
                            <h2 class="text-lg font-semibold">Actiuni</h2>
                            <p class="mt-1 text-sm text-slate-600">Componente minimale pentru MVP.</p>

                            <div class="mt-4 grid gap-6 lg:grid-cols-2">
                                <div class="rounded-xl border border-slate-200 p-4">
                                    <div class="text-sm font-semibold text-slate-700">Button</div>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white" type="button">Primary</button>
                                        <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" type="button">Secondary</button>
                                        <button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white" type="button">Danger</button>
                                        <button class="rounded-lg border border-slate-300 p-2 text-slate-700" type="button" aria-label="Icon button">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <div class="text-sm font-semibold text-slate-700">Button group</div>
                                    <div class="mt-3 inline-flex overflow-hidden rounded-lg border border-slate-300">
                                        <button class="px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" type="button">Day</button>
                                        <button class="border-x border-slate-300 bg-slate-900 px-4 py-2 text-sm font-semibold text-white" type="button">Week</button>
                                        <button class="px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100" type="button">Month</button>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <div class="text-sm font-semibold text-slate-700">Dropdown</div>
                                    <div class="relative mt-3 inline-block">
                                        <button id="dropdownButton" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" type="button" aria-expanded="false">
                                            Select
                                        </button>
                                        <div id="dropdownMenu" class="absolute left-0 mt-2 hidden w-40 rounded-lg border border-slate-200 bg-white p-1 shadow-lg">
                                            <button class="block w-full rounded-md px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100" type="button">Option 1</button>
                                            <button class="block w-full rounded-md px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100" type="button">Option 2</button>
                                            <button class="block w-full rounded-md px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100" type="button">Option 3</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <div class="text-sm font-semibold text-slate-700">Context menu</div>
                                    <div class="mt-3 flex items-center justify-between rounded-lg border border-slate-200 px-4 py-3">
                                        <div>
                                            <div class="text-sm font-semibold">Model #128</div>
                                            <div class="text-xs text-slate-500">Last update: today</div>
                                        </div>
                                        <div class="relative">
                                            <button id="contextButton" class="rounded-lg border border-slate-300 p-2 text-slate-700" type="button" aria-expanded="false" aria-label="Open context menu">
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                                    <circle cx="12" cy="5" r="1.8"></circle>
                                                    <circle cx="12" cy="12" r="1.8"></circle>
                                                    <circle cx="12" cy="19" r="1.8"></circle>
                                                </svg>
                                            </button>
                                            <div id="contextMenu" class="absolute right-0 mt-2 hidden w-40 rounded-lg border border-slate-200 bg-white p-1 shadow-lg">
                                                <button class="block w-full rounded-md px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100" type="button">Edit</button>
                                                <button class="block w-full rounded-md px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100" type="button">Duplicate</button>
                                                <button class="block w-full rounded-md px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50" type="button">Delete</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 border-t border-slate-200 pt-8">
                            <h2 class="text-lg font-semibold">Formulare</h2>
                            <p class="mt-1 text-sm text-slate-600">Inputuri de baza pentru MVP.</p>

                            <div class="mt-4 grid gap-6 lg:grid-cols-2">
                                <div class="rounded-xl border border-slate-200 p-4">
                                    <div class="text-sm font-semibold text-slate-700">Input</div>
                                    <div class="mt-3 space-y-3">
                                        <input class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" type="text" placeholder="Text" />
                                        <input class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" type="email" placeholder="Email" />
                                        <input class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" type="number" placeholder="Number" />
                                    </div>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <div class="text-sm font-semibold text-slate-700">Select</div>
                                    <div class="mt-3">
                                        <select class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                            <option>Select option</option>
                                            <option>Option 1</option>
                                            <option>Option 2</option>
                                            <option>Option 3</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <div class="text-sm font-semibold text-slate-700">Checkbox</div>
                                    <label class="mt-3 flex items-center gap-2 text-sm text-slate-700">
                                        <input class="h-4 w-4 rounded border-slate-300 text-slate-900" type="checkbox" />
                                        Remember me
                                    </label>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <div class="text-sm font-semibold text-slate-700">Radio</div>
                                    <div class="mt-3 space-y-2 text-sm text-slate-700">
                                        <label class="flex items-center gap-2">
                                            <input class="h-4 w-4 border-slate-300 text-slate-900" type="radio" name="plan" checked />
                                            Basic
                                        </label>
                                        <label class="flex items-center gap-2">
                                            <input class="h-4 w-4 border-slate-300 text-slate-900" type="radio" name="plan" />
                                            Pro
                                        </label>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <div class="text-sm font-semibold text-slate-700">Switch / Toggle</div>
                                    <label class="mt-3 inline-flex items-center gap-3 text-sm text-slate-700">
                                        <input class="peer sr-only" type="checkbox" checked />
                                        <span class="relative h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-slate-900">
                                            <span class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition peer-checked:translate-x-5"></span>
                                        </span>
                                        Notifications
                                    </label>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <div class="text-sm font-semibold text-slate-700">Textarea</div>
                                    <textarea class="mt-3 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" rows="4" placeholder="Write something..."></textarea>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <div class="text-sm font-semibold text-slate-700">Date picker</div>
                                    <input class="mt-3 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" type="date" />
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <div class="text-sm font-semibold text-slate-700">File upload</div>
                                    <input class="mt-3 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" type="file" />
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 border-t border-slate-200 pt-8">
                            <h2 class="text-lg font-semibold">Feedback</h2>
                            <p class="mt-1 text-sm text-slate-600">Modal simplu pentru confirmari.</p>

                            <div class="mt-4 rounded-xl border border-slate-200 p-4">
                                <div class="flex flex-wrap gap-2">
                                    <button id="openModalButton" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white" type="button">Open modal</button>
                                    <button id="toastSuccessButton" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" type="button">Show alert</button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 border-t border-slate-200 pt-8">
                            <h2 class="text-lg font-semibold">Tooltip</h2>
                            <p class="mt-1 text-sm text-slate-600">Top / Left / Right / Bottom.</p>

                            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <div class="flex justify-center">
                                    <div class="relative group">
                                        <span class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700">Top</span>
                                        <div class="pointer-events-none absolute left-1/2 top-0 -translate-x-1/2 -translate-y-full opacity-0 transition group-hover:opacity-100">
                                            <div class="rounded-md bg-slate-900 px-3 py-2 text-xs text-white">Tooltip top</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-center">
                                    <div class="relative group">
                                        <span class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700">Left</span>
                                        <div class="pointer-events-none absolute left-0 top-1/2 -translate-x-full -translate-y-1/2 opacity-0 transition group-hover:opacity-100">
                                            <div class="rounded-md bg-slate-900 px-3 py-2 text-xs text-white">Tooltip left</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-center">
                                    <div class="relative group">
                                        <span class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700">Right</span>
                                        <div class="pointer-events-none absolute right-0 top-1/2 translate-x-full -translate-y-1/2 opacity-0 transition group-hover:opacity-100">
                                            <div class="rounded-md bg-slate-900 px-3 py-2 text-xs text-white">Tooltip right</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-center">
                                    <div class="relative group">
                                        <span class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700">Bottom</span>
                                        <div class="pointer-events-none absolute left-1/2 bottom-0 -translate-x-1/2 translate-y-full opacity-0 transition group-hover:opacity-100">
                                            <div class="rounded-md bg-slate-900 px-3 py-2 text-xs text-white">Tooltip bottom</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 border-t border-slate-200 pt-8">
                            <h2 class="text-lg font-semibold">Accordion</h2>
                            <p class="mt-1 text-sm text-slate-600">Structura simpla, usor de extins.</p>

                            <div class="mt-4 space-y-2">
                                <button class="accordion-toggle flex w-full items-center justify-between rounded-lg border border-slate-200 px-4 py-3 text-left text-sm font-semibold" type="button" aria-expanded="true" data-accordion="item-1">
                                    <span>Section 1</span>
                                    <span class="text-slate-500">+</span>
                                </button>
                                <div class="accordion-panel rounded-lg border border-slate-200 p-4 text-sm text-slate-600" data-panel="item-1">
                                    Content pentru section 1. Text placeholder.
                                </div>

                                <button class="accordion-toggle flex w-full items-center justify-between rounded-lg border border-slate-200 px-4 py-3 text-left text-sm font-semibold" type="button" aria-expanded="false" data-accordion="item-2">
                                    <span>Section 2</span>
                                    <span class="text-slate-500">+</span>
                                </button>
                                <div class="accordion-panel hidden rounded-lg border border-slate-200 p-4 text-sm text-slate-600" data-panel="item-2">
                                    Content pentru section 2. Text placeholder.
                                </div>

                                <button class="accordion-toggle flex w-full items-center justify-between rounded-lg border border-slate-200 px-4 py-3 text-left text-sm font-semibold" type="button" aria-expanded="false" data-accordion="item-3">
                                    <span>Section 3</span>
                                    <span class="text-slate-500">+</span>
                                </button>
                                <div class="accordion-panel hidden rounded-lg border border-slate-200 p-4 text-sm text-slate-600" data-panel="item-3">
                                    Content pentru section 3. Text placeholder.
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 border-t border-slate-200 pt-8">
                            <h2 class="text-lg font-semibold">Collapse</h2>
                            <p class="mt-1 text-sm text-slate-600">Sectiune unica, deschidere/inchidere.</p>

                            <div class="mt-4">
                                <button id="collapseToggle" class="flex w-full items-center justify-between rounded-lg border border-slate-200 px-4 py-3 text-left text-sm font-semibold" type="button" aria-expanded="false">
                                    <span>Show details</span>
                                    <span class="text-slate-500">+</span>
                                </button>
                                <div id="collapsePanel" class="hidden rounded-lg border border-slate-200 p-4 text-sm text-slate-600">
                                    Continut pentru collapse. Text placeholder.
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 border-t border-slate-200 pt-8">
                            <h2 class="text-lg font-semibold">Vizual</h2>
                            <p class="mt-1 text-sm text-slate-600">Color palette de baza pentru UI.</p>

                            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <div class="rounded-xl border border-slate-200">
                                    <div class="h-20 rounded-t-xl bg-slate-900"></div>
                                    <div class="p-3 text-sm">
                                        <div class="font-semibold">Primary</div>
                                        <div class="text-slate-500">#0F172A</div>
                                    </div>
                                </div>
                                <div class="rounded-xl border border-slate-200">
                                    <div class="h-20 rounded-t-xl bg-slate-600"></div>
                                    <div class="p-3 text-sm">
                                        <div class="font-semibold">Secondary</div>
                                        <div class="text-slate-500">#475569</div>
                                    </div>
                                </div>
                                <div class="rounded-xl border border-slate-200">
                                    <div class="h-20 rounded-t-xl bg-emerald-600"></div>
                                    <div class="p-3 text-sm">
                                        <div class="font-semibold">Success</div>
                                        <div class="text-slate-500">#059669</div>
                                    </div>
                                </div>
                                <div class="rounded-xl border border-slate-200">
                                    <div class="h-20 rounded-t-xl bg-red-600"></div>
                                    <div class="p-3 text-sm">
                                        <div class="font-semibold">Danger</div>
                                        <div class="text-slate-500">#DC2626</div>
                                    </div>
                                </div>
                                <div class="rounded-xl border border-slate-200">
                                    <div class="h-20 rounded-t-xl bg-amber-500"></div>
                                    <div class="p-3 text-sm">
                                        <div class="font-semibold">Warning</div>
                                        <div class="text-slate-500">#F59E0B</div>
                                    </div>
                                </div>
                                <div class="rounded-xl border border-slate-200">
                                    <div class="h-20 rounded-t-xl bg-sky-600"></div>
                                    <div class="p-3 text-sm">
                                        <div class="font-semibold">Info</div>
                                        <div class="text-slate-500">#0284C7</div>
                                    </div>
                                </div>
                                <div class="rounded-xl border border-slate-200">
                                    <div class="h-20 rounded-t-xl bg-slate-100"></div>
                                    <div class="p-3 text-sm">
                                        <div class="font-semibold">Surface</div>
                                        <div class="text-slate-500">#F1F5F9</div>
                                    </div>
                                </div>
                                <div class="rounded-xl border border-slate-200">
                                    <div class="h-20 rounded-t-xl bg-white"></div>
                                    <div class="p-3 text-sm">
                                        <div class="font-semibold">Base</div>
                                        <div class="text-slate-500">#FFFFFF</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 border-t border-slate-200 pt-8">
                            <h2 class="text-lg font-semibold">Font Awesome</h2>
                            <p class="mt-1 text-sm text-slate-600">Minimum 5 icons.</p>

                            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                                <div class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 text-sm text-slate-700">
                                    <i class="fa-solid fa-house text-slate-900"></i>
                                    Home
                                </div>
                                <div class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 text-sm text-slate-700">
                                    <i class="fa-solid fa-user-group text-slate-900"></i>
                                    Clients
                                </div>
                                <div class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 text-sm text-slate-700">
                                    <i class="fa-solid fa-chart-line text-slate-900"></i>
                                    Reports
                                </div>
                                <div class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 text-sm text-slate-700">
                                    <i class="fa-solid fa-clipboard-check text-slate-900"></i>
                                    Tasks
                                </div>
                                <div class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 text-sm text-slate-700">
                                    <i class="fa-solid fa-gear text-slate-900"></i>
                                    Settings
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 border-t border-slate-200 pt-8">
                            <h2 class="text-lg font-semibold">DataTables</h2>
                            <p class="mt-1 text-sm text-slate-600">Tabel cu cautare (jQuery DataTables).</p>

                            <div class="mt-4 rounded-xl border border-slate-200 p-4">

                                <table id="modelsTable" class="dtable display dataTable no-footer dtr-inline">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Status</th>
                                            <th>Owner</th>
                                            <th>Updated</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Model Alpha</td>
                                            <td>Active</td>
                                            <td>Ana</td>
                                            <td>2024-03-04</td>
                                        </tr>
                                        <tr>
                                            <td>Model Beta</td>
                                            <td>Archived</td>
                                            <td>Matei</td>
                                            <td>2024-02-18</td>
                                        </tr>
                                        <tr>
                                            <td>Model Gamma</td>
                                            <td>Active</td>
                                            <td>Ioana</td>
                                            <td>2024-02-01</td>
                                        </tr>
                                        <tr>
                                            <td>Model Delta</td>
                                            <td>Draft</td>
                                            <td>Radu</td>
                                            <td>2024-01-21</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <div id="modalOverlay" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold">Confirm action</h3>
                        <p class="mt-1 text-sm text-slate-600">This is a minimal modal for MVP feedback.</p>
                    </div>
                    <button id="closeModalButton" class="rounded-lg border border-slate-200 p-2 text-slate-700" type="button" aria-label="Close modal">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6l-12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button id="cancelModalButton" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" type="button">Cancel</button>
                    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white" type="button">Confirm</button>
                </div>
            </div>
        </div>


        
        <x-script-components />
        <x-script-datatables />
        <script>

            const tabs = document.querySelectorAll('.tab-pill');
            const panels = document.querySelectorAll('.tab-panel');
            tabs.forEach((tab) => {
                tab.addEventListener('click', () => {
                    const target = tab.getAttribute('data-tab');
                    tabs.forEach((btn) => {
                        btn.classList.remove('bg-slate-900', 'text-white');
                        btn.classList.add('border', 'border-slate-200', 'text-slate-700');
                        btn.setAttribute('aria-selected', 'false');
                    });
                    tab.classList.add('bg-slate-900', 'text-white');
                    tab.classList.remove('border', 'border-slate-200', 'text-slate-700');
                    tab.setAttribute('aria-selected', 'true');
                    panels.forEach((panel) => {
                        panel.classList.toggle('hidden', panel.getAttribute('data-panel') !== target);
                    });
                });
            });

            const dropdownButton = document.getElementById('dropdownButton');
            const dropdownMenu = document.getElementById('dropdownMenu');
            const contextButton = document.getElementById('contextButton');
            const contextMenu = document.getElementById('contextMenu');

            const closeMenus = () => {
                dropdownMenu.classList.add('hidden');
                dropdownButton.setAttribute('aria-expanded', 'false');
                contextMenu.classList.add('hidden');
                contextButton.setAttribute('aria-expanded', 'false');
            };

            dropdownButton.addEventListener('click', (event) => {
                event.stopPropagation();
                const isHidden = dropdownMenu.classList.contains('hidden');
                closeMenus();
                dropdownMenu.classList.toggle('hidden', !isHidden);
                dropdownButton.setAttribute('aria-expanded', String(isHidden));
            });

            contextButton.addEventListener('click', (event) => {
                event.stopPropagation();
                const isHidden = contextMenu.classList.contains('hidden');
                closeMenus();
                contextMenu.classList.toggle('hidden', !isHidden);
                contextButton.setAttribute('aria-expanded', String(isHidden));
            });

            document.addEventListener('click', closeMenus);

            const modalOverlay = document.getElementById('modalOverlay');
            const openModalButton = document.getElementById('openModalButton');
            const closeModalButton = document.getElementById('closeModalButton');
            const cancelModalButton = document.getElementById('cancelModalButton');

            const closeModal = () => {
                modalOverlay.classList.add('hidden');
            };

            openModalButton.addEventListener('click', () => {
                modalOverlay.classList.remove('hidden');
            });
            closeModalButton.addEventListener('click', closeModal);
            cancelModalButton.addEventListener('click', closeModal);
            modalOverlay.addEventListener('click', (event) => {
                if (event.target === modalOverlay) {
                    closeModal();
                }
            });

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

            const toastSuccessButton = document.getElementById('toastSuccessButton');
            toastSuccessButton.addEventListener('click', () => {
                toastr['success']('Inconceivable!');
            });

            $(document).ready(function () {
                $('#modelsTable').DataTable({
                    dom: 'lBfrtip',
                    responsive: true,
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

            const accordionToggles = document.querySelectorAll('.accordion-toggle');
            const accordionPanels = document.querySelectorAll('.accordion-panel');
            accordionToggles.forEach((toggle) => {
                toggle.addEventListener('click', () => {
                    const target = toggle.getAttribute('data-accordion');
                    accordionPanels.forEach((panel) => {
                        const isTarget = panel.getAttribute('data-panel') === target;
                        panel.classList.toggle('hidden', !isTarget);
                    });
                    accordionToggles.forEach((btn) => {
                        btn.setAttribute('aria-expanded', String(btn === toggle));
                    });
                });
            });

            const collapseToggle = document.getElementById('collapseToggle');
            const collapsePanel = document.getElementById('collapsePanel');
            collapseToggle.addEventListener('click', () => {
                const isHidden = collapsePanel.classList.contains('hidden');
                collapsePanel.classList.toggle('hidden', !isHidden);
                collapseToggle.setAttribute('aria-expanded', String(isHidden));
            });
        </script>
        
    </body>
</html>
