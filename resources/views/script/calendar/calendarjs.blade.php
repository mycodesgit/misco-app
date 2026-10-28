<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right"
    };

    $(document).ready(function () {
        var $frame = $('#googleCalendarFrame');

        function reloadCalendar() {
            if ($frame.length) {
                $frame.attr('src', $frame.attr('src'));
            }
        }

        $('#calendarRefreshBtn').on('click', reloadCalendar);

        // Sync with Google: users log in here with email + password, so the
        // embedded calendar only shows events once the browser is signed in to
        // a Google account that can see it. Open Google's account chooser in a
        // popup, then reload the calendar as soon as the popup is closed.
        $('#googleSyncBtn').on('click', function () {
            var url = 'https://accounts.google.com/AccountChooser?continue=' +
                encodeURIComponent('https://calendar.google.com/calendar/r');

            var popup = window.open(url, 'googleSync', 'width=500,height=650');

            if (!popup) {
                // Popup blocked — fall back to a new tab
                window.open(url, '_blank');
                toastr.info('Sign in with your CPSU Google account in the new tab, then click the refresh button here.');
                return;
            }

            var watcher = setInterval(function () {
                if (popup.closed) {
                    clearInterval(watcher);
                    reloadCalendar();
                    toastr.success('Calendar refreshed.');
                }
            }, 700);
        });

        // Save / remove the office's Google Calendar ID
        $('#calendarSettingsForm').submit(function (event) {
            event.preventDefault();

            var $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true);

            $.ajax({
                url: calendarUpdateRoute,
                type: 'POST',
                data: $(this).serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        toastr.success(response.message);
                        // Reload to switch between the calendar and the empty state
                        setTimeout(function () { window.location.reload(); }, 800);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'An error occurred');
                },
                complete: function () {
                    $btn.prop('disabled', false);
                }
            });
        });
    });
</script>
