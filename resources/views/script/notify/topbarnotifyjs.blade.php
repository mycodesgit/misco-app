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
                    '<span class="flex-grow-1 min-w-0">' +
                        '<span class="d-block small ' + (n.read ? 'text-dark' : 'fw-bold text-dark') + '">' + escapeNotifHtml(n.title) + '</span>' +
                        '<span class="d-block small text-muted text-truncate">' + escapeNotifHtml(n.message) + '</span>' +
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

    // Play a sound for brand-new arrivals (no toaster popup)
    function soundNewNotifs(items, knownSet, kind) {
        if (!items) return;
        var hasNew = false;
        items.forEach(function (n) {
            if (!knownSet.has(n.id)) {
                knownSet.add(n.id);
                if (!notifFirstLoad && !n.read) {
                    hasNew = true;
                }
            }
        });
        if (hasNew) {
            playNotifSound(kind);
        }
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
            }
        });
    }

    $(document).ready(function () {
        fetchNotifications();
        // Polling stays as fallback in case Reverb drops; realtime below is primary
        setInterval(fetchNotifications, 60000);

        if (typeof Echo !== 'undefined') {
            try {
                // Realtime nudge: any ticket write refreshes the dropdowns
                Echo.channel('tickets').listen('TicketListUpdated', function () {
                    fetchNotifications();
                });
                // Reverb per-user pings for BOTH kinds: ticket events + chat messages
                Echo.private('App.Models.User.' + notifAuthUserId)
                    .listen('UserNotified', function (e) {
                        // Already viewing that ticket -> mark read silently, no sound
                        var openTicketId = $('#ticket_id').val();
                        if (openTicketId && e && String(e.ticket_id) === String(openTicketId)) {
                            $.ajax({
                                url: notifReadTicketRoute,
                                type: 'POST',
                                data: { ticket_id: openTicketId },
                                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                                complete: function () { fetchNotifications(); }
                            });
                            return;
                        }
                        playNotifSound(e && e.kind === 'chat' ? 'chat' : 'ticket');
                        fetchNotifications();
                    });
            } catch (e) { /* realtime unavailable, polling covers it */ }
        }

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
