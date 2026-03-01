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
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <a class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100" href="{{ url('/all-lessons') }}">
                                        All lessons
                                    </a>
                                    <a class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100" href="{{ url('/course-history') }}">
                                        course_history
                                    </a>
                                    <a class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100" href="{{ route('course.integrity') }}">
                                        debut integrity
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

                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Total categories</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['categories'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Total lessons</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['lessons'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Total carousel items</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['carousel'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="text-xs font-semibold text-slate-500">Total test items</div>
                            <div class="mt-2 text-xl font-semibold text-slate-700">{{ $summary['tests'] ?? 0 }}</div>
                        </div>
                    </div>

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
                                            $lessonCrmUrl = route('lesson.show', ['slug' => $lesson->url]);
                                        @endphp
                                        <li class="rounded-lg border border-slate-200 p-3">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <a href="{{ $lessonCrmUrl }}" class="text-sm font-semibold text-slate-700 hover:text-sky-700 hover:underline">
                                                        {{ $lesson->title }}
                                                    </a>
                                                    <a href="{{ $lessonCrmUrl }}" class="mt-1 block text-xs text-slate-500 hover:text-sky-700 hover:underline">
                                                        {{ $lesson->url }}
                                                    </a>
                                                    <div class="mt-1 text-[11px] text-slate-400">ID: {{ $lesson->id }}</div>
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
                                            <div class="mt-3 flex justify-end">
                                                <button
                                                    type="button"
                                                    data-course-edit-button
                                                    data-course-id="{{ $lesson->id }}"
                                                    data-course-title="{{ $lesson->title }}"
                                                    data-course-url="{{ $lesson->url }}"
                                                    data-course-category-url="{{ $lesson->category_url }}"
                                                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100"
                                                >
                                                    Edit course #{{ $lesson->id }}
                                                </button>
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

        <div id="course-edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
            <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <h2 id="course-edit-title" class="text-lg font-semibold text-slate-800">Edit course</h2>
                        <p class="mt-1 text-xs text-slate-500">Simple edit for selected course row.</p>
                    </div>
                    <button type="button" data-course-edit-close class="rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                        Close
                    </button>
                </div>

                <form id="course-edit-form" method="POST" action="">
                    @csrf
                    <div class="grid gap-3 md:grid-cols-3">
                        <label class="text-xs font-semibold text-slate-600">
                            Title
                            <input id="course-edit-title-input" type="text" name="title" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm font-normal text-slate-700">
                        </label>
                        <label class="text-xs font-semibold text-slate-600">
                            URL
                            <input id="course-edit-url-input" type="text" name="url" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm font-normal text-slate-700">
                        </label>
                        <label class="text-xs font-semibold text-slate-600">
                            Category URL
                            <input id="course-edit-category-url-input" type="text" name="category_url" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm font-normal text-slate-700">
                        </label>
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" data-course-edit-close class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
        <script>
            (function () {
                const modal = document.getElementById('course-edit-modal');
                const form = document.getElementById('course-edit-form');
                const titleText = document.getElementById('course-edit-title');
                const titleInput = document.getElementById('course-edit-title-input');
                const urlInput = document.getElementById('course-edit-url-input');
                const categoryUrlInput = document.getElementById('course-edit-category-url-input');
                const editButtons = document.querySelectorAll('[data-course-edit-button]');
                const closeButtons = document.querySelectorAll('[data-course-edit-close]');
                const updateUrlTemplate = @json(url('/course/__ID__/update'));

                if (!modal || !form || editButtons.length === 0) {
                    return;
                }

                const openModal = (payload) => {
                    const id = String(payload?.id ?? '').trim();
                    if (id === '') {
                        return;
                    }

                    form.setAttribute('action', updateUrlTemplate.replace('__ID__', encodeURIComponent(id)));
                    if (titleText) {
                        titleText.textContent = `Edit course #${id}`;
                    }
                    if (titleInput) {
                        titleInput.value = payload?.title ?? '';
                    }
                    if (urlInput) {
                        urlInput.value = payload?.url ?? '';
                    }
                    if (categoryUrlInput) {
                        categoryUrlInput.value = payload?.categoryUrl ?? '';
                    }

                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                };

                const closeModal = () => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                };

                editButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        openModal({
                            id: button.getAttribute('data-course-id'),
                            title: button.getAttribute('data-course-title'),
                            url: button.getAttribute('data-course-url'),
                            categoryUrl: button.getAttribute('data-course-category-url'),
                        });
                    });
                });

                closeButtons.forEach((button) => {
                    button.addEventListener('click', closeModal);
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
