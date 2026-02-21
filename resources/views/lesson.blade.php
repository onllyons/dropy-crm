<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component :title="'Lesson: ' . ($lesson->title ?? $blog)" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav :title="'Lesson: ' . ($lesson->title ?? $blog)" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold">{{ $lesson->title ?? 'Lesson' }}</h1>
                                <p class="mt-2 text-sm text-slate-600">
                                    URL slug: <span class="font-semibold text-slate-800">{{ $blog }}</span>
                                    <span class="mx-2 text-slate-300">|</span>
                                    blog={{ $blog }}
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:border-slate-300" href="{{ url('/all-lessons') }}">Back to all lessons</a>
                                <a class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:border-slate-300" href="{{ url('/course') }}">Back to course</a>
                            </div>
                        </div>
                    </div>
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

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex flex-wrap items-end justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-slate-700">Lesson content (AJAX)</div>
                                <div class="mt-1 text-xs text-slate-500">Equivalent of get_carousel_and_test.php</div>
                            </div>
                        </div>

                        <div id="lesson-content" class="mt-4 space-y-4">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-500">
                                Loading lesson content...
                            </div>
                        </div>
                    </div>

                    <div id="course-test-edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
                        <div class="w-full max-w-4xl rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                            <div class="mb-4 flex items-start justify-between gap-3">
                                <div>
                                    <h2 id="course-test-edit-title" class="text-lg font-semibold text-slate-800">Edit course_test</h2>
                                    <p class="mt-1 text-xs text-slate-500">Edit selected row and save.</p>
                                </div>
                                <button type="button" data-course-test-edit-close class="rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                    Close
                                </button>
                            </div>

                            <form id="course-test-edit-form" method="POST" action="">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div id="course-test-edit-fields" class="grid gap-2 md:grid-cols-2"></div>
                                <div class="mt-4 flex justify-end gap-2">
                                    <button type="button" data-course-test-edit-close class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                        Cancel
                                    </button>
                                    <button type="submit" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                        Save
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />
        <script>
            (function () {
                const content = document.getElementById('lesson-content');
                if (!content) {
                    return;
                }

                const endpointUrl = @json(route('lesson.content', ['slug' => $blog]));
                const slug = @json($blog);
                const courseTestUpdateUrlTemplate = @json(url('/course-test/__ID__/update'));
                const testEditRows = new Map();
                const courseTestEditModal = document.getElementById('course-test-edit-modal');
                const courseTestEditForm = document.getElementById('course-test-edit-form');
                const courseTestEditTitle = document.getElementById('course-test-edit-title');
                const courseTestEditFields = document.getElementById('course-test-edit-fields');
                const courseTestEditCloseButtons = document.querySelectorAll('[data-course-test-edit-close]');
                const mediaBaseUrl = 'https://www.language.onllyons.com';
                const mediaCourseBasePath = '/ru/ru-en/packs/assest/course';
                const mediaVideoBasePath = `${mediaCourseBasePath}/video-lessons`;
                const mediaAudioBasePath = `${mediaCourseBasePath}/audio-lessons`;
                const testVideoBasePath = '/ru/ru-en/packs/assest/course/content/videos';
                const testImageBasePath = '/ru/ru-en/packs/assest/course/content/images';
                const testAudioBasePaths = [
                    '/ru/ru-en/packs/assest/course/content/audio',
                    '/ru/ru-en/packs/assest/course/content/audios',
                ];

                const escapeHtml = (value) => String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');

                const renderError = (message) => {
                    content.innerHTML = `
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            ${escapeHtml(message)}
                        </div>
                    `;
                };

                const sanitizeForDisplay = (value) => {
                    if (Array.isArray(value)) {
                        return value.map((item) => sanitizeForDisplay(item));
                    }

                    if (value && typeof value === 'object') {
                        return Object.entries(value).reduce((acc, [key, nestedValue]) => {
                            if (key === 'file_path') {
                                return acc;
                            }
                            acc[key] = sanitizeForDisplay(nestedValue);
                            return acc;
                        }, {});
                    }

                    return value;
                };

                const formatFieldLabel = (key) => String(key ?? '')
                    .replace(/_/g, ' ')
                    .replace(/\s+/g, ' ')
                    .trim()
                    .replace(/\b\w/g, (char) => char.toUpperCase());

                const formatFieldValue = (value) => {
                    if (value === null || value === undefined) {
                        return '-';
                    }

                    if (typeof value === 'string') {
                        return value.trim() !== '' ? value : '-';
                    }

                    if (Array.isArray(value)) {
                        if (value.length === 0) {
                            return '-';
                        }
                        return value.map((item) => formatFieldValue(item)).join(', ');
                    }

                    if (typeof value === 'object') {
                        const objectEntries = Object.entries(value);
                        if (objectEntries.length === 0) {
                            return '-';
                        }

                        return objectEntries
                            .slice(0, 4)
                            .map(([key, nestedValue]) => `${formatFieldLabel(key)}: ${formatFieldValue(nestedValue)}`)
                            .join(' | ');
                    }

                    return String(value);
                };

                const formatInputValue = (value) => {
                    if (value === null || value === undefined) {
                        return '';
                    }

                    if (typeof value === 'string') {
                        return value;
                    }

                    if (Array.isArray(value) || typeof value === 'object') {
                        try {
                            return JSON.stringify(value);
                        } catch (error) {
                            return '';
                        }
                    }

                    return String(value);
                };

                const closeCourseTestEditModal = () => {
                    if (!courseTestEditModal) {
                        return;
                    }

                    courseTestEditModal.classList.add('hidden');
                    courseTestEditModal.classList.remove('flex');
                };

                const buildEditableFieldHtml = (key, value) => {
                    const escapedKey = escapeHtml(key);
                    const label = escapeHtml(formatFieldLabel(key));
                    const inputValue = formatInputValue(value);
                    const escapedValue = escapeHtml(inputValue);
                    const useTextarea = inputValue.includes('\n') || inputValue.length > 120;

                    if (useTextarea) {
                        return `
                            <label class="text-xs font-semibold text-slate-600">
                                ${label}
                                <textarea name="${escapedKey}" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm font-normal text-slate-700">${escapedValue}</textarea>
                            </label>
                        `;
                    }

                    return `
                        <label class="text-xs font-semibold text-slate-600">
                            ${label}
                            <input type="text" name="${escapedKey}" value="${escapedValue}" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm font-normal text-slate-700">
                        </label>
                    `;
                };

                const openCourseTestEditModal = (testIdRaw) => {
                    if (!courseTestEditModal || !courseTestEditForm || !courseTestEditFields) {
                        return;
                    }

                    const testId = String(testIdRaw ?? '').trim();
                    if (testId === '') {
                        return;
                    }

                    const editableEntries = testEditRows.get(testId);
                    if (!Array.isArray(editableEntries) || editableEntries.length === 0) {
                        return;
                    }

                    courseTestEditForm.setAttribute('action', courseTestUpdateUrlTemplate.replace('__ID__', encodeURIComponent(testId)));
                    courseTestEditFields.innerHTML = editableEntries.map(([key, value]) => buildEditableFieldHtml(key, value)).join('');
                    if (courseTestEditTitle) {
                        courseTestEditTitle.textContent = `Edit course_test #${testId}`;
                    }

                    courseTestEditModal.classList.remove('hidden');
                    courseTestEditModal.classList.add('flex');
                };

                const normalizeChoiceValue = (value) => {
                    if (value === null || value === undefined) {
                        return '';
                    }

                    if (typeof value === 'string') {
                        const normalized = value.replace(/\s+/g, ' ').trim();
                        if (normalized === '' || normalized === '""' || normalized === "''") {
                            return '';
                        }
                        return normalized;
                    }

                    if (Array.isArray(value)) {
                        const normalizedItems = value
                            .map((item) => normalizeChoiceValue(item))
                            .filter((item) => item !== '');
                        return normalizedItems.join(' ');
                    }

                    if (typeof value === 'object') {
                        const normalizedItems = Object.values(value)
                            .map((item) => normalizeChoiceValue(item))
                            .filter((item) => item !== '');
                        return normalizedItems.join(' ');
                    }

                    return String(value).trim();
                };

                const splitChoiceTokens = (value) => {
                    const normalized = normalizeChoiceValue(value);
                    if (normalized === '') {
                        return [];
                    }

                    return normalized
                        .split(/\s+/)
                        .map((token) => token.trim())
                        .filter((token) => token !== '');
                };

                const encodeFilePath = (value) => String(value ?? '')
                    .split('/')
                    .filter((segment) => segment !== '')
                    .map((segment) => encodeURIComponent(segment))
                    .join('/');

                const resolveMedia = (variantRaw, filePathRaw) => {
                    const variant = String(variantRaw ?? '').trim().toLowerCase();
                    const encodedPath = encodeFilePath(filePathRaw);
                    if (!encodedPath) {
                        return null;
                    }

                    if (variant === 'v' || variant === 'video') {
                        return {
                            type: 'video',
                            url: `${mediaBaseUrl}${mediaVideoBasePath}/${encodedPath}`,
                        };
                    }

                    if (variant === 'a' || variant === 'audio') {
                        return {
                            type: 'audio',
                            url: `${mediaBaseUrl}${mediaAudioBasePath}/${encodedPath}`,
                        };
                    }

                    return null;
                };

                const resolveTestMedia = (item) => {
                    const variant = String(item?.variant ?? '').trim().toLowerCase();
                    const mediaPath = [
                        item?.file_path,
                        item?.video_path,
                        item?.video_file,
                        item?.video,
                        item?.image_path,
                        item?.image_file,
                        item?.image,
                        item?.audio_path,
                        item?.audio_file,
                        item?.audio,
                        item?.media_file,
                    ].find((value) => String(value ?? '').trim() !== '');
                    const encodedPath = encodeFilePath(mediaPath);
                    if (!encodedPath) {
                        return null;
                    }

                    if (variant === 'v' || variant === 'video') {
                        return {
                            type: 'video',
                            url: `${mediaBaseUrl}${testVideoBasePath}/${encodedPath}`,
                        };
                    }

                    if (variant === 'i' || variant === 'image') {
                        return {
                            type: 'image',
                            url: `${mediaBaseUrl}${testImageBasePath}/${encodedPath}`,
                        };
                    }

                    if (variant === 'a' || variant === 'audio' || variant === 'ca') {
                        const urls = testAudioBasePaths.map((basePath) => `${mediaBaseUrl}${basePath}/${encodedPath}`);
                        return {
                            type: 'audio',
                            url: urls[0],
                            urls,
                        };
                    }

                    return null;
                };

                const renderCarouselCards = (items) => {
                    if (!Array.isArray(items) || items.length === 0) {
                        return '<div class="text-xs text-slate-500">No carousel rows found.</div>';
                    }

                    return items.map((item, index) => {
                        const baseTitle = escapeHtml(item?.base_title || '-');
                        const engTitle = escapeHtml(item?.eng_title || '-');
                        const rusTitle = escapeHtml(item?.rus_title || '-');
                        const variantRaw = String(item?.variant ?? '').trim();
                        const variant = escapeHtml(variantRaw || '-');
                        const media = resolveMedia(variantRaw, item?.file_path);

                        let mediaHtml = `
                            <div class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs text-slate-500">
                                No media for this row.
                            </div>
                        `;

                        if (media) {
                            const mediaUrl = escapeHtml(media.url);
                            if (media.type === 'video') {
                                mediaHtml = `
                                    <video controls preload="metadata" class="w-full rounded-lg border border-slate-300 bg-black">
                                        <source src="${mediaUrl}" type="video/mp4">
                                    </video>
                                    <a class="text-xs font-semibold text-slate-700 underline" href="${mediaUrl}" target="_blank" rel="noopener noreferrer">Open video</a>
                                `;
                            } else if (media.type === 'audio') {
                                mediaHtml = `
                                    <audio controls preload="metadata" class="w-full rounded-lg border border-slate-300 bg-white">
                                        <source src="${mediaUrl}" type="audio/mpeg">
                                    </audio>
                                    <a class="text-xs font-semibold text-slate-700 underline" href="${mediaUrl}" target="_blank" rel="noopener noreferrer">Open audio</a>
                                `;
                            }
                        }

                        return `
                            <article class="h-full rounded-xl border border-[#e7b583] p-4 shadow-sm" style="background:#ffe2c4;">
                                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                    <div class="text-xs font-semibold text-slate-700">Row #${index + 1}</div>
                                    <span class="rounded-full border border-slate-300 bg-white px-2 py-0.5 text-[10px] font-semibold text-slate-700">Variant: ${variant}</span>
                                </div>

                                <div class="grid gap-2 text-sm text-slate-800">
                                    <div><span class="font-semibold">base_title:</span> ${baseTitle}</div>
                                    <div><span class="font-semibold">eng_title:</span> ${engTitle}</div>
                                    <div><span class="font-semibold">rus_title:</span> ${rusTitle}</div>
                                </div>

                                <div class="mt-3 space-y-2">
                                    ${mediaHtml}
                                </div>
                            </article>
                        `;
                    }).join('');
                };

                const renderTestCards = (items) => {
                    if (!Array.isArray(items) || items.length === 0) {
                        return '<div class="text-xs text-slate-500">No test rows found.</div>';
                    }

                    const hiddenKeys = new Set(['course_url']);
                    const headerKeys = new Set(['id', 'series', 'variant', 'correct_answer', 'correct', 'true_answer']);
                    const choiceKeys = new Set(['v1', 'v2', 'v3', 'v4']);
                    const priorityKeys = ['question', 'question_text', 'description', 'description_text', 'title', 'eng_title', 'rus_title', 'base_title', 'text', 'word', 'answer', 'correct_answer', 'variant'];

                    return items.map((item, index) => {
                        const safeItem = sanitizeForDisplay(item);
                        const rawTestId = safeItem?.id;
                        const testId = escapeHtml(rawTestId ?? '-');
                        const series = escapeHtml(safeItem?.series ?? '-');
                        const variantLabelRaw = String(safeItem?.variant ?? '').trim();
                        const variantLabel = escapeHtml(variantLabelRaw || '-');
                        const correctRawValue = safeItem?.correct_answer ?? safeItem?.correct ?? safeItem?.true_answer;
                        const correctLabel = escapeHtml(normalizeChoiceValue(correctRawValue) || '-');
                        const variantRaw = variantLabelRaw.toLowerCase();
                        const isChoiceVariant = variantRaw === 'ct' || variantRaw === 'ca';
                        const normalizedTestId = rawTestId !== null && rawTestId !== undefined ? String(rawTestId).trim() : '';
                        const hasEditableId = normalizedTestId !== '';
                        const media = resolveTestMedia(item);
                        const choiceValues = isChoiceVariant
                            ? ['v1', 'v2', 'v3', 'v4']
                                .flatMap((key) => {
                                    if (variantRaw === 'ct' || variantRaw === 'ca') {
                                        return splitChoiceTokens(safeItem?.[key]);
                                    }

                                    const normalized = normalizeChoiceValue(safeItem?.[key]);
                                    return normalized !== '' ? [normalized] : [];
                                })
                            : [];
                        const editableEntries = Object.entries(safeItem || {})
                            .filter(([key]) => key !== 'id')
                            .sort(([keyA], [keyB]) => {
                                const indexA = priorityKeys.indexOf(keyA);
                                const indexB = priorityKeys.indexOf(keyB);

                                if (indexA === -1 && indexB === -1) {
                                    return keyA.localeCompare(keyB);
                                }
                                if (indexA === -1) {
                                    return 1;
                                }
                                if (indexB === -1) {
                                    return -1;
                                }

                                return indexA - indexB;
                            });

                        if (hasEditableId) {
                            testEditRows.set(normalizedTestId, editableEntries);
                        }

                        const fields = Object.entries(safeItem || {})
                            .filter(([key, value]) => {
                                if (hiddenKeys.has(key) || headerKeys.has(key)) {
                                    return false;
                                }
                                if (isChoiceVariant && choiceKeys.has(key)) {
                                    return false;
                                }
                                if (value === null || value === undefined) {
                                    return false;
                                }
                                if (typeof value === 'string' && value.trim() === '') {
                                    return false;
                                }
                                if (Array.isArray(value) && value.length === 0) {
                                    return false;
                                }
                                return true;
                            })
                            .sort(([keyA], [keyB]) => {
                                const indexA = priorityKeys.indexOf(keyA);
                                const indexB = priorityKeys.indexOf(keyB);

                                if (indexA === -1 && indexB === -1) {
                                    return keyA.localeCompare(keyB);
                                }
                                if (indexA === -1) {
                                    return 1;
                                }
                                if (indexB === -1) {
                                    return -1;
                                }

                                return indexA - indexB;
                            });

                        let mediaHtml = '';
                        if (media?.type === 'video') {
                            const mediaUrl = escapeHtml(media.url);
                            mediaHtml = `
                                <div class="mb-3 space-y-2">
                                    <video controls preload="metadata" class="w-full rounded-lg border border-slate-300 bg-black">
                                        <source src="${mediaUrl}" type="video/mp4">
                                    </video>
                                    <a class="text-xs font-semibold text-slate-700 underline" href="${mediaUrl}" target="_blank" rel="noopener noreferrer">Open test video</a>
                                </div>
                            `;
                        } else if (media?.type === 'image') {
                            const mediaUrl = escapeHtml(media.url);
                            mediaHtml = `
                                <div class="mb-3 space-y-2">
                                    <img src="${mediaUrl}" alt="Test image" loading="lazy" class="w-full rounded-lg border border-slate-300 bg-white object-contain">
                                    <a class="text-xs font-semibold text-slate-700 underline" href="${mediaUrl}" target="_blank" rel="noopener noreferrer">Open test image</a>
                                </div>
                            `;
                        } else if (media?.type === 'audio') {
                            const mediaUrl = escapeHtml(media.url);
                            const audioSources = Array.isArray(media.urls) && media.urls.length > 0
                                ? media.urls.map((url) => `<source src="${escapeHtml(url)}" type="audio/mpeg">`).join('')
                                : `<source src="${mediaUrl}" type="audio/mpeg">`;
                            mediaHtml = `
                                <div class="mb-3 space-y-2">
                                    <audio controls preload="metadata" class="w-full rounded-lg border border-slate-300 bg-white">
                                        ${audioSources}
                                    </audio>
                                    <a class="text-xs font-semibold text-slate-700 underline" href="${mediaUrl}" target="_blank" rel="noopener noreferrer">Open test audio</a>
                                </div>
                            `;
                        }

                        let choiceButtonsHtml = '';
                        if (choiceValues.length > 0) {
                            const buttons = choiceValues.map((value) => `
                                <button type="button" class="inline-flex w-auto max-w-full shrink-0 items-center rounded-full border border-slate-300 bg-white px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                    ${escapeHtml(value)}
                                </button>
                            `).join('');

                            choiceButtonsHtml = `
                                <div class="mt-3 mb-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                                    <div class="flex flex-wrap gap-2">
                                        ${buttons}
                                    </div>
                                </div>
                            `;
                        }

                        const fieldsHtml = fields.length > 0
                            ? fields.map(([key, value]) => `
                                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">${escapeHtml(formatFieldLabel(key))}</div>
                                    <div class="mt-1 break-words text-sm text-slate-800">${escapeHtml(formatFieldValue(value))}</div>
                                </div>
                            `).join('')
                            : '<div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-500">No visible fields for this row.</div>';
                        const editButtonHtml = hasEditableId
                            ? `
                                <div class="mt-3 flex justify-end">
                                    <button type="button" data-course-test-edit-id="${escapeHtml(normalizedTestId)}" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                        Edit course_test #${testId}
                                    </button>
                                </div>
                            `
                            : '';

                        return `
                            <article class="h-full rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                    <div class="text-xs font-semibold text-slate-700">Test #${index + 1}</div>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="rounded-full border border-slate-300 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold text-slate-700">ID: ${testId}</span>
                                        <span class="rounded-full border border-amber-300 bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800">Variant: ${variantLabel}</span>
                                        <span class="rounded-full border border-emerald-300 bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-800">Correct: ${correctLabel}</span>
                                        <span class="rounded-full border border-slate-300 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold text-slate-700">Series: ${series}</span>
                                    </div>
                                </div>
                                ${mediaHtml}
                                <div class="grid gap-2">
                                    ${fieldsHtml}
                                </div>
                                ${choiceButtonsHtml}
                                ${editButtonHtml}
                            </article>
                        `;
                    }).join('');
                };

                const renderPayload = (payload) => {
                    testEditRows.clear();
                    closeCourseTestEditModal();

                    const lesson = payload?.lesson || {};
                    const carousel = Array.isArray(payload?.carousel) ? payload.carousel : [];
                    const tests = Array.isArray(payload?.tests) ? payload.tests : [];

                    content.innerHTML = `
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <div class="text-xs text-slate-500">Lesson id</div>
                                <div class="mt-1 text-sm font-semibold text-slate-800">${escapeHtml(lesson?.id ?? '-')}</div>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <div class="text-xs text-slate-500">Category</div>
                                <div class="mt-1 text-sm font-semibold text-slate-800">${escapeHtml(lesson?.category_url ?? '-')}</div>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <div class="text-xs text-slate-500">Carousel rows</div>
                                <div class="mt-1 text-sm font-semibold text-slate-800">${carousel.length}</div>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <div class="text-xs text-slate-500">Test rows (all series)</div>
                                <div class="mt-1 text-sm font-semibold text-slate-800">${tests.length}</div>
                            </div>
                        </div>

                        <section class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <h2 class="text-sm font-semibold text-slate-800">Carousel details</h2>
                            <div class="mt-3 grid gap-3 md:grid-cols-3">
                                ${renderCarouselCards(carousel)}
                            </div>
                        </section>

                        <section class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <h2 class="text-sm font-semibold text-slate-800">course_test rows (all series)</h2>
                            <div class="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                ${renderTestCards(tests)}
                            </div>
                        </section>

                        <div class="text-xs text-slate-500">
                            Loaded for slug: <span class="font-semibold text-slate-700">${escapeHtml(slug)}</span>
                        </div>
                    `;
                };

                content.addEventListener('click', (event) => {
                    const editButton = event.target.closest('[data-course-test-edit-id]');
                    if (!editButton) {
                        return;
                    }

                    openCourseTestEditModal(editButton.getAttribute('data-course-test-edit-id'));
                });

                courseTestEditCloseButtons.forEach((button) => {
                    button.addEventListener('click', closeCourseTestEditModal);
                });

                if (courseTestEditModal) {
                    courseTestEditModal.addEventListener('click', (event) => {
                        if (event.target === courseTestEditModal) {
                            closeCourseTestEditModal();
                        }
                    });
                }

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeCourseTestEditModal();
                    }
                });

                const loadContent = () => {
                    content.innerHTML = `
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-500">
                            Loading lesson content...
                        </div>
                    `;

                    fetch(endpointUrl, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                        },
                    })
                        .then((response) => {
                            if (!response.ok) {
                                return response.json().then((json) => {
                                    throw new Error(json?.message || 'Failed to load lesson content.');
                                }).catch(() => {
                                    throw new Error('Failed to load lesson content.');
                                });
                            }
                            return response.json();
                        })
                        .then((payload) => {
                            if (!payload || payload.success !== true) {
                                throw new Error(payload?.message || 'Invalid response format.');
                            }
                            renderPayload(payload);
                        })
                        .catch((error) => {
                            renderError(error?.message || 'Unknown error while loading lesson content.');
                        });
                };

                loadContent();
            })();
        </script>
    </body>
</html>
