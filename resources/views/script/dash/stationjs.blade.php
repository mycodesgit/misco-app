<script>
    $(document).ready(function () {
        const stationShowUrl = "{{ route('station.show') }}";
        const stationUpdateUrl = "{{ route('station.update') }}";
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        const myUserId = {{ auth()->id() }};

        const badgeColor = {
            'Office': 'bg-success-subtle text-success border-success-subtle',
            'Field': 'bg-warning-subtle text-warning border-warning-subtle',
            'Leave': 'bg-secondary-subtle text-secondary border-secondary-subtle'
        };

        function paintStationBadge(station) {
            const $badge = $('#stationBadge');
            $badge.text(station);
            $badge.removeClass('bg-success-subtle text-success border-success-subtle bg-warning-subtle text-warning border-warning-subtle bg-secondary-subtle text-secondary border-secondary-subtle text-bg-success text-bg-warning text-bg-secondary bg-success bg-warning bg-secondary');
            $badge.addClass(badgeColor[station] || 'bg-success-subtle text-success border-success-subtle');
        }

        function syncStationRadios(station) {
            $('input[name="station"]').each(function () {
                $(this).prop('checked', $(this).val() === station);
            });
        }

        // Sync with server truth every time the modal opens (no stale radios)
        $('#stationModal').on('show.bs.modal', function () {
            $.ajax({
                url: stationShowUrl,
                type: 'GET',
                success: function (data) {
                    if (data && data.station) {
                        syncStationRadios(data.station);
                        paintStationBadge(data.station);
                    }
                }
            });
        });

        // Save — POST, no page reload
        $('#saveStationBtn').on('click', function () {
            const station = $('input[name="station"]:checked').val();

            if (!station) {
                toastr.warning('Pick Office, Field, or Leave first.');
                return;
            }

            const $btn = $(this).prop('disabled', true);

            $.ajax({
                url: stationUpdateUrl,
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                data: { station: station },
                success: function (res) {
                    paintStationBadge(res.data.station);
                    toastr.success(res.message);
                    $('#stationModal').modal('hide');
                },
                error: function (xhr) {
                    const msg = (xhr.responseJSON && (xhr.responseJSON.message || (xhr.responseJSON.errors && xhr.responseJSON.errors.station && xhr.responseJSON.errors.station[0]))) || 'Failed to update station.';
                    toastr.error(msg);
                },
                complete: function () {
                    $btn.prop('disabled', false);
                }
            });
        });

        // Realtime: any station change on the channel repaints instantly, no reload
        if (typeof Echo !== 'undefined') {
            Echo.channel('station')
                .listen('UserStationUpdated', function (e) {
                    if (!e || !e.user_id) return;
                    if (parseInt(e.user_id) === parseInt(myUserId)) {
                        paintStationBadge(e.station);
                        syncStationRadios(e.station);
                    } else if (e.name) {
                        toastr.info(e.name + ' is now: ' + e.station);
                    }
                });
        }
    });
</script>
