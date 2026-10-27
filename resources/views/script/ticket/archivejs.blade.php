<script>
    $(document).ready(function() {
        // Nothing archived yet -> the page shows an empty state, no table
        if (!$('#ticketArchiveTable').length) return;

        /* ================= Column renderers ================= */

        function escapeHtml(text) {
            return String(text ?? '')
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function renderDate(data) {
            if (!data) {
                return '<span class="text-muted">-</span>';
            }

            return '<span class="small text-muted">' + moment(data).format('MMM DD, YYYY h:mm A') + '</span>';
        }

        function archiveUrl() {
            return ticketArchiveRoute + '?year=' + encodeURIComponent($('#archiveYearSelect').val() || '');
        }

        var dataTableArchive = $('#ticketArchiveTable').DataTable({
            "ajax": {
                "url": archiveUrl(),
                "type": "GET",
            },
            destroy: true,
            info: true,
            responsive: true,
            lengthChange: true,
            searching: true,
            paging: true,
            order: [], // keep the server order: newest first
            "columns": [
                // 1. Ticket Number
                {
                    data: 'ticket_number',
                    render: function(data) {
                        return '<span class="fw-bold">#' + escapeHtml(data ?? 'N/A') + '</span>';
                    }
                },
                // 2. Requester Info (Name & Office)
                {
                    data: 'requester',
                    render: function(data, type, row) {
                        var user = row.requester;
                        var fullName = 'Unknown User';
                        var initials = 'U';

                        if (user) {
                            var fname = user.fname ? user.fname.trim() : '';
                            var lname = user.lname ? user.lname.trim() : '';
                            var ext   = user.ext ? ' ' + user.ext.trim() : '';

                            if (fname || lname) {
                                fullName = (fname + ' ' + lname + ext).trim();
                            }

                            var firstInit = fname ? fname.charAt(0).toUpperCase() : '';
                            var lastInit  = lname ? lname.charAt(0).toUpperCase() : '';
                            initials = (firstInit + lastInit) || fullName.charAt(0).toUpperCase() || 'U';
                        }

                        var officeAbbr = (user && user.office && user.office.office_abbr)
                            ? user.office.office_abbr
                            : 'N/A';

                        return `
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-circle-sm bg-warning text-dark fw-bold d-flex align-items-center justify-content-center rounded-circle" style="width: 32px; height: 32px; font-size: 12px;">
                                    ${escapeHtml(initials)}
                                </div>
                                <div>
                                    <div class="fw-semibold small">${escapeHtml(fullName)}</div>
                                    <small class="text-muted">${escapeHtml(officeAbbr)}</small>
                                </div>
                            </div>`;
                    }
                },
                // 3. Subject
                {
                    data: 'issue_description',
                    render: function(data) {
                        var text = String(data ?? '').trim();

                        if (!text) {
                            return '<span class="text-muted">-</span>';
                        }

                        var short = text.length > 45 ? text.substring(0, 45) + '…' : text;

                        return `<span class="d-block text-truncate" style="max-width: 200px;" title="${escapeHtml(text)}">${escapeHtml(short)}</span>`;
                    }
                },
                // 4. Category
                {
                    data: 'category',
                    render: function(data, type, row) {
                        return row.category ? escapeHtml(row.category.ticketcatname) : '-';
                    }
                },
                // 5. Sub-Category
                {
                    data: 'subcategory',
                    render: function(data, type, row) {
                        return row.subcategory ? escapeHtml(row.subcategory.ticketsubcatname) : '-';
                    }
                },
                // 6. Support Desk (office the ticket was filed to)
                {
                    data: 'supportoffice',
                    render: function(data, type, row) {
                        return row.supportoffice
                            ? '<span class="badge bg-light text-dark border">' + escapeHtml(row.supportoffice.office_abbr) + '</span>'
                            : '-';
                    }
                },
                // 7. Priority Badge
                {
                    data: 'priority',
                    render: function(data) {
                        var badgeClass = 'bg-secondary';
                        if (data === 'High' || data === 'Urgent') badgeClass = 'bg-danger';
                        else if (data === 'Medium') badgeClass = 'bg-warning text-dark';
                        else if (data === 'Low') badgeClass = 'bg-info';

                        return `<span class="badge ${badgeClass} rounded-pill">${escapeHtml(data ?? 'Medium')}</span>`;
                    }
                },
                // 8. Status Badge (archived tickets are Resolved or Cancelled)
                {
                    data: 'status',
                    render: function(data) {
                        var statusClass = data === 'Resolved' ? 'bg-success' : 'bg-danger';
                        var label = data === 'Cancelled' ? 'Closed' : data;

                        return `<span class="badge ${statusClass}">${escapeHtml(label)}</span>`;
                    }
                },
                // 9. Resolved By
                {
                    data: 'resolved_by_name',
                    render: function(data) {
                        return data
                            ? '<span class="fw-semibold small">' + escapeHtml(data) + '</span>'
                            : '<span class="text-muted">-</span>';
                    }
                },
                // 10. Date Created
                {
                    data: 'created_at',
                    render: renderDate
                },
                // 11. Date Resolved
                {
                    data: 'resolved_at',
                    render: function(data, type, row) {
                        return row.status === 'Resolved' ? renderDate(data) : '<span class="text-muted">-</span>';
                    }
                },
                // 12. Actions (View)
                {
                    data: 'id',
                    render: function(data) {
                        return `<a href="${ticketViewRoute}?view=${data}" class="btn btn-sm btn-info text-white me-1" data-bs-toggle="tooltip" title="View Ticket"><i class="ti ti-eye"></i></a>`;
                    }
                }
            ],
            "createdRow": function (row, data, index) {
                $(row).attr('id', 'tr-' + data.id);
            }
        });

        // Switch year
        $('#archiveYearSelect').on('change', function () {
            $('#archiveYearLabel').text($(this).val());
            dataTableArchive.ajax.url(archiveUrl()).load();
        });
    });
</script>
