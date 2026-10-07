<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right"
    };

    $(document).ready(function() {
        var payloadCache = {};

        var now = new Date();
        var currentMonth = ("0" + (now.getMonth() + 1)).slice(-2);
        var currentYear = now.getFullYear();

        $('#filterMonth').val(currentMonth);
        $('#filterYear').val(String(currentYear));

        var dataTable = $('#audittrailTable').DataTable({
            "ajax": {
                "url": audittrailReadRoute,
                "type": "GET",
                "data": function(d) {
                    d.category = $('#filterCategory').val();
                    d.month = $('#filterMonth').val();
                    d.year = $('#filterYear').val();
                },
                "error": function(xhr) {
                    var msg = 'Failed to load audit logs';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    toastr.error(msg);
                }
            },
            destroy: true,
            info: true,
            responsive: true,
            lengthChange: true,
            searching: true,
            paging: true,
            order: [[5, "desc"]],
            "columns": [
                { data: 'user' },
                { data: 'email' },
                {
                    data: 'action',
                    render: function(data) {
                        return '<span class="badge bg-info">' + $('<div>').text(data ?? '-').html() + '</span>';
                    }
                },
                { data: 'ip' },
                { data: 'agent' },
                { data: 'date' },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        payloadCache[row.id] = row.full || 'No payload';
                        return '<button type="button" class="btn btn-sm btn-success text-white btn-view-payload" data-id="' + row.id + '" title="View JSON payload"><i class="ti ti-code"></i></button>';
                    }
                }
            ]
        });

        $('#audittrailForm').submit(function(event) {
            event.preventDefault();
            dataTable.ajax.reload();
        });

        function highlightAuditJson(json) {
            var escaped = String(json)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
            return escaped.replace(/(&quot;(\\.|[^"\\])*?&quot;)(\s*:)?|\b(true|false|null)\b|-?\d+(\.\d+)?([eE][+-]?\d+)?/g, function(match, str, _inner, colon, bool) {
                var cls = 'jn';
                if (str) {
                    cls = colon ? 'jk' : 'js';
                } else if (bool) {
                    cls = 'jb';
                }
                return '<span class="' + cls + '">' + match + '</span>';
            });
        }

        $(document).on('click', '.btn-view-payload', function() {
            var payload = payloadCache[$(this).data('id')] || 'No payload';
            $('#actionDataPayload code').html(highlightAuditJson(payload));
            $('#actionDataModal').modal('show');
        });
    });
</script>
