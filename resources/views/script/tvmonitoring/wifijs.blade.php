<script>
    $(document).ready(function () {
        var wifiFetchUrl = "{{ route('monitoring.wifi') }}";

        function renderWifi(data) {
            if (!data) return;

            $('#wifi-available-big').text(data.available);
            $('#wifi-available-count').text(data.available);
            $('#wifi-used-count').text(data.used);
            $('#wifi-total-count').text(data.total);
            $('#wifi-term').text(data.term);
            $('#wifi-available-bar').css('width', data.pct + '%').attr('aria-valuenow', data.pct);
        }

        function fetchWifi() {
            $.ajax({
                url: wifiFetchUrl,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    var data = response.data || response;
                    if (!data || typeof data.available === 'undefined') return;
                    renderWifi(data);
                }
            });
        }

        // Initial load
        fetchWifi();

        // Realtime: vouchers change via the Wifi Vouchers manage page,
        // so polling is the live channel here (same as queue).
        setInterval(function () {
            fetchWifi();
        }, 15000);
    });
</script>
