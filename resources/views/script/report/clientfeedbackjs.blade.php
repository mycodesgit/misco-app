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
                }
            },
            destroy: true,
            info: true,
            responsive: true,
            lengthChange: true,
            searching: true,
            paging: true,
            order: [[7, "desc"]],
            "columns": [
                {
                    data: 'ticket_number',
                    render: function(data) {
                        return '<span class="fw-bold">#' + (data ?? 'N/A') + '</span>';
                    }
                },
                { data: 'requester' },
                { data: 'office' },
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
    });
</script>
