<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right"
    };

    $(document).ready(function() {
        var now = new Date();
        var currentMonth = ("0" + (now.getMonth() + 1)).slice(-2);
        var currentYear = now.getFullYear();

        $('#filterMonth').val(currentMonth);
        $('#filterYear').val(String(currentYear));

        var dataTable = $('#clientfeedbackTable').DataTable({
            "ajax": {
                "url": clientfeedbackReadRoute,
                "type": "GET",
                "data": function(d) {
                    d.month = $('#filterMonth').val();
                    d.year = $('#filterYear').val();
                    d.off_id = $('#filterOffice').val();
                    d.user_id = $('#filterUser').val();
                }
            },
            destroy: true,
            info: true,
            responsive: true,
            lengthChange: true,
            searching: true,
            paging: true,
            order: [[8, "desc"]],
            "columns": [
                {
                    data: 'ticket_number',
                    render: function(data) {
                        return '<span class="fw-bold">#' + (data ?? 'N/A') + '</span>';
                    }
                },
                { data: 'requester' },
                { data: 'office' },
                { data: 'resolved_by' },
                { data: 'category' },
                { data: 'subcategory' },
                {
                    data: 'rating',
                    render: function(data) {
                        var rating = parseInt(data, 10) || 0;
                        var stars = '';
                        for (var i = 1; i <= 5; i++) {
                            stars += '<i class="ti ti-star-filled ' + (i <= rating ? 'text-warning' : 'text-muted') + '"></i>';
                        }
                        return '<span class="text-nowrap" title="' + rating + ' / 5">' + stars + '</span>';
                    }
                },
                {
                    data: 'feedback',
                    render: function(data) {
                        return data ? '<span class="small">' + $('<div>').text(data).html() + '</span>' : '<span class="text-muted">-</span>';
                    }
                },
                { data: 'date' }
            ]
        });

        $('#clientfeedbackForm').submit(function(event) {
            event.preventDefault();
            dataTable.ajax.reload();
        });

        // Specific-user dropdown follows the selected office
        function loadOfficeUsers(offId, done) {
            var $user = $('#filterUser');
            $user.prop('disabled', true).html('<option value="">All Users</option>');
            $('#filterUserHint').text('Loading users...');
            if (!offId) {
                $('#filterUserHint').text('Select an office first.');
                if (done) done();
                return;
            }
            $.ajax({
                url: clientfeedbackUsersRoute,
                type: 'GET',
                data: { off_id: offId },
                success: function (response) {
                    var users = (response && response.data) || [];
                    users.forEach(function (u) {
                        $user.append($('<option>', { value: u.id, text: u.name }));
                    });
                    $user.prop('disabled', false);
                    $('#filterUserHint').text(users.length + ' user(s) in this office.');
                    if (done) done();
                },
                error: function () {
                    $('#filterUserHint').text('Could not load users.');
                    if (done) done();
                }
            });
        }

        $('#filterOffice').on('change', function () {
            loadOfficeUsers($(this).val(), function () {
                dataTable.ajax.reload();
            });
        });

        $('#filterUser').on('change', function () {
            dataTable.ajax.reload();
        });

        // Non-admins get a locked office — preload its users on open
        if ($('#filterOffice').val()) {
            loadOfficeUsers($('#filterOffice').val());
        }

        // Total rating summary follows the current filter (All / certain office)
        dataTable.on('xhr', function (e, settings, json) {
            renderRatingSummary(json && json.summary ? json.summary : null);
        });

        function renderRatingSummary(summary) {
            var total = summary ? (summary.total || 0) : 0;
            var average = summary ? (parseFloat(summary.average) || 0) : 0;
            var dist = summary && summary.dist ? summary.dist : {};

            $('#cf-scope-label').text(summary && summary.scope ? summary.scope : '-');
            $('#cf-total-count').text(total);
            $('#cf-avg-value').text(total > 0 ? average.toFixed(1) : '0');

            var stars = '';
            var rounded = Math.round(average);
            for (var i = 1; i <= 5; i++) {
                stars += '<i class="ti ti-star-filled ' + (i <= rounded && total > 0 ? 'text-warning' : 'text-muted') + '"></i>';
            }
            $('#cf-avg-stars').html(stars);

            [5, 4, 3, 2, 1].forEach(function (star) {
                var count = parseInt(dist[star], 10) || 0;
                var pct = total > 0 ? Math.round((count / total) * 100) : 0;
                $('#cf-count-' + star).text(count);
                $('#cf-bar-' + star).css('width', pct + '%');
            });
        }
    });
</script>
