<script>
    var notifFetchRoute = "{{ route('notifications.fetch') }}";
    var notifReadAllRoute = "{{ route('notifications.readAll') }}";
    var notifReadBase = "{{ url('/notifications/read') }}";
    var notifReadTicketRoute = "{{ route('notifications.readTicket') }}";
    var notifAuthUserId = {{ auth()->id() }};

    var knownNotifIds = { tickets: new Set(), chats: new Set() };
    var notifFirstLoad = true;
    var notifAudioCtx = null;

    // Unlock audio on first user interaction (browsers block sound before that)
    function unlockNotifAudio() {
        try {
            if (!notifAudioCtx) {
                var AC = window.AudioContext || window.webkitAudioContext;
                if (AC) notifAudioCtx = new AC();
            }
            if (notifAudioCtx && notifAudioCtx.state === 'suspended') {
                notifAudioCtx.resume();
            }
        } catch (e) { /* audio unavailable */ }
    }
    document.addEventListener('click', unlockNotifAudio, { once: true });
    document.addEventListener('keydown', unlockNotifAudio, { once: true });

    // Short two-tone chime, no audio file needed
    function playNotifSound(kind) {
        try {
            unlockNotifAudio();
            if (!notifAudioCtx) return;
            var now = notifAudioCtx.currentTime;
            var freqs = (kind === 'chat') ? [660, 880] : [880, 660];
            freqs.forEach(function (freq, i) {
                var osc = notifAudioCtx.createOscillator();
                var gain = notifAudioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.value = freq;
                gain.gain.setValueAtTime(0.0001, now + i * 0.15);
                gain.gain.exponentialRampToValueAtTime(0.25, now + i * 0.15 + 0.03);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + i * 0.15 + 0.14);
                osc.connect(gain);
                gain.connect(notifAudioCtx.destination);
                osc.start(now + i * 0.15);
                osc.stop(now + i * 0.15 + 0.16);
            });
        } catch (e) { /* audio unavailable */ }
    }

    var notifEventStyle = {
        'created':     { icon: 'ti-ticket',       color: 'text-primary' },
        'in_progress': { icon: 'ti-progress',      color: 'text-info' },
        'resolved':    { icon: 'ti-circle-check',  color: 'text-success' },
        'cancelled':   { icon: 'ti-circle-x',      color: 'text-danger' },
        'chat':        { icon: 'ti-message',       color: 'text-success' }
    };

    function escapeNotifHtml(text) {
        return String(text == null ? '' : text)
            .replace(/&/g, "&amp;").replace(/</g, "&lt;")
            .replace(/>/g, "&gt;").replace(/"/g, "&quot;");
    }

    function renderNotifList(listId, items, emptyText) {
        var $list = $(listId);
        if (!items || items.length === 0) {
            $list.html('<li class="px-3 py-3 text-center text-muted small">' + emptyText + '</li>');
            return;
        }
        var html = '';
        items.forEach(function (n) {
            var key = n.event || 'chat';
            var style = notifEventStyle[key] || { icon: 'ti-bell', color: 'text-secondary' };
            html += '<li>' +
                '<a href="#" class="notif-item d-flex gap-2 px-3 py-2 text-decoration-none ' + (n.read ? '' : 'bg-light') + '" data-id="' + n.id + '" data-url="' + escapeNotifHtml(n.url || '#') + '">' +
                    '<span class="' + style.color + ' fs-5 lh-1 mt-1"><i class="ti ' + style.icon + '"></i></span>' +
                    '<span class="flex-grow-1 overflow-hidden" style="min-width:0;">' +
                        '<span class="d-block small ' + (n.read ? 'text-dark' : 'fw-bold text-dark') + '">' + escapeNotifHtml(n.title) + '</span>' +
                        '<span class="d-block small text-muted text-truncate" title="' + escapeNotifHtml(n.message) + '">' + escapeNotifHtml(n.message) + '</span>' +
                        '<span class="d-block text-muted" style="font-size:0.7rem;">' + escapeNotifHtml(n.time || '') + '</span>' +
                    '</span>' +
                    (n.read ? '' : '<span class="badge bg-primary rounded-pill align-self-center ms-1" style="width:8px;height:8px;padding:0;">&nbsp;</span>') +
                '</a></li>';
        });
        $list.html(html);
    }

    function setNotifBadge(badgeId, count) {
        var $badge = $(badgeId);
        count = parseInt(count, 10) || 0;
        if (count > 0) {
            $badge.text(count > 99 ? '99+' : count).show();
        } else {
            $badge.hide();
        }
    }

    // Play a sound for brand-new arrivals (no toaster popup). This is the
    // only place that chimes, so each notification sounds exactly once no
    // matter how it arrived (socket ping, ticket-list nudge or polling).
    function soundNewNotifs(items, knownSet, kind) {
        if (!items) return;
        var openTicketId = $('#ticket_id').val();
        var hasNew = false;
        items.forEach(function (n) {
            if (!knownSet.has(n.id)) {
                knownSet.add(n.id);
                // Stay silent for the ticket that is open on screen
                var isOpenTicket = openTicketId && String(n.ticket_id) === String(openTicketId);
                if (!notifFirstLoad && !n.read && !isOpenTicket) {
                    hasNew = true;
                }
            }
        });
        if (hasNew) {
            playNotifSound(kind);
        }
    }

    // While a ticket is open on screen, its notifications (ticket + chat) are
    // marked as read 1 second after they show up — only while the tab is
    // actually visible, so nothing is marked read in a background tab.
    var OPEN_TICKET_READ_DELAY = 1000;
    var openTicketReadTimer = null;
    var openTicketHasUnread = false;

    function hasUnreadForOpenTicket(res) {
        var openTicketId = $('#ticket_id').val();
        if (!openTicketId) return false;

        return [].concat(res.tickets || [], res.chats || []).some(function (n) {
            return !n.read && String(n.ticket_id) === String(openTicketId);
        });
    }

    function markOpenTicketReadSoon() {
        var openTicketId = $('#ticket_id').val();
        if (!openTicketId || document.visibilityState !== 'visible') return;

        clearTimeout(openTicketReadTimer);
        openTicketReadTimer = setTimeout(function () {
            if (document.visibilityState !== 'visible') return;

            $.ajax({
                url: notifReadTicketRoute,
                type: 'POST',
                data: { ticket_id: openTicketId },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                complete: function () { fetchNotifications(); }
            });
        }, OPEN_TICKET_READ_DELAY);
    }

    function fetchNotifications() {
        $.ajax({
            url: notifFetchRoute,
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                renderNotifList('#notifTicketList', res.tickets, 'No ticket notifications.');
                renderNotifList('#notifChatList', res.chats, 'No new chat messages.');
                setNotifBadge('#notifTicketBadge', res.unread_tickets);
                setNotifBadge('#notifChatBadge', res.unread_chats);
                soundNewNotifs(res.tickets, knownNotifIds.tickets, 'ticket');
                soundNewNotifs(res.chats, knownNotifIds.chats, 'chat');
                notifFirstLoad = false;

                // Covers every way a notification arrives: page load, socket
                // ping, ticket-list nudge and polling
                openTicketHasUnread = hasUnreadForOpenTicket(res);
                if (openTicketHasUnread) {
                    markOpenTicketReadSoon();
                }
            }
        });
    }

    // Realtime events often come in bursts (one ticket write = several
    // events); fold them into a single fetch.
    var notifFetchTimer = null;
    function scheduleNotifFetch() {
        clearTimeout(notifFetchTimer);
        notifFetchTimer = setTimeout(fetchNotifications, 300);
    }

    // Poll slowly while realtime works, quickly while it doesn't
    var NOTIF_POLL_LIVE = 60000;
    var NOTIF_POLL_FALLBACK = 10000;
    var notifPollTimer = null;
    var notifPollMs = null;
    function setNotifPolling(ms) {
        if (notifPollMs === ms) return;
        notifPollMs = ms;
        clearInterval(notifPollTimer);
        notifPollTimer = setInterval(fetchNotifications, ms);
    }

    $(document).ready(function () {
        fetchNotifications();
        setNotifPolling(NOTIF_POLL_FALLBACK);

        if (typeof Echo !== 'undefined') {
            try {
                var notifSocketUp = false;
                var notifChannelUp = false;
                var syncNotifPolling = function () {
                    setNotifPolling(notifSocketUp && notifChannelUp ? NOTIF_POLL_LIVE : NOTIF_POLL_FALLBACK);
                };

                var connection = Echo.connector.pusher.connection;
                notifSocketUp = connection.state === 'connected';
                connection.bind('state_change', function (states) {
                    notifSocketUp = states.current === 'connected';
                    syncNotifPolling();
                });

                // Realtime nudge: any ticket write refreshes the dropdowns
                Echo.channel('tickets').listen('TicketListUpdated', function () {
                    scheduleNotifFetch();
                });

                // Reverb per-user pings for BOTH kinds: ticket events + chat messages
                Echo.private('App.Models.User.' + notifAuthUserId)
                    .subscribed(function () {
                        // First subscribe and every reconnect: catch up on
                        // anything that arrived while the socket was down
                        notifChannelUp = true;
                        syncNotifPolling();
                        scheduleNotifFetch();
                    })
                    .error(function () {
                        notifChannelUp = false;
                        syncNotifPolling();
                    })
                    .listen('UserNotified', function () {
                        // The fetch shows the new item, chimes once, and (if it
                        // belongs to the open ticket) marks it read after 1s
                        scheduleNotifFetch();
                    });
            } catch (e) { /* realtime unavailable, polling covers it */ }
        }

        // Background tabs can miss socket events — refresh when visible again
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                scheduleNotifFetch();
                // Back on the open ticket with unread items -> read in 1s
                if (openTicketHasUnread) {
                    markOpenTicketReadSoon();
                }
            } else {
                // Left the tab before the second passed -> keep them unread
                clearTimeout(openTicketReadTimer);
            }
        });

        // Click a notification -> mark read -> redirect where it belongs (ticket view)
        $(document).on('click', '.notif-item', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            var fallbackUrl = $(this).data('url');
            $.ajax({
                url: notifReadBase + '/' + id,
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (res) {
                    fetchNotifications();
                    if (res.url) window.location.href = res.url;
                    else if (fallbackUrl && fallbackUrl !== '#') window.location.href = fallbackUrl;
                },
                error: function () {
                    if (fallbackUrl && fallbackUrl !== '#') window.location.href = fallbackUrl;
                }
            });
        });

        // Mark all of one kind as read
        $(document).on('click', '.notif-mark-all', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $.ajax({
                url: notifReadAllRoute,
                type: 'POST',
                data: { kind: $(this).data('kind') },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function () { fetchNotifications(); }
            });
        });
    });
</script>
