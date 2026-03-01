<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x-seo-component title="Messenger" />
        <x-style-head-dropy />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
        <div class="min-h-screen flex">
            <x-left-nav />

            <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 md:hidden"></div>

            <div class="flex-1 md:ml-64">
                <x-top-nav title="Messenger" />

                <main class="p-4 md:p-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h1 class="text-2xl font-semibold">Messenger</h1>
                        <p class="mt-2 text-sm text-slate-600">Tickets center: open, inspect, reply, and close support requests.</p>
                    </div>

                    <div class="mt-6 grid gap-6 xl:grid-cols-[390px,1fr]">
                        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-100 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-sm font-semibold text-slate-700">Tickets</div>
                                    <div class="flex items-center gap-2">
                                        <select
                                            id="messengerLimitSelect"
                                            class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs font-semibold text-slate-700"
                                        >
                                            <option value="20" selected>20</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                        <button
                                            type="button"
                                            id="messengerReloadTickets"
                                            class="rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                        >
                                            Reload
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-3 flex gap-2">
                                    <button
                                        type="button"
                                        id="messengerTabOpen"
                                        data-tab="open"
                                        class="rounded-lg border border-slate-900 bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white"
                                    >
                                        Open
                                    </button>
                                    <button
                                        type="button"
                                        id="messengerTabClosed"
                                        data-tab="closed"
                                        class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                    >
                                        Closed
                                    </button>
                                </div>
                                <div id="messengerOpenRangeFilters" class="mt-3 flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        data-range="today"
                                        class="messenger-range-btn rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                    >
                                        Today
                                    </button>
                                    <button
                                        type="button"
                                        data-range="yesterday"
                                        class="messenger-range-btn rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                    >
                                        Yesterday
                                    </button>
                                    <button
                                        type="button"
                                        data-range="last5"
                                        class="messenger-range-btn rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                    >
                                        Last 5 days
                                    </button>
                                    <button
                                        type="button"
                                        data-range="last30"
                                        class="messenger-range-btn rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                    >
                                        Last 30 days
                                    </button>
                                </div>
                            </div>

                            <div class="border-b border-slate-100 px-4 py-2 text-xs text-slate-500">
                                <span>Open loaded: <span id="messengerOpenCount">0</span></span>
                                <span class="ml-4">Closed loaded: <span id="messengerClosedCount">0</span></span>
                            </div>

                            <div id="messengerTicketsOpen" class="max-h-[70vh] overflow-y-auto"></div>
                            <div id="messengerTicketsClosed" class="hidden max-h-[70vh] overflow-y-auto"></div>
                        </section>

                        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:p-5">
                            <div id="messengerEmptyState" class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">
                                Select a ticket from the left list.
                            </div>

                            <div id="messengerDetail" class="hidden">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <h2 id="messengerTicketTitle" class="text-lg font-semibold text-slate-800">-</h2>
                                        <div id="messengerTicketMeta" class="mt-2 text-xs text-slate-500"></div>
                                    </div>
                                    <span id="messengerTicketStatus" class="rounded-full border px-2.5 py-1 text-xs font-semibold">-</span>
                                </div>

                                <div id="messengerCourseInfo" class="mt-2 hidden text-xs text-slate-500"></div>
                                <div id="messengerMessages" class="mt-4 max-h-[55vh] space-y-3 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-3"></div>

                                <div class="mt-4">
                                    <label for="messengerInput" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Reply</label>
                                    <textarea
                                        id="messengerInput"
                                        class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none ring-0 focus:border-slate-400"
                                        rows="4"
                                        placeholder="Type your message"
                                    ></textarea>
                                </div>

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <button
                                        type="button"
                                        id="messengerSendButton"
                                        class="rounded-lg border border-slate-900 bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800"
                                    >
                                        Send
                                    </button>
                                    <button
                                        type="button"
                                        id="messengerCloseButton"
                                        class="rounded-lg border border-rose-300 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100"
                                    >
                                        Close ticket
                                    </button>
                                    <button
                                        type="button"
                                        id="messengerRefreshCurrentButton"
                                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                    >
                                        Refresh current
                                    </button>
                                </div>
                            </div>
                        </section>
                    </div>
                </main>
            </div>
        </div>

        <x-script-components />
        <x-offcanvas-right />

        <script>
            (function () {
                var endpoints = {
                    tickets: @json(route('messenger.tickets')),
                    ticket: @json(route('messenger.ticket')),
                    sendMessage: @json(route('messenger.send-message')),
                    closeComplaint: @json(route('messenger.close-complaint'))
                };
                var csrfToken = @json(csrf_token());
                var userImageBase = 'https://www.language.onllyons.com/ru/ru-en/dist/images/user-images/';
                var pollMs = 5000;
                var userDetailBase = @json(url('/users'));
                var lessonBaseUrl = @json(url('/lesson-crm'));

                var currentKey = '';
                var currentTab = 'open';
                var currentLimit = 20;
                var currentOpenRange = 'last30';
                var pollTimeout = null;
                var draftByTicket = {};

                var ticketsByTab = {
                    open: [],
                    closed: []
                };
                var loadedByTab = {
                    open: false,
                    closed: false
                };
                var tabCounts = {
                    open: 0,
                    closed: 0
                };

                function showToastError(message) {
                    var text = message && String(message).trim() !== '' ? String(message) : 'Request failed.';
                    if (window.toastr && typeof window.toastr.error === 'function') {
                        window.toastr.error(text);
                    } else {
                        alert(text);
                    }
                }

                function showToastSuccess(message) {
                    var text = message && String(message).trim() !== '' ? String(message) : 'Done.';
                    if (window.toastr && typeof window.toastr.success === 'function') {
                        window.toastr.success(text);
                    }
                }

                function escapeHtml(text) {
                    return String(text || '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }

                function nl2brSafe(text) {
                    return escapeHtml(text).replace(/\r?\n/g, '<br>');
                }

                function normalizeTimeToTs(value) {
                    if (value === null || typeof value === 'undefined' || value === '') {
                        return 0;
                    }
                    if (!isNaN(Number(value))) {
                        return Number(value);
                    }
                    var parsed = Date.parse(String(value));
                    return isNaN(parsed) ? 0 : Math.floor(parsed / 1000);
                }

                function formatDateTime(value) {
                    var ts = normalizeTimeToTs(value);
                    if (!ts) {
                        return '-';
                    }
                    return new Date(ts * 1000).toLocaleString();
                }

                function makeTicketKey(tableName, id) {
                    return String(tableName || '') + '#' + String(id || '');
                }

                function isOpenTicket(ticket) {
                    return !!(ticket && ticket.complaint && Number(ticket.complaint.status || 0) === 0);
                }

                function resolveTicketTab(ticket) {
                    return isOpenTicket(ticket) ? 'open' : 'closed';
                }

                function getListForTab(tab) {
                    return ticketsByTab[tab] || [];
                }

                function updateCounters() {
                    $('#messengerOpenCount').text(tabCounts.open);
                    $('#messengerClosedCount').text(tabCounts.closed);
                }

                function updateOpenRangeButtons() {
                    $('#messengerOpenRangeFilters [data-range]').each(function () {
                        var button = $(this);
                        var buttonRange = String(button.attr('data-range') || '');
                        var isActive = buttonRange === currentOpenRange;

                        if (isActive) {
                            button
                                .removeClass('border-slate-300 bg-white text-slate-700')
                                .addClass('border-slate-900 bg-slate-900 text-white');
                        } else {
                            button
                                .removeClass('border-slate-900 bg-slate-900 text-white')
                                .addClass('border-slate-300 bg-white text-slate-700');
                        }
                    });
                }

                function sortTickets(list) {
                    list.sort(function (a, b) {
                        var aTs = Number((a.complaint || {}).time_sort || 0);
                        var bTs = Number((b.complaint || {}).time_sort || 0);
                        return bTs - aTs;
                    });
                    return list;
                }

                function setTicketsForTab(tab, tickets) {
                    var normalized = Array.isArray(tickets) ? tickets.slice() : [];
                    ticketsByTab[tab] = sortTickets(normalized);
                    tabCounts[tab] = ticketsByTab[tab].length;
                    loadedByTab[tab] = true;
                    updateCounters();
                }

                function removeTicketFromTab(tab, key) {
                    var list = getListForTab(tab);
                    var filtered = [];
                    for (var i = 0; i < list.length; i += 1) {
                        var row = list[i];
                        var rowKey = makeTicketKey((row.complaint || {}).table, (row.complaint || {}).id);
                        if (rowKey !== key) {
                            filtered.push(row);
                        }
                    }
                    ticketsByTab[tab] = filtered;
                    tabCounts[tab] = filtered.length;
                }

                function upsertTicket(ticket) {
                    if (!ticket || !ticket.complaint) {
                        return;
                    }
                    var key = makeTicketKey(ticket.complaint.table, ticket.complaint.id);
                    removeTicketFromTab('open', key);
                    removeTicketFromTab('closed', key);

                    var tab = resolveTicketTab(ticket);
                    var list = getListForTab(tab).slice();
                    list.unshift(ticket);
                    ticketsByTab[tab] = sortTickets(list);
                    tabCounts[tab] = ticketsByTab[tab].length;
                    updateCounters();
                }

                function saveCurrentDraft() {
                    if (!currentKey) {
                        return;
                    }
                    draftByTicket[currentKey] = String($('#messengerInput').val() || '');
                }

                function getTicketByKey(key) {
                    var tabs = ['open', 'closed'];
                    for (var t = 0; t < tabs.length; t += 1) {
                        var list = getListForTab(tabs[t]);
                        for (var i = 0; i < list.length; i += 1) {
                            var complaint = list[i].complaint || {};
                            if (makeTicketKey(complaint.table, complaint.id) === key) {
                                return list[i];
                            }
                        }
                    }
                    return null;
                }

                function complaintTitle(complaint) {
                    var tableName = String((complaint || {}).table || '');
                    var theme = String((complaint || {}).theme || '');
                    var url = String((complaint || {}).url || '');

                    if (tableName === 'complaint_user') {
                        if (theme === 'error') {
                            return 'Произошла ошибка';
                        }
                        if (theme === 'suggestion') {
                            return 'Помогите нам сделать Onllyons лучше';
                        }
                    }

                    if (tableName === 'complaint_game') {
                        var gameMap = {
                            '/play/chose/': 'Задачи',
                            '/play/choose-theme/': 'Задачи - Выбрать тему',
                            '/play/spelling-fun/': 'Написать перевод',
                            '/play/riddles-translate/': 'Расшифруйте аудио',
                            '/play/riddles-pick/': 'Переведите аудио',
                            '/play/riddles-true-false/': 'Верно - Не верно'
                        };
                        if (gameMap[url]) {
                            return 'Игра: ' + gameMap[url];
                        }
                    }

                    if (tableName === 'complaint_flashcards') {
                        if (url.indexOf('/books/') === 0) return 'Карточки: Короткие истории';
                        if (url.indexOf('/poetry/') === 0) return 'Карточки: Стихи';
                        if (url.indexOf('/dialogues/') === 0) return 'Карточки: Диалоги';
                        if (url.indexOf('/flashcard-alphabet/') === 0) return 'Карточки: Алфавит';
                        if (url.indexOf('/flashcard-phonetic-symbols/') === 0) return 'Карточки: Звуки английского языка';
                        if (url.indexOf('/flashcard-words-learning/') === 0) return 'Карточки: Слова';
                        if (url.indexOf('/flashcard-questions-learning/') === 0) return 'Карточки: Важные вопросы';
                        if (url.indexOf('/flashcard-sentences-learning/') === 0) return 'Карточки: Идиомы';
                        if (url.indexOf('/flashcard-know-learning/') === 0) return 'Карточки: Важнейшие теоретические идеи';
                        if (url.indexOf('/course-read-learning/') === 0) return 'Карточки: Основы чтения';
                    }

                    var base = String((complaint || {}).displayName || 'Ticket');
                    return theme ? (base + ': ' + theme) : base;
                }

                function buildTicketRow(ticket) {
                    var complaint = ticket.complaint || {};
                    var messages = ticket.messages || [];
                    var lastMessage = messages.length ? messages[messages.length - 1] : null;
                    var key = makeTicketKey(complaint.table, complaint.id);
                    var isSelected = key === currentKey;
                    var selectedClass = isSelected ? 'border-slate-900 bg-slate-100' : 'border-slate-200 bg-white hover:bg-slate-50';
                    var lastMessageText = lastMessage ? String(lastMessage.message || '') : '';
                    var sender = lastMessage && lastMessage.user ? String(lastMessage.user.name || '-') : '-';

                    var html = '';
                    html += '<button type="button" data-ticket-key="' + escapeHtml(key) + '" class="block w-full border-b border-slate-100 px-4 py-3 text-left">';
                    html += '<div class="rounded-xl border px-3 py-2 transition ' + selectedClass + '">';
                    html += '<div class="flex items-start justify-between gap-2">';
                    html += '<div class="min-w-0">';
                    html += '<div class="truncate text-sm font-semibold text-slate-800">' + escapeHtml(complaintTitle(complaint)) + '</div>';
                    html += '<div class="mt-1 text-xs text-slate-500">#' + escapeHtml(complaint.id) + ' | User ' + escapeHtml(complaint.userId) + '</div>';
                    html += '</div>';
                    html += '<div class="shrink-0 text-[11px] text-slate-500">' + escapeHtml(formatDateTime(complaint.time)) + '</div>';
                    html += '</div>';
                    html += '<div class="mt-2 text-xs text-slate-500">From: ' + escapeHtml(sender) + '</div>';
                    html += '<div class="mt-1 line-clamp-2 text-xs text-slate-600">' + nl2brSafe(lastMessageText) + '</div>';
                    html += '</div>';
                    html += '</button>';
                    return html;
                }

                function renderTickets() {
                    var openHtml = '';
                    var closedHtml = '';
                    var openList = getListForTab('open');
                    var closedList = getListForTab('closed');

                    if (!openList.length) {
                        openHtml = '<div class="px-4 py-6 text-sm text-slate-500">No open tickets loaded.</div>';
                    } else {
                        for (var i = 0; i < openList.length; i += 1) {
                            openHtml += buildTicketRow(openList[i]);
                        }
                    }

                    if (!closedList.length) {
                        closedHtml = '<div class="px-4 py-6 text-sm text-slate-500">No closed tickets loaded.</div>';
                    } else {
                        for (var j = 0; j < closedList.length; j += 1) {
                            closedHtml += buildTicketRow(closedList[j]);
                        }
                    }

                    $('#messengerTicketsOpen').html(openHtml);
                    $('#messengerTicketsClosed').html(closedHtml);
                    updateCounters();
                }

                function resolveImageUrl(image) {
                    var value = String(image || '').trim();
                    if (value === '') {
                        value = 'default.png';
                    }
                    if (value.indexOf('http://') === 0 || value.indexOf('https://') === 0) {
                        return value;
                    }
                    return userImageBase + value.replace(/^\/+/, '');
                }

                function renderMessages(messages) {
                    if (!messages || !messages.length) {
                        $('#messengerMessages').html('<div class="py-4 text-center text-sm text-slate-500">No messages.</div>');
                        return;
                    }

                    var html = '';
                    for (var i = 0; i < messages.length; i += 1) {
                        var message = messages[i] || {};
                        var user = message.user || {};
                        var isUser = String(message.type || '') === 'user';
                        var wrapperClass = isUser ? 'justify-start' : 'justify-end';
                        var bubbleClass = isUser
                            ? 'border-slate-200 bg-white text-slate-700'
                            : 'border-emerald-200 bg-emerald-50 text-emerald-900';

                        html += '<div class="flex ' + wrapperClass + '">';
                        html += '<div class="max-w-[90%] rounded-xl border px-3 py-2 shadow-sm ' + bubbleClass + '">';
                        html += '<div class="mb-1 flex items-center gap-2 text-[11px]">';
                        html += '<img src="' + escapeHtml(resolveImageUrl(user.image)) + '" alt="" class="h-6 w-6 rounded-full object-cover bg-slate-200" />';
                        html += '<span class="font-semibold">' + escapeHtml(user.name || '-') + '</span>';
                        html += '<span class="text-slate-500">' + escapeHtml(formatDateTime(message.time)) + '</span>';
                        html += '</div>';
                        html += '<div class="text-sm leading-6">' + nl2brSafe(message.message || '') + '</div>';
                        html += '</div>';
                        html += '</div>';
                    }

                    $('#messengerMessages').html(html);
                    var box = document.getElementById('messengerMessages');
                    if (box) {
                        box.scrollTop = box.scrollHeight;
                    }
                }

                function renderCourseInfo(complaint) {
                    var container = $('#messengerCourseInfo');
                    if (!complaint || (complaint.table || '') !== 'complaint_user_course') {
                        container.addClass('hidden').html('');
                        return;
                    }

                    var lines = [];
                    if (complaint.course_id_slide || complaint.course_id_slide === 0) {
                        lines.push('Slide ID: ' + escapeHtml(String(complaint.course_id_slide)));
                    }
                        if (complaint.course_url) {
                            var slug = String(complaint.course_url || '').trim();
                            if (slug !== '' && slug.indexOf('/') === -1) {
                                var lessonLink = lessonBaseUrl + '/' + encodeURIComponent(slug);
                                lines.push('Course URL: <a href="' + lessonLink + '" target="_blank" rel="noreferrer" class="text-sky-600 underline">' + escapeHtml(slug) + '</a>');
                            } else {
                                var safeUrl = escapeHtml(slug);
                                lines.push('Course URL slug: ' + safeUrl);
                            }
                        }
                    if (complaint.course_series || complaint.course_series === 0) {
                        lines.push('Series: ' + escapeHtml(String(complaint.course_series)));
                    }

                    if (!lines.length) {
                        lines.push('Course metadata is empty.');
                    }

                    container.removeClass('hidden').html(lines.map(function (line) {
                        return '<div>' + line + '</div>';
                    }).join(''));
                }

                function stopPolling() {
                    if (pollTimeout) {
                        clearTimeout(pollTimeout);
                        pollTimeout = null;
                    }
                }

                function showTicket(ticket) {
                    if (!ticket || !ticket.complaint) {
                        saveCurrentDraft();
                        $('#messengerDetail').addClass('hidden');
                        $('#messengerEmptyState').removeClass('hidden');
                        currentKey = '';
                        stopPolling();
                        return;
                    }

                    var nextKey = makeTicketKey(ticket.complaint.table, ticket.complaint.id);
                    if (currentKey && currentKey !== nextKey) {
                        saveCurrentDraft();
                    }
                    currentKey = nextKey;
                    renderTickets();

                    $('#messengerEmptyState').addClass('hidden');
                    $('#messengerDetail').removeClass('hidden');

                    var complaint = ticket.complaint;
                    var isOpen = Number(complaint.status || 0) === 0;
                    var draftValue = draftByTicket[currentKey] || '';

                    $('#messengerTicketTitle').text(complaintTitle(complaint));
                    var userId = Number(complaint.userId || 0);
                    var userMeta = '<span class="font-semibold text-slate-700">' + escapeHtml(complaint.userId) + '</span>';
                    if (userId > 0) {
                        userMeta += ' <a href="' + userDetailBase + '/' + encodeURIComponent(userId) + '" target="_blank" rel="noreferrer" class="text-sky-600 hover:underline text-xs font-semibold">(profil)</a>';
                    }
                    $('#messengerTicketMeta').html(
                        'ID: <span class="font-semibold text-slate-700">#' + escapeHtml(complaint.id) + '</span>' +
                        ' | Table: <span class="font-semibold text-slate-700">' + escapeHtml(complaint.table) + '</span>' +
                        ' | User: ' + userMeta +
                        ' | Time: <span class="font-semibold text-slate-700">' + escapeHtml(formatDateTime(complaint.time)) + '</span>' +
                        (complaint.url ? ' | URL: <span class="font-semibold text-slate-700">' + escapeHtml(complaint.url) + '</span>' : '')
                    );
                    renderCourseInfo(complaint);

                    if (isOpen) {
                        $('#messengerTicketStatus')
                            .removeClass()
                            .addClass('rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700')
                            .text('Open');
                    } else {
                        $('#messengerTicketStatus')
                            .removeClass()
                            .addClass('rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700')
                            .text('Closed');
                    }

                    $('#messengerInput').prop('disabled', !isOpen).val(isOpen ? draftValue : '');
                    $('#messengerSendButton').prop('disabled', !isOpen);
                    $('#messengerCloseButton').prop('disabled', !isOpen);
                    renderMessages(ticket.messages || []);

                    if (isOpen && currentTab === 'open') {
                        startPolling();
                    } else {
                        stopPolling();
                    }
                }

                function pickDefaultTicketForCurrentTab() {
                    var list = getListForTab(currentTab);
                    return list.length ? list[0] : null;
                }

                function postJson(url, payload, onSuccess, onError) {
                    var data = payload || {};
                    data._token = csrfToken;

                    $.ajax({
                        type: 'POST',
                        url: url,
                        dataType: 'json',
                        data: data,
                        success: function (response) {
                            if (typeof onSuccess === 'function') {
                                onSuccess(response || {});
                            }
                        },
                        error: function (xhr) {
                            if (typeof onError === 'function') {
                                onError(xhr);
                            } else {
                                showToastError('Request failed.');
                            }
                        }
                    });
                }

                function loadTicketsForTab(tab, keepCurrentSelection) {
                    var resolvedTab = (tab === 'closed') ? 'closed' : 'open';
                    var requestRange = resolvedTab === 'open' ? currentOpenRange : 'all';

                    postJson(endpoints.tickets, {
                        status: resolvedTab,
                        limit: currentLimit,
                        range: requestRange
                    }, function (response) {
                        if (!response.success) {
                            showToastError(response.error_message || 'Failed to load tickets.');
                            return;
                        }

                        setTicketsForTab(resolvedTab, Array.isArray(response.data) ? response.data : []);
                        renderTickets();

                        if (currentTab !== resolvedTab) {
                            return;
                        }

                        var selected = null;
                        if (keepCurrentSelection && currentKey) {
                            selected = getTicketByKey(currentKey);
                            if (selected && resolveTicketTab(selected) !== currentTab) {
                                selected = null;
                            }
                        }
                        if (!selected) {
                            selected = pickDefaultTicketForCurrentTab();
                        }
                        showTicket(selected);
                    }, function (xhr) {
                        var errorMessage = 'Failed to load tickets.';
                        if (xhr && xhr.responseJSON && xhr.responseJSON.error_message) {
                            errorMessage = xhr.responseJSON.error_message;
                        }
                        showToastError(errorMessage);
                    });
                }

                function refreshCurrentTicket(done) {
                    var ticket = getTicketByKey(currentKey);
                    if (!ticket || !ticket.complaint) {
                        if (typeof done === 'function') {
                            done();
                        }
                        return;
                    }

                    postJson(endpoints.ticket, {
                        table: ticket.complaint.table,
                        complaintId: ticket.complaint.id
                    }, function (response) {
                        if (response.success && response.data) {
                            upsertTicket(response.data);
                            renderTickets();

                            if (resolveTicketTab(response.data) === currentTab) {
                                showTicket(response.data);
                            } else {
                                showTicket(pickDefaultTicketForCurrentTab());
                            }
                        }
                        if (typeof done === 'function') {
                            done();
                        }
                    }, function () {
                        if (typeof done === 'function') {
                            done();
                        }
                    });
                }

                function startPolling() {
                    stopPolling();
                    var ticket = getTicketByKey(currentKey);
                    if (!ticket || !isOpenTicket(ticket) || currentTab !== 'open') {
                        return;
                    }

                    pollTimeout = setTimeout(function () {
                        refreshCurrentTicket(function () {
                            startPolling();
                        });
                    }, pollMs);
                }

                function setActiveTab(tab) {
                    var nextTab = tab === 'closed' ? 'closed' : 'open';

                    if (currentTab !== nextTab) {
                        saveCurrentDraft();
                    }

                    currentTab = nextTab;

                    if (currentTab === 'open') {
                        $('#messengerTicketsOpen').removeClass('hidden');
                        $('#messengerTicketsClosed').addClass('hidden');
                        $('#messengerOpenRangeFilters').removeClass('hidden');
                        $('#messengerTabOpen')
                            .removeClass('border-slate-300 bg-white text-slate-700')
                            .addClass('border-slate-900 bg-slate-900 text-white');
                        $('#messengerTabClosed')
                            .removeClass('border-slate-900 bg-slate-900 text-white')
                            .addClass('border-slate-300 bg-white text-slate-700');
                    } else {
                        $('#messengerTicketsOpen').addClass('hidden');
                        $('#messengerTicketsClosed').removeClass('hidden');
                        $('#messengerOpenRangeFilters').addClass('hidden');
                        $('#messengerTabClosed')
                            .removeClass('border-slate-300 bg-white text-slate-700')
                            .addClass('border-slate-900 bg-slate-900 text-white');
                        $('#messengerTabOpen')
                            .removeClass('border-slate-900 bg-slate-900 text-white')
                            .addClass('border-slate-300 bg-white text-slate-700');
                    }

                    updateOpenRangeButtons();
                    renderTickets();

                    var selected = currentKey ? getTicketByKey(currentKey) : null;
                    if (selected && resolveTicketTab(selected) === currentTab) {
                        showTicket(selected);
                    } else {
                        showTicket(null);
                    }

                    if (!loadedByTab[currentTab]) {
                        loadTicketsForTab(currentTab, false);
                    }
                }

                $('#messengerTicketsOpen, #messengerTicketsClosed').on('click', '[data-ticket-key]', function () {
                    var key = String($(this).attr('data-ticket-key') || '');
                    if (!key) {
                        return;
                    }

                    var ticket = getTicketByKey(key);
                    if (!ticket || resolveTicketTab(ticket) !== currentTab) {
                        return;
                    }

                    showTicket(ticket);
                });

                $('#messengerTabOpen').on('click', function () {
                    setActiveTab('open');
                });

                $('#messengerTabClosed').on('click', function () {
                    setActiveTab('closed');
                });

                $('#messengerReloadTickets').on('click', function () {
                    loadTicketsForTab(currentTab, true);
                });

                $('#messengerOpenRangeFilters').on('click', '[data-range]', function () {
                    var nextRange = String($(this).attr('data-range') || '');
                    if (['today', 'yesterday', 'last5', 'last30'].indexOf(nextRange) === -1) {
                        return;
                    }
                    if (currentOpenRange === nextRange) {
                        return;
                    }

                    currentOpenRange = nextRange;
                    updateOpenRangeButtons();

                    if (currentTab === 'open') {
                        loadTicketsForTab('open', true);
                    }
                });

                $('#messengerLimitSelect').on('change', function () {
                    var nextLimit = parseInt(String($(this).val() || '20'), 10);
                    if ([20, 50, 100].indexOf(nextLimit) === -1) {
                        nextLimit = 20;
                    }
                    currentLimit = nextLimit;

                    ticketsByTab = { open: [], closed: [] };
                    loadedByTab = { open: false, closed: false };
                    tabCounts = { open: 0, closed: 0 };
                    updateCounters();
                    renderTickets();
                    showTicket(null);
                    loadTicketsForTab(currentTab, false);
                });

                $('#messengerInput').on('input', function () {
                    if (!currentKey) {
                        return;
                    }
                    draftByTicket[currentKey] = String($(this).val() || '');
                });

                $('#messengerRefreshCurrentButton').on('click', function () {
                    refreshCurrentTicket();
                });

                $('#messengerSendButton').on('click', function () {
                    var ticket = getTicketByKey(currentKey);
                    if (!ticket || !ticket.complaint || !isOpenTicket(ticket)) {
                        return;
                    }

                    var message = String($('#messengerInput').val() || '').trim();
                    if (!message) {
                        showToastError('Введите сообщение.');
                        return;
                    }

                    postJson(endpoints.sendMessage, {
                        table: ticket.complaint.table,
                        complaintId: ticket.complaint.id,
                        message: message
                    }, function (response) {
                        if (!response.success) {
                            showToastError(response.error_message || 'Failed to send message.');
                            return;
                        }

                        if (response.data && response.data.complaint) {
                            var sentKey = makeTicketKey(response.data.complaint.table, response.data.complaint.id);
                            draftByTicket[sentKey] = '';
                            upsertTicket(response.data);
                            renderTickets();

                            if (resolveTicketTab(response.data) === currentTab) {
                                showTicket(response.data);
                            } else {
                                showTicket(pickDefaultTicketForCurrentTab());
                            }
                        } else {
                            draftByTicket[currentKey] = '';
                            refreshCurrentTicket();
                        }

                        showToastSuccess('Message sent.');
                    }, function (xhr) {
                        var errorMessage = 'Failed to send message.';
                        if (xhr && xhr.responseJSON && xhr.responseJSON.error_message) {
                            errorMessage = xhr.responseJSON.error_message;
                        }
                        showToastError(errorMessage);
                    });
                });

                $('#messengerCloseButton').on('click', function () {
                    var ticket = getTicketByKey(currentKey);
                    if (!ticket || !ticket.complaint || !isOpenTicket(ticket)) {
                        return;
                    }

                    if (!window.confirm('Close this ticket?')) {
                        return;
                    }

                    postJson(endpoints.closeComplaint, {
                        table: ticket.complaint.table,
                        complaintId: ticket.complaint.id
                    }, function (response) {
                        if (!response.success) {
                            showToastError(response.error_message || 'Failed to close ticket.');
                            return;
                        }

                        ticket.complaint.status = 1;
                        draftByTicket[currentKey] = '';
                        upsertTicket(ticket);
                        renderTickets();

                        if (currentTab === 'open') {
                            showTicket(pickDefaultTicketForCurrentTab());
                        } else {
                            showTicket(ticket);
                        }

                        showToastSuccess('Ticket closed.');
                    }, function (xhr) {
                        var errorMessage = 'Failed to close ticket.';
                        if (xhr && xhr.responseJSON && xhr.responseJSON.error_message) {
                            errorMessage = xhr.responseJSON.error_message;
                        }
                        showToastError(errorMessage);
                    });
                });

                document.addEventListener('visibilitychange', function () {
                    if (document.visibilityState === 'visible') {
                        refreshCurrentTicket(function () {
                            startPolling();
                        });
                    } else {
                        stopPolling();
                    }
                });

                $('#messengerLimitSelect').val(String(currentLimit));
                updateOpenRangeButtons();
                setActiveTab('open');
            })();
        </script>
    </body>
</html>
