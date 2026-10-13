<script>
    $(document).ready(function () {
        var personnelFetchUrl = "{{ route('monitoring.personnel') }}";

        function stationBadgeClass(station) {
            if (station === 'Office') return 'bg-success-subtle text-success';
            if (station === 'Field') return 'bg-warning-subtle text-warning';
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

        function renderPersonnel(data) {
            if (!data) return;

            $('#personnel-onduty-count').text(data.onduty);
            $('#personnel-total-count').text(data.total);
            $('#personnel-field-count').text(data.field + ' Staff');
            $('#personnel-office-count').text(data.office + ' Staff');

            var html = '';
            if (data.staff && data.staff.length > 0) {
                var items = '';
                data.staff.forEach(function (s) {
                    items += '<div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">' +
                        '<div class="text-truncate me-1">' +
                        '<small class="fw-bold d-block text-truncate" style="max-width: 90px;">' + escapeHtml(s.short_name) + '</small>' +
                        '<span class="text-muted extra-small d-block text-truncate" style="max-width: 90px;">' + escapeHtml(s.office) + '</span>' +
                        '</div>' +
                        '<span class="badge extra-small ' + stationBadgeClass(s.station) + '">' + escapeHtml(s.station || '-') + '</span>' +
                        '</div>';
                });
                // Duplicate set for the seamless infinite marquee loop (same as other TV cards)
                html = items + items;
            } else {
                html = '<div class="text-muted extra-small text-center py-2">No staff found</div>';
            }
            $('#personnel-staff-list').html(html);
        }

        function fetchPersonnel() {
            $.ajax({
                url: personnelFetchUrl,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    var data = response.data || response;
                    if (!data || typeof data.total === 'undefined') return;
                    renderPersonnel(data);
                }
            });
        }

        // Initial load
        fetchPersonnel();

        // Realtime via station channel (same event the dashboard Station modal broadcasts)
        if (typeof Echo !== 'undefined') {
            Echo.channel('station')
                .listen('UserStationUpdated', function () {
                    fetchPersonnel();
                });
        }

        // Fallback polling in case Reverb is unreachable
        setInterval(function () {
            fetchPersonnel();
        }, 15000);
    });
</script>
