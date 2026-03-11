<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="User details" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="User details" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold">User profile</h1>
                                <p class="mt-2 text-sm text-slate-600">Detailed profile and status.</p>
                            </div>
                            <a class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:border-slate-300" href="{{ url('/users') }}">Back to users</a>
                        </div>
                    </div>

                    @if ($error)
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ $error }}
                        </div>
                    @endif
                    @if (session('status'))
                        <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    @if ($user)
                        @php
                            $imageValue = $user->image ?? null;
                            $imageUrl = $imageValue ? 'https://www.language.onllyons.com/ru/ru-en/dist/images/user-images/' . ltrim($imageValue, '/') : null;
                            $timeLabel = $user->time_label ?? null;
                            $subscriptionRows = $subscriptionRows ?? collect();
                            $subscriptionError = $subscriptionError ?? null;
                            $subscriptionGiftRows = $subscriptionGiftRows ?? collect();
                            $subscriptionGiftError = $subscriptionGiftError ?? null;
                            $userActionCooldownRows = $userActionCooldownRows ?? collect();
                            $userActionCooldownError = $userActionCooldownError ?? null;
                            $gameActionCooldownRows = $gameActionCooldownRows ?? collect();
                            $gameActionCooldownError = $gameActionCooldownError ?? null;
                            $cooldownFilter = $cooldownFilter ?? 'all';
                            $cooldownFilterOptions = $cooldownFilterOptions ?? ['all' => 'All', 'active' => 'Only active'];
                            $flashCardsProgress = $flashCardsProgress ?? [
                                'summary' => [],
                                'moduleProgress' => collect(),
                                'recentAttempts' => collect(),
                                'error' => null,
                            ];
                            $toEuropeanDateTime = function ($value) {
                                if ($value === null || $value === '') {
                                    return '-';
                                }
                                if (is_numeric($value)) {
                                    $ts = (int) $value;
                                    return $ts > 0 ? date('d.m.Y H:i:s', $ts) : '-';
                                }
                                $ts = strtotime((string) $value);
                                return $ts ? date('d.m.Y H:i:s', $ts) : (string) $value;
                            };
                            $formatDuration = function ($seconds) {
                                $total = max(0, (int) $seconds);
                                $hours = intdiv($total, 3600);
                                $minutes = intdiv($total % 3600, 60);
                                $rest = $total % 60;

                                if ($hours > 0) {
                                    return sprintf('%dh %02dm', $hours, $minutes);
                                }

                                if ($minutes > 0) {
                                    return sprintf('%dm %02ds', $minutes, $rest);
                                }

                                return sprintf('%ds', $rest);
                            };
                        @endphp
                        <div class="mt-6 grid gap-6 lg:grid-cols-[260px,1fr]">
                            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                <div class="flex items-center justify-center">
                                    @if ($imageUrl)
                                        <img class="h-48 w-48 rounded-2xl object-cover bg-slate-100" src="{{ $imageUrl }}" alt="{{ $user->username ?? 'User' }}" loading="lazy" />
                                    @else
                                        <div class="flex h-48 w-48 items-center justify-center rounded-2xl bg-slate-200 text-2xl font-semibold text-slate-600">
                                            {{ strtoupper(substr($user->username ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="mt-4 text-center">
                                    <div class="text-lg font-semibold text-slate-800">{{ $user->name ?? $user->username ?? 'User' }}</div>
                                    @if (!empty($user->surname))
                                        <div class="text-sm text-slate-500">{{ $user->surname }}</div>
                                    @endif
                                    @if (!empty($user->email))
                                        <div class="mt-2 text-xs text-slate-500">{{ $user->email }}</div>
                                    @endif
                                </div>
                                <div class="mt-4 flex flex-wrap justify-center gap-2">
                                    <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600">Level {{ $user->level ?? '-' }}</span>
                                    <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ ($user->verified ?? 0) > 0 ? 'Verified' : 'Not verified' }}</span>
                                    @if (($user->byGoogle ?? 0) > 0)
                                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Google</span>
                                    @endif
                                    @if (($user->appleUser ?? 0) > 0)
                                        <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-700">Apple</span>
                                    @endif
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                <div class="text-sm font-semibold text-slate-700">Profile details</div>
                                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <div class="text-xs font-semibold text-slate-500">User ID</div>
                                        <div class="mt-1 text-sm text-slate-700">#{{ $user->id }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-500">Username</div>
                                        <div class="mt-1 text-sm text-slate-700">{{ $user->username ?? '-' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-500">Name</div>
                                        <div class="mt-1 text-sm text-slate-700">{{ $user->name ?? '-' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-500">Surname</div>
                                        <div class="mt-1 text-sm text-slate-700">{{ $user->surname ?? '-' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-500">Email</div>
                                        <div class="mt-1 text-sm text-slate-700">{{ $user->email ?? '-' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-500">Level</div>
                                        <div class="mt-1 text-sm text-slate-700">{{ $user->level ?? '-' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-500">Verified</div>
                                        <div class="mt-1 text-sm text-slate-700">{{ ($user->verified ?? 0) > 0 ? 'Yes' : 'No' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-500">Joined</div>
                                        <div class="mt-1 text-sm text-slate-700">{{ $timeLabel ?? '-' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-500">Google user</div>
                                        <div class="mt-1 text-sm text-slate-700">{{ ($user->byGoogle ?? 0) > 0 ? 'Yes' : 'No' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-500">Apple user</div>
                                        <div class="mt-1 text-sm text-slate-700">{{ ($user->appleUser ?? 0) > 0 ? 'Yes' : 'No' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-500">Profile access</div>
                                        <div class="mt-1 text-sm text-slate-700">{{ $user->profileAccess ?? '-' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-500">Image file</div>
                                        <div class="mt-1 break-all text-sm text-slate-700">{{ $user->image ?? '-' }}</div>
                                    </div>
                                </div>

                                @if (!empty($user->bio))
                                    <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                                        <div class="text-xs font-semibold text-slate-500">Bio</div>
                                        <div class="mt-2 whitespace-pre-line">{{ $user->bio }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Profile quick menu (template)</div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <a class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-slate-300 hover:bg-white" href="{{ route('users.behavior', ['id' => $user->id]) }}">website/app views</a>
                                <a class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-slate-300 hover:bg-white" href="{{ route('users.course-history', ['id' => $user->id]) }}">course history</a>
                                <a class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-slate-300 hover:bg-white" href="{{ route('flash-cards.v2.progress', ['q' => $user->id]) }}">flash-cards v2 progress</a>
                                <a class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-slate-300 hover:bg-white">Nav 3</a>
                            </div>
                        </div>

                        @php
                            $flashSummary = $flashCardsProgress['summary'] ?? [];
                            $flashModuleProgress = $flashCardsProgress['moduleProgress'] ?? collect();
                            $flashRecentAttempts = $flashCardsProgress['recentAttempts'] ?? collect();
                            $flashCardsError = $flashCardsProgress['error'] ?? null;
                        @endphp
                        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-slate-700">Flash-cards V2 progress</div>
                                    <div class="mt-2 text-xs text-slate-500">Read-only overview from flashcard_lesson_attempts for user_id = {{ $user->id }}</div>
                                </div>
                                <a class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-slate-300 hover:bg-white" href="{{ route('flash-cards.v2.progress', ['q' => $user->id]) }}">
                                    Open full progress page
                                </a>
                            </div>

                            @if ($flashCardsError)
                                <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                    {{ $flashCardsError }}
                                </div>
                            @endif

                            <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Completed lessons</div>
                                    <div class="mt-2 text-xl font-semibold text-slate-800">{{ number_format((int) ($flashSummary['completed_lessons'] ?? 0)) }}</div>
                                    <div class="mt-1 text-xs text-slate-500">from {{ number_format((int) ($flashSummary['catalog_lessons_total'] ?? 0)) }}</div>
                                </div>
                                <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-sky-700">Progress</div>
                                    <div class="mt-2 text-xl font-semibold text-sky-700">{{ number_format((float) ($flashSummary['progress_percent'] ?? 0), 1) }}%</div>
                                    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-white/70">
                                        <div class="h-full rounded-full bg-sky-500" style="width: {{ max(0, min(100, (float) ($flashSummary['progress_percent'] ?? 0))) }}%;"></div>
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Attempts</div>
                                    <div class="mt-2 text-xl font-semibold text-emerald-700">{{ number_format((int) ($flashSummary['attempts_total'] ?? 0)) }}</div>
                                    <div class="mt-1 text-xs text-emerald-700">Completed: {{ number_format((int) ($flashSummary['completed_attempts'] ?? 0)) }}</div>
                                </div>
                                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-amber-700">In progress</div>
                                    <div class="mt-2 text-xl font-semibold text-amber-700">{{ number_format((int) ($flashSummary['in_progress_attempts'] ?? 0)) }}</div>
                                    <div class="mt-1 text-xs text-amber-700">Started lessons: {{ number_format((int) ($flashSummary['started_lessons'] ?? 0)) }}</div>
                                </div>
                                <div class="rounded-2xl border border-violet-200 bg-violet-50 p-4">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-violet-700">Accuracy</div>
                                    <div class="mt-2 text-xl font-semibold text-violet-700">
                                        @if (($flashSummary['accuracy_percent'] ?? null) !== null)
                                            {{ number_format((float) $flashSummary['accuracy_percent'], 1) }}%
                                        @else
                                            -
                                        @endif
                                    </div>
                                    <div class="mt-1 text-xs text-violet-700">
                                        {{ number_format((int) ($flashSummary['answers_correct'] ?? 0)) }}/{{ number_format((int) ($flashSummary['questions_total'] ?? 0)) }}
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tracked time</div>
                                    <div class="mt-2 text-xl font-semibold text-slate-800">{{ $formatDuration($flashSummary['total_time_seconds'] ?? 0) }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ trim((string) ($flashSummary['last_activity_at'] ?? '')) !== '' ? $flashSummary['last_activity_at'] : 'No activity yet' }}</div>
                                </div>
                            </div>

                            <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.05fr)_minmax(0,.95fr)]">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-sm font-semibold text-slate-700">Progress by module</div>
                                    <div class="mt-3 overflow-x-auto">
                                        <table class="min-w-full text-sm">
                                            <thead>
                                                <tr class="text-left text-slate-500">
                                                    <th class="pb-2">Module</th>
                                                    <th class="pb-2">Completed</th>
                                                    <th class="pb-2">Attempts</th>
                                                    <th class="pb-2">Time</th>
                                                    <th class="pb-2">Progress</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                                @forelse ($flashModuleProgress as $row)
                                                    <tr>
                                                        <td class="py-2 text-slate-700">
                                                            <a href="{{ route('flash-cards.v2.module', ['moduleId' => (int) ($row->module_id ?? 0)]) }}" class="hover:text-sky-700 hover:underline">
                                                                {{ trim((string) ($row->module_title ?? '')) !== '' ? $row->module_title : ('Module #' . (int) ($row->module_id ?? 0)) }}
                                                            </a>
                                                        </td>
                                                        <td class="py-2 text-slate-700">{{ (int) ($row->completed_lessons ?? 0) }}/{{ (int) ($row->lessons_total ?? 0) }}</td>
                                                        <td class="py-2 text-slate-700">{{ number_format((int) ($row->attempts_total ?? 0)) }}</td>
                                                        <td class="py-2 text-slate-700">{{ $formatDuration($row->total_time_seconds ?? 0) }}</td>
                                                        <td class="py-2 text-slate-700">{{ number_format((float) ($row->progress_percent ?? 0), 1) }}%</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td class="py-3 text-slate-500" colspan="5">No flash-cards progress yet.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-sm font-semibold text-slate-700">Latest attempts</div>
                                    <div class="mt-3 space-y-3">
                                        @forelse ($flashRecentAttempts as $row)
                                            @php
                                                $attemptStatusClass = (string) ($row->status ?? '') === 'completed'
                                                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                                    : ((string) ($row->status ?? '') === 'in_progress'
                                                        ? 'border-amber-200 bg-amber-50 text-amber-700'
                                                        : 'border-slate-200 bg-white text-slate-700');
                                            @endphp
                                            <article class="rounded-xl border border-slate-200 bg-white p-3">
                                                <div class="flex flex-wrap items-start justify-between gap-2">
                                                    <div>
                                                        <a href="{{ route('flash-cards.v2.lesson', ['lessonId' => (int) ($row->lesson_id ?? 0)]) }}" class="font-semibold text-slate-800 hover:text-sky-700 hover:underline">
                                                            {{ trim((string) ($row->lesson_title ?? '')) !== '' ? $row->lesson_title : ('Lesson #' . (int) ($row->lesson_id ?? 0)) }}
                                                        </a>
                                                        <div class="mt-1 text-xs text-slate-500">
                                                            {{ trim((string) ($row->module_title ?? '')) !== '' ? $row->module_title : '-' }}
                                                            @if (trim((string) ($row->lesson_level ?? '')) !== '')
                                                                · {{ $row->lesson_level }}
                                                            @endif
                                                            @if (trim((string) ($row->lesson_type ?? '')) !== '')
                                                                · {{ $row->lesson_type }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <span class="rounded-full border px-2 py-0.5 text-xs font-semibold {{ $attemptStatusClass }}">
                                                        {{ trim((string) ($row->status ?? '')) !== '' ? $row->status : 'unknown' }}
                                                    </span>
                                                </div>
                                                <div class="mt-3 grid gap-2 text-xs text-slate-600 sm:grid-cols-2">
                                                    <div>Attempt #{{ (int) ($row->attempt_number ?? 0) }} · time {{ $formatDuration($row->total_time_seconds ?? 0) }}</div>
                                                    <div>
                                                        Quiz:
                                                        {{ number_format((int) ($row->answers_correct ?? 0)) }}/{{ number_format((int) ($row->questions_total ?? 0)) }}
                                                        @if (($row->accuracy_percent ?? null) !== null)
                                                            ({{ number_format((float) $row->accuracy_percent, 1) }}%)
                                                        @endif
                                                    </div>
                                                    <div>Started: {{ trim((string) ($row->started_at ?? '')) !== '' ? $row->started_at : '-' }}</div>
                                                    <div>Completed: {{ trim((string) ($row->completed_at ?? '')) !== '' ? $row->completed_at : 'Not completed' }}</div>
                                                </div>
                                            </article>
                                        @empty
                                            <div class="rounded-xl border border-dashed border-slate-200 bg-white p-4 text-sm text-slate-500">
                                                No attempts for this user.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                            @php
                                $subscriptionCount = $subscriptionRows->count();
                                $currentSubscription = $subscriptionRows->first();
                            @endphp
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-slate-700">Current subscription</div>
                                    <div class="mt-2 text-xs text-slate-500">Data from subscriptionManagement for user_id = {{ $user->id }}</div>
                                    <div class="mt-1 text-xs text-slate-500">Acest tabel raspunde pentru abonamentul curent al utilizatorului.</div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @if ($currentSubscription)
                                        <form
                                            method="POST"
                                            action="{{ route('users.subscription.expire-current', ['id' => $user->id]) }}"
                                            onsubmit="return confirm('Expire current subscription now? This will only move expire date into the past.')"
                                        >
                                            @csrf
                                            <button
                                                type="submit"
                                                class="rounded-lg border border-rose-300 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100"
                                            >
                                                Expire current subscription
                                            </button>
                                        </form>
                                    @endif
                                    <button
                                        type="button"
                                        id="grantProOpenButton"
                                        class="rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-100"
                                    >
                                        Give Pro subscription
                                    </button>
                                </div>
                            </div>

                            @if ($subscriptionError)
                                <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                    {{ $subscriptionError }}
                                </div>
                            @endif

                            @if ($currentSubscription)
                                @php
                                    $planCode = (int) ($currentSubscription->subscribe ?? 0);
                                    $planLabel = $planCode === 1 ? 'Basic' : ($planCode === 2 ? 'Pro' : 'Free/unknown');
                                    $startTs = is_numeric($currentSubscription->subscribe_start ?? null) ? (int) $currentSubscription->subscribe_start : null;
                                    $expireTs = is_numeric($currentSubscription->subscribe_expire ?? null) ? (int) $currentSubscription->subscribe_expire : null;
                                    $startLabel = $toEuropeanDateTime($currentSubscription->subscribe_start ?? null);
                                    $expireLabel = $toEuropeanDateTime($currentSubscription->subscribe_expire ?? null);
                                    $isActive = $expireTs ? $expireTs >= time() : false;
                                    $daysLeft = $expireTs ? max((int) ceil(($expireTs - time()) / 86400), 0) : null;
                                    $hasActiveGiftSubscription = $subscriptionGiftRows->contains(function ($giftRow) {
                                        return is_numeric($giftRow->subscribe_expire ?? null)
                                            && (int) $giftRow->subscribe_expire > time();
                                    });
                                @endphp

                                <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div class="text-sm font-semibold text-slate-800">Row #{{ $currentSubscription->id }}</div>
                                        <span class="{{ $isActive ? 'rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700' : 'rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700' }}">
                                            {{ $isActive ? 'Active' : 'Expired' }}
                                        </span>
                                    </div>
                                    <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                        <div>
                                            <div class="text-xs font-semibold text-slate-500">Plan</div>
                                            <div class="mt-1 text-sm text-slate-700">{{ $planLabel }} ({{ $planCode }})</div>
                                        </div>
                                        <div>
                                            <div class="text-xs font-semibold text-slate-500">Start</div>
                                            <div class="mt-1 text-sm text-slate-700">{{ $startLabel }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs font-semibold text-slate-500">Expire</div>
                                            <div class="mt-1 text-sm text-slate-700">{{ $expireLabel }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs font-semibold text-slate-500">Days left</div>
                                            <div class="mt-1 text-sm text-slate-700">{{ $daysLeft !== null ? $daysLeft : '-' }}</div>
                                        </div>
                                    </div>
                                </div>

                                @if ($hasActiveGiftSubscription)
                                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                                        Active gift subscription exists. Even if you expire the current subscription row, user may still have access via <code>subscriptionManagementGift</code>.
                                    </div>
                                @endif

                                @if ($subscriptionCount > 1)
                                    <div class="mt-4">
                                        <div class="text-xs font-semibold text-slate-500">Other rows (history): {{ $subscriptionCount - 1 }}</div>
                                        <div class="mt-2 space-y-2">
                                            @foreach ($subscriptionRows->slice(1) as $row)
                                                @php
                                                    $historyPlanCode = (int) ($row->subscribe ?? 0);
                                                    $historyPlanLabel = $historyPlanCode === 1 ? 'Basic' : ($historyPlanCode === 2 ? 'Pro' : 'Free/unknown');
                                                    $historyStartLabel = $toEuropeanDateTime($row->subscribe_start ?? null);
                                                    $historyExpireLabel = $toEuropeanDateTime($row->subscribe_expire ?? null);
                                                @endphp
                                                <div class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700">
                                                    Row #{{ $row->id }} | {{ $historyPlanLabel }} ({{ $historyPlanCode }}) | start: {{ $historyStartLabel }} | expire: {{ $historyExpireLabel }}
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="mt-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-4 text-sm text-slate-500">
                                    No rows in subscriptionManagement for this user.
                                </div>
                            @endif
                        </div>

                        <div id="grantProModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
                            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                                <div class="mb-4 flex items-start justify-between gap-3">
                                    <div>
                                        <h2 class="text-lg font-semibold text-slate-800">Give Pro subscription</h2>
                                        <p class="mt-1 text-xs text-slate-500">User #{{ $user->id }} - {{ $user->username ?? '-' }}</p>
                                    </div>
                                    <button type="button" data-grant-pro-close class="rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                        Close
                                    </button>
                                </div>

                                <form method="POST" action="{{ route('users.subscription.grant-pro', ['id' => $user->id]) }}">
                                    @csrf
                                    <div class="text-xs font-semibold text-slate-600">Quick select</div>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <button type="button" data-days-preset="7" class="rounded-full border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">7 days</button>
                                        <button type="button" data-days-preset="30" class="rounded-full border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">30 days</button>
                                        <button type="button" data-days-preset="90" class="rounded-full border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">90 days</button>
                                        <button type="button" data-days-preset="365" class="rounded-full border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">365 days</button>
                                    </div>

                                    <label class="mt-4 block text-xs font-semibold text-slate-600">
                                        Days
                                        <input
                                            id="grantProDaysInput"
                                            type="number"
                                            name="days"
                                            value="30"
                                            min="1"
                                            max="3650"
                                            required
                                            class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700"
                                        >
                                    </label>
                                    <div class="mt-4 flex justify-end gap-2">
                                        <button type="button" data-grant-pro-close class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                            Cancel
                                        </button>
                                        <button type="submit" class="rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                                            Give Pro
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="text-sm font-semibold text-slate-700">Gift subscriptions</div>
                            <div class="mt-2 text-xs text-slate-500">Rows from subscriptionManagementGift for user_id = {{ $user->id }}</div>

                            @if ($subscriptionGiftError)
                                <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                    {{ $subscriptionGiftError }}
                                </div>
                            @endif

                            <div class="mt-4 overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-left text-slate-500">
                                            <th class="pb-2">ID</th>
                                            <th class="pb-2">user_id</th>
                                            <th class="pb-2">subscribe_start</th>
                                            <th class="pb-2">subscribe_expire</th>
                                            <th class="pb-2">Duration</th>
                                            <th class="pb-2">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse ($subscriptionGiftRows as $row)
                                            @php
                                                $giftStartRaw = $row->subscribe_start ?? null;
                                                $giftExpireRaw = $row->subscribe_expire ?? null;
                                                $giftStartTs = is_numeric($giftStartRaw) ? (int) $giftStartRaw : strtotime((string) $giftStartRaw);
                                                $giftExpireTs = is_numeric($giftExpireRaw) ? (int) $giftExpireRaw : strtotime((string) $giftExpireRaw);
                                                $giftStartTs = $giftStartTs ?: null;
                                                $giftExpireTs = $giftExpireTs ?: null;
                                                $giftStartLabel = $toEuropeanDateTime($row->subscribe_start ?? null);
                                                $giftExpireLabel = $toEuropeanDateTime($row->subscribe_expire ?? null);
                                                $giftIsActive = $giftExpireTs ? $giftExpireTs >= time() : false;
                                                $giftDurationLabel = '-';
                                                if ($giftStartTs && $giftExpireTs && $giftExpireTs >= $giftStartTs) {
                                                    $durationSeconds = $giftExpireTs - $giftStartTs;
                                                    $durationDays = (int) floor($durationSeconds / 86400);
                                                    $durationHours = (int) floor(($durationSeconds % 86400) / 3600);
                                                    $durationMinutes = (int) floor(($durationSeconds % 3600) / 60);
                                                    $giftDurationLabel = $durationDays > 0
                                                        ? $durationDays . 'd ' . $durationHours . 'h'
                                                        : $durationHours . 'h ' . $durationMinutes . 'm';
                                                }
                                            @endphp
                                            <tr>
                                                <td class="py-2 text-slate-700">{{ $row->id }}</td>
                                                <td class="py-2 text-slate-600">{{ $row->user_id }}</td>
                                                <td class="py-2 text-slate-600">{{ $giftStartLabel }}</td>
                                                <td class="py-2 text-slate-600">{{ $giftExpireLabel }}</td>
                                                <td class="py-2 text-slate-600">{{ $giftDurationLabel }}</td>
                                                <td class="py-2">
                                                    <span class="{{ $giftIsActive ? 'rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700' : 'rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700' }}">
                                                        {{ $giftIsActive ? 'Active' : 'Expired' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="py-3 text-slate-500" colspan="6">No rows in subscriptionManagementGift for this user.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="flex flex-wrap items-end justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-slate-700">Cooldown</div>
                                    <div class="mt-2 text-xs text-slate-500">Read-only data from user_action_cooldown and game_action_cooldown.</div>
                                </div>
                                <form method="GET" action="{{ url('/users/' . $user->id) }}" class="flex items-center gap-2">
                                    <label for="cooldown_filter" class="text-xs text-slate-600">Filter</label>
                                    <select id="cooldown_filter" name="cooldown_filter" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs text-slate-700" onchange="this.form.submit()">
                                        @foreach ($cooldownFilterOptions as $filterKey => $filterLabel)
                                            <option value="{{ $filterKey }}" {{ $cooldownFilter === $filterKey ? 'selected' : '' }}>{{ $filterLabel }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>

                            <div class="mt-5 text-sm font-semibold text-slate-700">user_action_cooldown</div>
                            <div class="mt-1 text-xs text-slate-500">Columns: action_type, counter, created, expire, hash, status</div>
                            @if ($userActionCooldownError)
                                <div class="mt-3 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                    {{ $userActionCooldownError }}
                                </div>
                            @endif
                            <div class="mt-3 overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-left text-slate-500">
                                            <th class="pb-2">action_type</th>
                                            <th class="pb-2">counter</th>
                                            <th class="pb-2">created</th>
                                            <th class="pb-2">expire</th>
                                            <th class="pb-2">hash</th>
                                            <th class="pb-2">status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse ($userActionCooldownRows as $row)
                                            @php
                                                $isActive = (int) ($row->is_active ?? 0) > 0;
                                                $createdLabel = $toEuropeanDateTime($row->created ?? null);
                                                $expireLabel = $toEuropeanDateTime($row->expire ?? null);
                                            @endphp
                                            <tr>
                                                <td class="py-2 text-slate-700">{{ $row->action_type ?? '-' }}</td>
                                                <td class="py-2 text-slate-700">{{ is_numeric($row->counter ?? null) ? (int) $row->counter : ($row->counter ?? '-') }}</td>
                                                <td class="py-2 text-slate-700">{{ $createdLabel }}</td>
                                                <td class="py-2 text-slate-700">{{ $expireLabel }}</td>
                                                <td class="py-2 text-slate-600">{{ $row->hash ?? '-' }}</td>
                                                <td class="py-2">
                                                    <span class="{{ $isActive ? 'rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700' : 'rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700' }}">
                                                        {{ $isActive ? 'Active' : 'Expired' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="py-3 text-slate-500" colspan="6">No cooldown records</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-6 text-sm font-semibold text-slate-700">game_action_cooldown</div>
                            <div class="mt-1 text-xs text-slate-500">Columns: action_type, counter (decoded JSON), hoursAtStart, reason, created, status</div>
                            @if ($gameActionCooldownError)
                                <div class="mt-3 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                    {{ $gameActionCooldownError }}
                                </div>
                            @endif
                            <div class="mt-3 overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-left text-slate-500">
                                            <th class="pb-2">action_type</th>
                                            <th class="pb-2">counter</th>
                                            <th class="pb-2">hoursAtStart</th>
                                            <th class="pb-2">reason</th>
                                            <th class="pb-2">created</th>
                                            <th class="pb-2">status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse ($gameActionCooldownRows as $row)
                                            @php
                                                $isActive = (int) ($row->is_active ?? 0) > 0;
                                                $createdLabel = $toEuropeanDateTime($row->created ?? null);
                                            @endphp
                                            <tr>
                                                <td class="py-2 text-slate-700">{{ $row->action_type ?? '-' }}</td>
                                                <td class="py-2 text-slate-700">{{ $row->counter_decoded ?? ($row->counter ?? '-') }}</td>
                                                <td class="py-2 text-slate-700">{{ is_numeric($row->hoursAtStart ?? null) ? (int) $row->hoursAtStart : ($row->hoursAtStart ?? '-') }}</td>
                                                <td class="py-2 text-slate-700">{{ $row->reason ?? '-' }}</td>
                                                <td class="py-2 text-slate-700">{{ $createdLabel }}</td>
                                                <td class="py-2">
                                                    <span class="{{ $isActive ? 'rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700' : 'rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700' }}">
                                                        {{ $isActive ? 'Active' : 'Expired' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="py-3 text-slate-500" colspan="6">No cooldown records</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    @endif
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
        <script>
            (function () {
                const modal = document.getElementById('grantProModal');
                const openButton = document.getElementById('grantProOpenButton');
                const closeButtons = document.querySelectorAll('[data-grant-pro-close]');
                const daysInput = document.getElementById('grantProDaysInput');
                const presetButtons = document.querySelectorAll('[data-days-preset]');

                if (!modal || !openButton || !daysInput) {
                    return;
                }

                const openModal = () => {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                };

                const closeModal = () => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                };

                openButton.addEventListener('click', openModal);

                closeButtons.forEach((button) => {
                    button.addEventListener('click', closeModal);
                });

                presetButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        const value = button.getAttribute('data-days-preset');
                        if (value) {
                            daysInput.value = value;
                        }
                    });
                });

                modal.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        closeModal();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                        closeModal();
                    }
                });
            })();
        </script>
    </body>
</html>
