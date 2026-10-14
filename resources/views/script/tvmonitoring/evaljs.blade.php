<script>
    $(document).ready(function () {
        var evalFetchUrl = "{{ route('monitoring.evaluation') }}";

        function escapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function renderEval(data) {
            if (!data) return;

            var isOn = data.status === 'On';

            // Status badge + big status text
            var $badge = $('#eval-status-badge');
            $badge.removeClass('bg-success-subtle text-success border-success-subtle bg-secondary-subtle text-secondary border-secondary-subtle');
            var $big = $('#eval-status-big');
            $big.removeClass('text-success text-secondary');
            if (isOn) {
                $badge.addClass('bg-success-subtle text-success border-success-subtle');
                $big.addClass('text-success');
            } else {
                $badge.addClass('bg-secondary-subtle text-secondary border-secondary-subtle');
                $big.addClass('text-secondary');
            }
            $('#eval-status-text').text(data.status);
            $big.text(data.status);

            // Term + today's response count
            $('#eval-term').text(data.term);
            $('#eval-today-count').text(data.today);
            $('#eval-today-footer').text(data.today);

            // Latest responses (no time badges — rows show faculty + evaluator + average)
            var html = '';
            if (data.latest && data.latest.length > 0) {
                var items = '';
                data.latest.forEach(function (e) {
                    var title = e.title && e.title.length > 18 ? e.title.substring(0, 18) + '…' : (e.title || '—');
                    var sub = e.sub && e.sub.length > 22 ? e.sub.substring(0, 22) + '…' : (e.sub || '');
                    items += '<div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">' +
                        '<div class="text-truncate me-1">' +
                        '<small class="fw-bold d-block text-truncate" style="max-width: 95px;">' + escapeHtml(title) + '</small>' +
                        '<span class="text-muted extra-small d-block text-truncate" style="max-width: 95px;">' + escapeHtml(sub) + '</span>' +
                        '</div>' +
                        '<span class="badge bg-success-subtle text-success extra-small">Submitted</span>' +
                        '</div>';
                });
                // Duplicate set for the seamless infinite marquee loop (same as other TV cards)
                html = items + items;
            } else {
                html = '<div class="text-muted extra-small text-center py-2">No new responses in the last minute</div>';
            }
            $('#eval-latest-list').html(html);
        }

        function fetchEval() {
            $.ajax({
                url: evalFetchUrl,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    var data = response.data || response;
                    if (!data || typeof data.status === 'undefined') return;
                    renderEval(data);
                }
            });
        }

        // Initial load
        fetchEval();

        // Realtime: responses are written by the external QCE system straight
        // into the eval DB, so polling is the live channel here (same as queue).
        setInterval(function () {
            fetchEval();
        }, 15000);
    });
</script>
