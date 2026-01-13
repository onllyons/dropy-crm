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

                    @if ($user)
                        @php
                            $imageValue = $user->image ?? null;
                            $imageUrl = $imageValue ? 'https://www.language.onllyons.com/ru/ru-en/dist/images/user-images/' . ltrim($imageValue, '/') : null;
                            $timeLabel = $user->time_label ?? null;
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
                    @endif
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
    </body>
</html>
