<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Course" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Course" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold">Course</h1>
                                <p class="mt-2 text-sm text-slate-600">Overview by category and lesson count.</p>
                                <div class="mt-3">
                                    <a class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100" href="{{ url('/course-history') }}">
                                        course_history
                                    </a>
                                </div>
                            </div>
                            <div class="text-xs font-semibold text-slate-500">
                                DB: {{ session('tenant_db', config('dropy.tenants.default')) }}
                            </div>
                        </div>
                    </div>

                    @if ($error)
                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ $error }}
                        </div>
                    @endif

                    <div class="mt-6 space-y-4">
                        @forelse ($categories as $category)
                            @php
                                $code = $category->var_idtest_1_1;
                                $lessonList = $lessonsByCategory->get($code, collect());
                            @endphp
                            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <h2 class="text-lg font-semibold">{{ $category->var_idtest_1 }}</h2>
                                        <div class="mt-1 text-xs text-slate-500">Code: {{ $code }} · Level: {{ $category->var_idtest_3 }}</div>
                                    </div>
                                    <div class="flex flex-wrap gap-2 text-xs">
                                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1">Lessons: {{ $lessonList->count() }}</span>
                                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1">Carousel: {{ $carouselCounts->get($code, 0) }}</span>
                                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1">Tests: {{ $testCounts->get($code, 0) }}</span>
                                    </div>
                                </div>

                                <ul class="mt-4 grid gap-2 md:grid-cols-2">
                                    @forelse ($lessonList as $lesson)
                                        @php
                                            $carouselSeries = $carouselSeriesByLesson->get($lesson->url, collect());
                                            $testSeries = $testSeriesByLesson->get($lesson->url, collect());
                                            $seriesList = $carouselSeries->pluck('series')
                                                ->merge($testSeries->pluck('series'))
                                                ->unique()
                                                ->sort()
                                                ->values();
                                        @endphp
                                        <li class="rounded-lg border border-slate-200 p-3">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <div class="text-sm font-semibold text-slate-700">{{ $lesson->title }}</div>
                                                    <div class="mt-1 text-xs text-slate-500">{{ $lesson->url }}</div>
                                                </div>
                                                <div class="flex flex-col gap-1 text-[11px] text-slate-500">
                                                    <span>Carousel: {{ $carouselCountsByLesson->get($lesson->url, 0) }}</span>
                                                    <span>Tests: {{ $testCountsByLesson->get($lesson->url, 0) }}</span>
                                                </div>
                                            </div>
                                            <div class="mt-2 space-y-1 text-xs text-slate-500">
                                                @forelse ($seriesList as $series)
                                                    @php
                                                        $carouselCount = optional($carouselSeries->firstWhere('series', $series))->count ?? 0;
                                                        $testCount = optional($testSeries->firstWhere('series', $series))->count ?? 0;
                                                    @endphp
                                                    <div>Series {{ $series }}: Carousel {{ $carouselCount }} · Tests {{ $testCount }}</div>
                                                @empty
                                                    <div>No series data.</div>
                                                @endforelse
                                            </div>
                                        </li>
                                    @empty
                                        <li class="text-sm text-slate-500">No lessons for this category.</li>
                                    @endforelse
                                </ul>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 text-sm text-slate-600">
                                No categories found.
                            </div>
                        @endforelse
                    </div>
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
    </body>
</html>
