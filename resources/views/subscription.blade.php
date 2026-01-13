<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Subscription" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Subscription" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h1 class="text-2xl font-semibold">Subscription</h1>
                    </div>

                    @if (!empty($error))
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ $error }}
                        </div>
                    @endif

                    @php
                        $stats = $stats ?? [];
                        $revenueTotal = '$' . number_format((float) ($stats['revenue_total'] ?? 0), 2, '.', '');
                        $revenueWeb = '$' . number_format((float) ($stats['revenue_web'] ?? 0), 2, '.', '');
                        $revenueApp = '$' . number_format((float) ($stats['revenue_app'] ?? 0), 2, '.', '');
                        $revenueMonth = '$' . number_format((float) ($stats['revenue_month'] ?? 0), 2, '.', '');
                        $activeMrr = '$' . number_format((float) ($stats['active_mrr'] ?? 0), 2, '.', '');
                    @endphp

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm font-semibold text-slate-700">Subscription health</div>
                            <div class="text-xs text-slate-500">Based on subscriptionManagement + history tables.</div>
                        </div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Active subscriptions</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $stats['active'] ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Expiring in 7 days</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $stats['expiring_7d'] ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Expired</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $stats['expired'] ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">All-time revenue</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $revenueTotal }}</div>
                                <div class="mt-1 text-xs text-slate-500">Web: {{ $revenueWeb }} | App: {{ $revenueApp }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Revenue this month</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $revenueMonth }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Active MRR (current)</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $activeMrr }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Successful payments</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $stats['success_count'] ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Payment errors</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $stats['error_count'] ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Cancelled</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $stats['cancel_count'] ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Click to pay</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $stats['pending_count'] ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Basic plan</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $stats['plan_basic'] ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Pro plan</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $stats['plan_pro'] ?? 0 }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold text-slate-500">Unknown plan</div>
                                <div class="mt-2 text-xl font-semibold text-slate-700">{{ $stats['plan_unknown'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>

                    @php
                        $activeSubscriptions = $activeSubscriptions ?? collect();
                        $activeWebCount = $activeWebCount ?? 0;
                        $activeAppCount = $activeAppCount ?? 0;
                        $activeTotal = $activeSubscriptions->count();
                        $nowTs = time();
                    @endphp

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm font-semibold text-slate-700">Active subscriptions</div>
                            <div class="text-xs text-slate-500">Total: {{ $activeTotal }} (Web: {{ $activeWebCount }}, App: {{ $activeAppCount }}).</div>
                        </div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            @forelse ($activeSubscriptions as $sub)
                                @php
                                    $subscribeValue = $sub->subscribe ?? null;
                                    $planLabel = 'Free';
                                    if ((int) $subscribeValue === 1) {
                                        $planLabel = 'Basic';
                                    } elseif ((int) $subscribeValue === 2) {
                                        $planLabel = 'Pro';
                                    } elseif ($subscribeValue === null) {
                                        $planLabel = 'Unknown';
                                    }

                                    $priceValue = $sub->subscribe_price ?? null;
                                    $priceLabel = is_numeric($priceValue) ? '$' . number_format((float) $priceValue, 2, '.', '') : '-';

                                    $startLabel = is_numeric($sub->subscribe_start ?? null)
                                        ? date('Y-m-d', (int) $sub->subscribe_start)
                                        : '-';
                                    $expireLabel = is_numeric($sub->subscribe_expire ?? null)
                                        ? date('Y-m-d', (int) $sub->subscribe_expire)
                                        : '-';

                                    $daysLeft = is_numeric($sub->subscribe_expire ?? null)
                                        ? (int) ceil(((int) $sub->subscribe_expire - $nowTs) / 86400)
                                        : null;
                                    $daysLeft = $daysLeft !== null ? max($daysLeft, 0) : null;

                                    $imageValue = $sub->image ?? null;
                                    $imageUrl = $imageValue ? 'https://www.language.onllyons.com/ru/ru-en/dist/images/user-images/' . ltrim($imageValue, '/') : null;
                                    $userLabel = $sub->username ?? $sub->user_id ?? 'User';
                                    $profileUrl = url('/users/' . ($sub->user_id ?? ''));
                                    $sourceLabel = $sub->source ?? 'Web';
                                    $sourceBadgeClass = $sourceLabel === 'App'
                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                        : 'border-sky-200 bg-sky-50 text-sky-700';
                                @endphp
                                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold {{ $sourceBadgeClass }}">{{ $sourceLabel }}</span>
                                        <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold text-slate-600">{{ $planLabel }}</span>
                                    </div>
                                    <div class="mt-3 flex items-center gap-3">
                                        <a href="{{ $profileUrl }}">
                                            @if ($imageUrl)
                                                <img class="h-11 w-11 rounded-full object-cover bg-slate-100" src="{{ $imageUrl }}" alt="{{ $userLabel }}" loading="lazy" />
                                            @else
                                                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-200 text-xs font-semibold text-slate-600">
                                                    {{ strtoupper(substr((string) $userLabel, 0, 1)) }}
                                                </div>
                                            @endif
                                        </a>
                                        <div>
                                            <a class="text-sm font-semibold text-slate-800 hover:underline" href="{{ $profileUrl }}">{{ $userLabel }}</a>
                                            @if (!empty($sub->name))
                                                <div class="text-xs text-slate-500">{{ $sub->name }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mt-3 grid gap-1 text-xs text-slate-600">
                                        <div><span class="font-semibold text-slate-700">Price:</span> {{ $priceLabel }}</div>
                                        <div><span class="font-semibold text-slate-700">Start:</span> {{ $startLabel }}</div>
                                        <div><span class="font-semibold text-slate-700">Expires:</span> {{ $expireLabel }}</div>
                                        <div><span class="font-semibold text-slate-700">Days left:</span> {{ $daysLeft !== null ? $daysLeft : '-' }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6 text-sm text-slate-500">
                                    No active subscriptions found.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="text-sm font-semibold text-slate-700">Subscription pages</div>
                        <div class="mt-3 flex flex-wrap gap-2 rounded-xl border border-slate-200 bg-slate-50 p-2">
                            <a class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:border-slate-300" href="{{ url('/subscription-history') }}">
                                subscriptionHistory
                            </a>
                            <a class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:border-slate-300" href="{{ url('/subscription-history-app') }}">
                                subscriptionHistoryApp
                            </a>
                            <a class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:border-slate-300" href="{{ url('/subscription-management') }}">
                                subscriptionManagement
                            </a>
                        </div>
                    </div>

                    @php
                        $subscriptionTables = [
                            'subscriptionHistory',
                            'subscriptionHistoryApp',
                            'subscriptionManagement',
                            'subscriptionManagementGift',
                            'subscriptionRCProductIds',
                            'subscriptionStripePlanIds',
                            'subscriptionType',
                        ];
                    @endphp

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm font-semibold text-slate-700">MySQL tables</div>
                            <div class="text-xs text-slate-500">Description is empty for now.</div>
                        </div>
                        <div class="mt-3 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-slate-500">
                                        <th class="pb-2">Table name</th>
                                        <th class="pb-2">Description</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($subscriptionTables as $tableName)
                                        <tr>
                                            <td class="py-2 font-semibold text-slate-700">{{ $tableName }}</td>
                                            <td class="py-2 text-slate-500"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
    </body>
</html>
