<script>
    $(document).ready(function () {
        var helpdeskFetchUrl = "{{ route('monitoring.helpdesk') }}";
        var lastCounts = null;
        var soundOn = true;
        var audioCtx = null;

        function unlockAudio() {
            try {
                if (!audioCtx) {
                    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
            } catch (e) { /* audio not supported */ }
            try {
                if ('speechSynthesis' in window) {
                    window.speechSynthesis.getVoices();
                }
            } catch (e) { /* speech not supported */ }
        }

        // Browsers block sound until the user interacts — unlock on first gesture
        $(document).one('click keydown touchstart', unlockAudio);

        $('#helpdesk-sound-toggle').on('click', function () {
            soundOn = !soundOn;
            unlockAudio();
            $('#helpdesk-sound-icon').attr('class', soundOn ? 'ti ti-volume' : 'ti ti-volume-off');
            if (!soundOn && 'speechSynthesis' in window) {
                window.speechSynthesis.cancel();
            }
        });

        function playAlertTone() {
            try {
                unlockAudio();
                if (!audioCtx) return;
                [880, 660, 880].forEach(function (freq, i) {
                    var osc = audioCtx.createOscillator();
                    var gain = audioCtx.createGain();
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.type = 'sine';
                    osc.frequency.value = freq;
                    var t = audioCtx.currentTime + (i * 0.22);
                    gain.gain.setValueAtTime(0.001, t);
                    gain.gain.exponentialRampToValueAtTime(0.4, t + 0.03);
                    gain.gain.exponentialRampToValueAtTime(0.001, t + 0.2);
                    osc.start(t);
                    osc.stop(t + 0.22);
                });
            } catch (e) { /* ignore */ }
        }

        function speak(text) {
            try {
                if (!soundOn || !('speechSynthesis' in window)) return;
                window.speechSynthesis.cancel();
                var msg = new SpeechSynthesisUtterance(text);
                msg.lang = 'en-US';
                msg.rate = 1;
                msg.volume = 1;
                window.speechSynthesis.speak(msg);
            } catch (e) { /* ignore */ }
        }

        function announce(status, priority) {
            var prio = priority ? priority + ' priority. ' : '';
            if (status === 'Pending') {
                speak('New Pending Ticket, ' + prio + 'Please sign in your account to check it.');
            } else if (status === 'In Progress') {
                speak('One Ticket marks as In Progress Ticket');
            } else if (status === 'Resolved') {
                speak('One Ticket mark as Resolved Ticket');
            }
        }

        function priorityBadgeClass(priority) {
            if (priority === 'High' || priority === 'Urgent') return 'bg-danger-subtle text-danger';
            if (priority === 'Medium') return 'bg-warning-subtle text-warning';
            return 'bg-secondary-subtle text-secondary';
        }

        function escapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function renderHelpdesk(data) {
            if (!data) return;

            $('#helpdesk-pending-count').text(data.counts.pending);
            $('#helpdesk-progress-count').text(data.counts.in_progress);
            $('#helpdesk-resolved-count').text(data.counts.resolved);

            $('#helpdesk-prio-high').text(data.priority.High);
            $('#helpdesk-prio-medium').text(data.priority.Medium);
            $('#helpdesk-prio-low').text(data.priority.Low);
            $('#helpdesk-prio-urgent').text(data.priority.Urgent);

            var html = '';
            if (data.latest && data.latest.length > 0) {
                var items = '';
                data.latest.slice(0, 5).forEach(function (t) {
                    var shortDesc = t.issue_description
                        ? (t.issue_description.length > 18 ? t.issue_description.substring(0, 18) + '…' : t.issue_description)
                        : 'Ticket';
                    items += '<div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">' +
                        '<div class="text-truncate me-1">' +
                        '<small class="fw-medium d-block text-truncate" style="max-width: 90px;">' + escapeHtml(shortDesc) + '</small>' +
                        '<span class="text-muted extra-small">#' + escapeHtml(t.ticket_number || t.id) + '</span>' +
                        '</div>' +
                        '<span class="badge extra-small ' + priorityBadgeClass(t.priority) + '">' + escapeHtml(t.priority || '-') + '</span>' +
                        '</div>';
                });
                // Duplicate set for the seamless infinite marquee loop (same as other TV cards)
                html = items + items;
            } else {
                html = '<div class="text-muted extra-small text-center py-2">No active tickets</div>';
            }
            $('#helpdesk-latest-list').html(html);
        }

        function fetchHelpdesk(announceNew) {
            $.ajax({
                url: helpdeskFetchUrl,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    var data = response.data || response;
                    if (!data || !data.counts) return;

                    // Sound alerts only when a count grew (new arrival), never on first load
                    if (announceNew && lastCounts) {
                        var topPriority = (data.latest && data.latest[0] && data.latest[0].priority) || '';
                        if (data.counts.pending > lastCounts.pending) {
                            playAlertTone();
                            announce('Pending', topPriority);
                        }
                        if (data.counts.in_progress > lastCounts.in_progress) {
                            playAlertTone();
                            announce('In Progress', topPriority);
                        }
                        if (data.counts.resolved > lastCounts.resolved) {
                            playAlertTone();
                            announce('Resolved', topPriority);
                        }
                    }

                    lastCounts = data.counts;
                    renderHelpdesk(data);
                }
            });
        }

        // Initial load (no sound on first paint)
        fetchHelpdesk(false);

        // Realtime via existing ticket feed
        if (typeof Echo !== 'undefined') {
            Echo.channel('tickets')
                .listen('TicketListUpdated', function () {
                    fetchHelpdesk(true);
                });
        }

        // Fallback polling in case Reverb is unreachable
        setInterval(function () {
            fetchHelpdesk(true);
        }, 15000);
    });
</script>
