<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="All lessons" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="All lessons" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold">All lessons</h1>
                                <p class="mt-2 text-sm text-slate-600">All categories and lessons loaded from DB.</p>
                            </div>
                            <a class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:border-slate-300" href="{{ url('/course') }}">Back to course</a>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm font-semibold text-slate-700">All courses</div>
                            <div id="all-lessons-search-meta" class="text-xs text-slate-500">Container: #echo-contents-course</div>
                        </div>
                        <div class="mt-3">
                            <input
                                id="all-lessons-search"
                                type="text"
                                placeholder="Search category or lesson..."
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-0 transition placeholder:text-slate-400 focus:border-slate-400"
                            />
                        </div>
                        <div id="echo-contents-course" class="mt-4 space-y-4">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-500">
                                Loading lessons...
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
        <script>
            (function () {
                const container = document.getElementById('echo-contents-course');
                const searchInput = document.getElementById('all-lessons-search');
                const searchMeta = document.getElementById('all-lessons-search-meta');
                if (!container) {
                    return;
                }

                const endpointUrl = @json(route('all-lessons.content'));
                let loadedCourses = {};

                const escapeHtml = (value) => String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');

                const normalizeText = (value) => String(value ?? '').trim().toLowerCase();

                const getTotalLessons = (entries) => entries.reduce((sum, [, categoryData]) => {
                    const titles = Array.isArray(categoryData?.titles) ? categoryData.titles : [];
                    return sum + titles.length;
                }, 0);

                const renderError = (message) => {
                    container.innerHTML = `
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            ${escapeHtml(message)}
                        </div>
                    `;
                };

                const renderCourses = (courses, query = '') => {
                    const entries = Object.entries(courses || {});
                    const totalCategories = entries.length;
                    const totalLessons = getTotalLessons(entries);

                    if (searchMeta) {
                        if (query !== '') {
                            searchMeta.textContent = `Found ${totalLessons} lessons in ${totalCategories} categories`;
                        } else {
                            searchMeta.textContent = `Loaded ${totalLessons} lessons in ${totalCategories} categories`;
                        }
                    }

                    if (entries.length === 0) {
                        container.innerHTML = `
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-500">
                                ${query !== '' ? `No lessons found for "${escapeHtml(query)}".` : 'No lessons found.'}
                            </div>
                        `;
                        return;
                    }

                    const html = entries.map(([categoryName, categoryData]) => {
                        const titles = Array.isArray(categoryData?.titles) ? categoryData.titles : [];
                        const categoryCode = categoryData?.var_idtest_1_1 || '-';
                        const lessonItemsHtml = titles.map((lesson) => {
                            const lessonTitle = escapeHtml(lesson?.title || 'Untitled lesson');
                            const lessonUrl = encodeURIComponent(lesson?.url || '');
                            const isFinished = !!lesson?.isFinished;
                            const finishedBadge = isFinished
                                ? '<span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Finished</span>'
                                : '<span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold text-slate-600">Pending</span>';

                            return `
                                <a class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 transition hover:border-slate-300 hover:bg-slate-50" href="/lesson/${lessonUrl}">
                                    <span class="truncate">${lessonTitle}</span>
                                    ${finishedBadge}
                                </a>
                            `;
                        }).join('');

                        return `
                            <section class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <h2 class="text-base font-semibold text-slate-800">${escapeHtml(categoryName)}</h2>
                                        <div class="mt-1 text-xs text-slate-500">Code: ${escapeHtml(categoryCode)}</div>
                                    </div>
                                    <div class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700">
                                        Lessons: ${titles.length}
                                    </div>
                                </div>
                                <div class="mt-3 grid gap-2 md:grid-cols-2">
                                    ${lessonItemsHtml || '<div class="text-xs text-slate-500">No lessons in this category.</div>'}
                                </div>
                            </section>
                        `;
                    }).join('');

                    container.innerHTML = html;
                };

                const filterCourses = (courses, rawQuery) => {
                    const query = normalizeText(rawQuery);
                    if (query === '') {
                        return courses || {};
                    }

                    const filtered = {};
                    Object.entries(courses || {}).forEach(([categoryName, categoryData]) => {
                        const titles = Array.isArray(categoryData?.titles) ? categoryData.titles : [];
                        const categoryHaystack = normalizeText(`${categoryName} ${categoryData?.var_idtest_1_1 || ''}`);
                        const isCategoryMatch = categoryHaystack.includes(query);

                        if (isCategoryMatch) {
                            filtered[categoryName] = {
                                ...categoryData,
                                titles: titles,
                            };
                            return;
                        }

                        const matchedTitles = titles.filter((lesson) => {
                            const lessonHaystack = normalizeText(`${lesson?.title || ''} ${lesson?.url || ''}`);
                            return lessonHaystack.includes(query);
                        });

                        if (matchedTitles.length > 0) {
                            filtered[categoryName] = {
                                ...categoryData,
                                titles: matchedTitles,
                            };
                        }
                    });

                    return filtered;
                };

                const applySearch = () => {
                    const rawQuery = searchInput ? searchInput.value : '';
                    const filteredCourses = filterCourses(loadedCourses, rawQuery);
                    renderCourses(filteredCourses, rawQuery);
                };

                if (searchInput) {
                    searchInput.addEventListener('input', applySearch);
                }

                fetch(endpointUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                    },
                })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error('Failed to load lessons.');
                        }
                        return response.json();
                    })
                    .then((payload) => {
                        if (!payload || payload.success !== true) {
                            throw new Error(payload?.message || 'Invalid server response.');
                        }
                        loadedCourses = payload.courses || {};
                        applySearch();
                    })
                    .catch((error) => {
                        renderError(error?.message || 'Unknown error while loading lessons.');
                    });
            })();
        </script>
    </body>
</html>
