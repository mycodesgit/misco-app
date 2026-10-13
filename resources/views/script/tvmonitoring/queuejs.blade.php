<script>
    $(document).ready(function () {
        var queueFetchUrl = "{{ route('monitoring.queue') }}";

        function escapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function renderQueue(data) {
            if (!data) return;

            var isOn = data.mode === 'On';

            // Mode badge: Live vs Off
            var $badge = $('#queue-mode-badge');
            $badge.removeClass('bg-success-subtle text-success border-success-subtle bg-secondary-subtle text-secondary border-secondary-subtle');
            if (isOn) {
                $badge.addClass('bg-success-subtle text-success border-success-subtle');
                $badge.html('<i class="fas fa-circle me-1 style-dot blink-dot"></i>Live');
            } else {
                $badge.addClass('bg-secondary-subtle text-secondary border-secondary-subtle');
                $badge.html('<i class="fas fa-circle me-1 style-dot"></i>Off');
            }

            $('#queue-on-view').toggleClass('d-none', !isOn);
            $('#queue-off-view').toggleClass('d-none', isOn);
            if (!isOn) return;

            // Main serving window
            if (data.main) {
                $('#queue-main-window').text(data.main.window + ' Serving');
                $('#queue-main-number').text(data.main.number);
            }

            $('#queue-waiting-count').text(data.waiting);
            $('#queue-window-count').text(data.counters ? data.counters.length : 0);

            // Per-window numbers (duplicated for the seamless infinite marquee loop)
            var html = '';
            if (data.counters && data.counters.length > 0) {
                var items = '';
                data.counters.forEach(function (c) {
                    items += '<div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">' +
                        '<div class="text-truncate me-1">' +
                        '<small class="fw-medium text-muted d-block text-truncate" style="max-width: 90px;">' + escapeHtml(c.window) + '</small>' +
                        '<span class="text-muted extra-small d-block text-truncate" style="max-width: 90px;">' + escapeHtml(c.category || '') + '</span>' +
                        '</div>' +
                        '<span class="fw-bold small">' + escapeHtml(c.number) + '</span>' +
                        '</div>';
                });
                html = items + items;
            } else {
                html = '<div class="text-muted extra-small text-center py-2">No counters</div>';
            }
            $('#queue-counter-list').html(html);
        }

        function fetchQueue() {
            $.ajax({
                url: queueFetchUrl,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    var data = response.data || response;
                    if (!data || typeof data.mode === 'undefined') return;
                    renderQueue(data);
                }
            });
        }

        // Initial load
        fetchQueue();

        // Realtime: queue numbers are written by the external COAS system straight
        // into the settings DB, so polling is the live channel here (same as the
        // HelpDesk card's fallback, but as the primary mechanism).
        setInterval(function () {
            fetchQueue();
        }, 10000);
    });
</script>
