<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="message_games" />
        <x-style-head-dropy />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="message_games" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-semibold">message_games</h1>
                                <p class="mt-2 text-sm text-slate-600">Rows from table <span class="font-semibold">message_games</span>.</p>
                            </div>
                            <a class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" href="{{ url('/games') }}">
                                Back to games
                                <i class="fa-solid fa-arrow-right text-slate-400"></i>
                            </a>
                        </div>
                    </div>

                    @if (!empty($error))
                        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ $error }}
                        </div>
                    @endif

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-semibold text-slate-700">message_games rows</div>
                        <div class="mt-1 text-xs text-slate-500">Columns: id, title, description, image, type</div>

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-slate-500">
                                        <th class="pb-2 pr-3">ID</th>
                                        <th class="pb-2 pr-3">Title</th>
                                        <th class="pb-2 pr-3">Description</th>
                                        <th class="pb-2 pr-3">Image</th>
                                        <th class="pb-2 pr-3">Type</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse (($rows ?? collect()) as $row)
                                        @php
                                            $rawImage = trim((string) ($row->image ?? ''));
                                            $imageUrl = '';
                                            $typeRaw = trim((string) ($row->type ?? ''));
                                            $typeKey = strtolower($typeRaw);
                                            $typeClass = 'border-slate-200 bg-slate-50 text-slate-700';
                                            if ($typeKey === 'of_support') {
                                                $typeClass = 'border-blue-200 bg-blue-50 text-blue-700';
                                            } elseif ($typeKey === 'of_praise') {
                                                $typeClass = 'border-emerald-200 bg-emerald-50 text-emerald-700';
                                            }
                                            if ($rawImage !== '') {
                                                $imageUrl = preg_match('/^https?:\/\//i', $rawImage)
                                                    ? $rawImage
                                                    : 'https://www.language.onllyons.com/' . ltrim($rawImage, '/');
                                            }
                                        @endphp
                                        <tr>
                                            <td class="py-2 pr-3 text-slate-600">{{ $row->id ?? '-' }}</td>
                                            <td class="py-2 pr-3 font-semibold text-slate-700">{{ $row->title ?? '-' }}</td>
                                            <td class="py-2 pr-3 text-slate-600">
                                                <div class="max-w-[420px] whitespace-pre-wrap break-words">{{ $row->description ?? '-' }}</div>
                                            </td>
                                            <td class="py-2 pr-3 text-slate-600">
                                                @if ($imageUrl !== '')
                                                    <div class="flex items-start gap-3">
                                                        <button
                                                            type="button"
                                                            class="js-open-image-modal h-14 w-14 overflow-hidden rounded-lg border border-slate-200 bg-slate-100"
                                                            data-image-url="{{ $imageUrl }}"
                                                            data-image-alt="message image {{ $row->id ?? '' }}"
                                                            title="Click to preview"
                                                        >
                                                            <img
                                                                src="{{ $imageUrl }}"
                                                                alt="message image {{ $row->id ?? '' }}"
                                                                class="h-full w-full object-cover"
                                                                loading="lazy"
                                                            />
                                                        </button>
                                                        <a
                                                            href="{{ $imageUrl }}"
                                                            target="_blank"
                                                            rel="noreferrer"
                                                            class="inline-block max-w-[280px] truncate text-sky-600 hover:underline"
                                                            title="{{ $imageUrl }}"
                                                        >
                                                            {{ $imageUrl }}
                                                        </a>
                                                    </div>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="py-2 pr-3 text-slate-600">
                                                @if ($typeRaw !== '')
                                                    <span class="inline-flex items-center rounded-full border px-2 py-1 text-xs font-semibold {{ $typeClass }}">
                                                        {{ $typeRaw }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-3 text-slate-500">No rows found in message_games.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <div id="imageLightbox" class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-900/80 p-4">
            <div class="relative w-full max-w-5xl">
                <button
                    id="imageLightboxClose"
                    type="button"
                    class="absolute right-2 top-2 z-10 inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/30 bg-black/40 text-white hover:bg-black/60"
                    aria-label="Close image preview"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <img
                    id="imageLightboxPreview"
                    src=""
                    alt="Image preview"
                    class="max-h-[85vh] w-full rounded-xl border border-white/20 bg-black/20 object-contain"
                />
                <a
                    id="imageLightboxOpenNew"
                    href="#"
                    target="_blank"
                    rel="noreferrer"
                    class="mt-3 inline-flex items-center gap-2 rounded-lg border border-white/30 bg-black/30 px-3 py-2 text-xs font-semibold text-white hover:bg-black/50"
                >
                    Open in new tab
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
            </div>
        </div>

        <x-script-components />
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var modal = document.getElementById('imageLightbox');
                var closeButton = document.getElementById('imageLightboxClose');
                var previewImage = document.getElementById('imageLightboxPreview');
                var openNewLink = document.getElementById('imageLightboxOpenNew');

                if (!modal || !previewImage || !openNewLink) {
                    return;
                }

                function closeLightbox() {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    previewImage.setAttribute('src', '');
                    openNewLink.setAttribute('href', '#');
                    document.body.classList.remove('overflow-hidden');
                }

                function openLightbox(url, alt) {
                    if (!url) {
                        return;
                    }
                    previewImage.setAttribute('src', url);
                    previewImage.setAttribute('alt', alt || 'Image preview');
                    openNewLink.setAttribute('href', url);
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                }

                document.querySelectorAll('.js-open-image-modal').forEach(function (button) {
                    button.addEventListener('click', function () {
                        var url = button.getAttribute('data-image-url') || '';
                        var alt = button.getAttribute('data-image-alt') || 'Image preview';
                        openLightbox(url, alt);
                    });
                });

                if (closeButton) {
                    closeButton.addEventListener('click', closeLightbox);
                }

                modal.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        closeLightbox();
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        closeLightbox();
                    }
                });
            });
        </script>
        <x-offcanvas-right />
    </body>
</html>
