<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right"
    };
    $(document).ready(function() {
        $('#addTicket').submit(function(event) {
            event.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                url: ticketCreateRoute,
                type: "POST",
                data: formData,
                success: function(response) {
                    if(response.success) {
                        toastr.success(response.message);
                        console.log(response);
                        $(document).trigger('ticketAdded');
                        $('#addTicket')[0].reset();
                    } else {
                        toastr.error(response.message);
                        console.log(response);
                    }
                },
                error: function(xhr, status, error, message) {
                    var errorMessage = xhr.responseText ? JSON.parse(xhr.responseText).message : 'An error occurred';
                    toastr.error(errorMessage);
                }
            });
        });

        /* ================= Shared ticket column renderers ================= */

        function escapeHtml(text) {
            return String(text ?? '')
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Subject — the issue description the requester typed in
        function renderTicketSubject(data) {
            var text = String(data ?? '').trim();

            if (!text) {
                return '<span class="text-muted">-</span>';
            }

            var short = text.length > 45 ? text.substring(0, 45) + '…' : text;

            return `<span class="d-block text-truncate" style="max-width: 200px;" title="${escapeHtml(text)}">${escapeHtml(short)}</span>`;
        }

        // Resolved By — the staff member who marked the ticket as Resolved
        function renderResolvedBy(data) {
            if (!data) {
                return '<span class="text-muted">-</span>';
            }

            return '<span class="fw-semibold small">' + escapeHtml(data) + '</span>';
        }

        // Date Created
        function renderDateCreated(data) {
            if (!data) {
                return '<span class="text-muted">-</span>';
            }

            return '<span class="small text-muted">' + moment(data).format('MMM DD, YYYY h:mm A') + '</span>';
        }

        // Date Resolved — only meaningful while the ticket is Resolved
        function renderDateResolved(data, type, row) {
            if (row.status !== 'Resolved' || !data) {
                return '<span class="text-muted">-</span>';
            }

            return '<span class="small text-muted">' + moment(data).format('MMM DD, YYYY h:mm A') + '</span>';
        }

        // Duration — how long the ticket took, or has been running so far
        function renderDuration(data, type, row) {
            if (!data) {
                return '<span class="text-muted">-</span>';
            }

            var badgeClass = 'bg-light text-dark border';

            if (row.status === 'Resolved') {
                badgeClass = 'bg-success text-white';
            } else if (row.status === 'Cancelled') {
                badgeClass = 'bg-secondary text-white';
            } else if (row.status === 'In Progress') {
                badgeClass = 'bg-warning text-dark';
            }

            return '<span class="badge ' + badgeClass + '">' + escapeHtml(data) + '</span>';
        }

        var dataTablePending = $('#ticketpendingTable').DataTable({
            "ajax": {
                "url": ticketPendingReadRoute,
                "type": "GET",
            },
            destroy: true,
            info: true,
            responsive: true,
            lengthChange: true,
            searching: true,
            paging: true,
            "columns": [
                // 1. Ticket Number
                {
                    data: 'ticket_number',
                    render: function(data) {
                        return '<span class="fw-bold">#' + (data ?? 'N/A') + '</span>';
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

                            // Construct Full Name safely
                            if (fname || lname) {
                                fullName = (fname + ' ' + lname + ext).trim();
                            } else if (user.name) {
                                fullName = user.name; // Fallback in case a virtual attribute exists
                            }

                            // Extract Initials safely
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
                                    ${initials}
                                </div>
                                <div>
                                    <div class="fw-semibold small">${fullName}</div>
                                    <small class="text-muted">${officeAbbr}</small>
                                </div>
                            </div>`;
                    }
                },
                // 3. Subject
                {
                    data: 'issue_description',
                    render: renderTicketSubject
                },
                // 4. Category
                {
                    data: 'category',
                    render: function(data, type, row) {
                        return row.category ? row.category.ticketcatname : '-';
                    }
                },
                // 5. Sub-Category
                {
                    data: 'subcategory',
                    render: function(data, type, row) {
                        return row.subcategory ? row.subcategory.ticketsubcatname : '-';
                    }
                },
                // 6. Priority Badge
                {
                    data: 'priority',
                    render: function(data) {
                        var badgeClass = 'bg-secondary';
                        if (data === 'High' || data === 'Urgent') badgeClass = 'bg-danger';
                        else if (data === 'Medium') badgeClass = 'bg-warning text-dark';
                        else if (data === 'Low') badgeClass = 'bg-info';

                        return `<span class="badge ${badgeClass} rounded-pill">${data ?? 'Medium'}</span>`;
                    }
                },
                // 7. Status Badge
                {
                    data: 'status',
                    render: function(data) {
                        var statusClass = 'bg-secondary';
                        if (data === 'Pending') statusClass = 'bg-primary';
                        else if (data === 'In Progress') statusClass = 'bg-warning text-dark';
                        else if (data === 'Resolved' || data === 'Completed') statusClass = 'bg-success';
                        else if (data === 'Cancelled') statusClass = 'bg-danger';

                        return `<span class="badge ${statusClass}">${data ?? 'Pending'}</span>`;
                    }
                },
                // 8. Actions (View / Edit / Delete)
                {
                    data: 'id',
                    render: function(data, type, row) {
                        var baseUrl = "{{ route('tickets.store') }}";
                        var viewBtn = `<a href="${baseUrl}?view=${data}" class="btn btn-sm btn-info text-white me-1" data-bs-toggle="tooltip" title="View Ticket"><i class="ti ti-eye"></i></a>`;
                        var deleteBtn = `<button type="button" value="${data}" class="btn btn-sm btn-danger ticket-delete" data-bs-toggle="tooltip" title="Delete Ticket"><i class="ti ti-trash"></i></button>`;

                        return viewBtn + (typeof editBtn !== 'undefined' ? editBtn : '') + deleteBtn;
                    }
                }
            ],
            "createdRow": function (row, data, index) {
                $(row).attr('id', 'tr-' + data.id);
            }
        });
        $(document).on('ticketAdded', function() {
            dataTablePending.ajax.reload();
        });
        dataTablePending.on('draw', function () {
            $('[data-toggle="tooltip"]').tooltip();
        });


        var dataTableProgress = $('#ticketprogressTable').DataTable({
            "ajax": {
                "url": ticketProgressRoute,
                "type": "GET",
            },
            destroy: true,
            info: true,
            responsive: true,
            lengthChange: true,
            searching: true,
            paging: true,
            "columns": [
                // 1. Ticket Number
                {
                    data: 'ticket_number',
                    render: function(data) {
                        return '<span class="fw-bold">#' + (data ?? 'N/A') + '</span>';
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

                            // Construct Full Name safely
                            if (fname || lname) {
                                fullName = (fname + ' ' + lname + ext).trim();
                            } else if (user.name) {
                                fullName = user.name; // Fallback in case a virtual attribute exists
                            }

                            // Extract Initials safely
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
                                    ${initials}
                                </div>
                                <div>
                                    <div class="fw-semibold small">${fullName}</div>
                                    <small class="text-muted">${officeAbbr}</small>
                                </div>
                            </div>`;
                    }
                },
                // 3. Subject
                {
                    data: 'issue_description',
                    render: renderTicketSubject
                },
                // 4. Category
                {
                    data: 'category',
                    render: function(data, type, row) {
                        return row.category ? row.category.ticketcatname : '-';
                    }
                },
                // 5. Sub-Category
                {
                    data: 'subcategory',
                    render: function(data, type, row) {
                        return row.subcategory ? row.subcategory.ticketsubcatname : '-';
                    }
                },
                // 6. Priority Badge
                {
                    data: 'priority',
                    render: function(data) {
                        var badgeClass = 'bg-secondary';
                        if (data === 'High' || data === 'Urgent') badgeClass = 'bg-danger';
                        else if (data === 'Medium') badgeClass = 'bg-warning text-dark';
                        else if (data === 'Low') badgeClass = 'bg-info';

                        return `<span class="badge ${badgeClass} rounded-pill">${data ?? 'Medium'}</span>`;
                    }
                },
                // 7. Status Badge
                {
                    data: 'status',
                    render: function(data) {
                        var statusClass = 'bg-secondary';
                        if (data === 'Pending') statusClass = 'bg-primary';
                        else if (data === 'In Progress') statusClass = 'bg-warning text-dark';
                        else if (data === 'Resolved' || data === 'Completed') statusClass = 'bg-success';
                        else if (data === 'Cancelled') statusClass = 'bg-danger';

                        return `<span class="badge ${statusClass}">${data ?? 'Pending'}</span>`;
                    }
                },
                // 8. Actions (View / Edit / Delete)
                {
                    data: 'id',
                    render: function(data, type, row) {
                        var baseUrl = "{{ route('tickets.store') }}";
                        var viewBtn = `<a href="${baseUrl}?view=${data}" class="btn btn-sm btn-info text-white me-1" data-bs-toggle="tooltip" title="View Ticket"><i class="ti ti-eye"></i></a>`;

                        return viewBtn;
                    }
                }
            ],
            "createdRow": function (row, data, index) {
                $(row).attr('id', 'tr-' + data.id);
            }
        });
        $(document).on('ticketProgressAdded', function() {
            dataTableProgress.ajax.reload();
        });


        var dataTableResolved = $('#ticketresolvedTable').DataTable({
            "ajax": {
                "url": ticketResolvedRoute,
                "type": "GET",
            },
            destroy: true,
            info: true,
            responsive: true,
            lengthChange: true,
            searching: true,
            paging: true,
            "columns": [
                // 1. Ticket Number
                {
                    data: 'ticket_number',
                    render: function(data) {
                        return '<span class="fw-bold">#' + (data ?? 'N/A') + '</span>';
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

                            // Construct Full Name safely
                            if (fname || lname) {
                                fullName = (fname + ' ' + lname + ext).trim();
                            } else if (user.name) {
                                fullName = user.name; // Fallback in case a virtual attribute exists
                            }

                            // Extract Initials safely
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
                                    ${initials}
                                </div>
                                <div>
                                    <div class="fw-semibold small">${fullName}</div>
                                    <small class="text-muted">${officeAbbr}</small>
                                </div>
                            </div>`;
                    }
                },
                // 3. Subject
                {
                    data: 'issue_description',
                    render: renderTicketSubject
                },
                // 4. Category
                {
                    data: 'category',
                    render: function(data, type, row) {
                        return row.category ? row.category.ticketcatname : '-';
                    }
                },
                // 5. Sub-Category
                {
                    data: 'subcategory',
                    render: function(data, type, row) {
                        return row.subcategory ? row.subcategory.ticketsubcatname : '-';
                    }
                },
                // 6. Priority Badge
                {
                    data: 'priority',
                    render: function(data) {
                        var badgeClass = 'bg-secondary';
                        if (data === 'High' || data === 'Urgent') badgeClass = 'bg-danger';
                        else if (data === 'Medium') badgeClass = 'bg-warning text-dark';
                        else if (data === 'Low') badgeClass = 'bg-info';

                        return `<span class="badge ${badgeClass} rounded-pill">${data ?? 'Medium'}</span>`;
                    }
                },
                // 7. Status Badge
                {
                    data: 'status',
                    render: function(data) {
                        var statusClass = 'bg-secondary';
                        if (data === 'Pending') statusClass = 'bg-primary';
                        else if (data === 'In Progress') statusClass = 'bg-warning text-dark';
                        else if (data === 'Resolved' || data === 'Completed') statusClass = 'bg-success';
                        else if (data === 'Cancelled') statusClass = 'bg-danger';

                        return `<span class="badge ${statusClass}">${data ?? 'Pending'}</span>`;
                    }
                },
                // 8. Resolved By
                {
                    data: 'resolved_by_name',
                    render: renderResolvedBy
                },
                // 9. Date Created
                {
                    data: 'created_at',
                    render: renderDateCreated
                },
                // 10. Date Resolved
                {
                    data: 'resolved_at',
                    render: renderDateResolved
                },
                // 11. Duration
                {
                    data: 'duration',
                    render: renderDuration
                },
                // 12. Actions (View / Edit / Delete)
                {
                    data: 'id',
                    render: function(data, type, row) {
                        var baseUrl = "{{ route('tickets.store') }}";
                        var viewBtn = `<a href="${baseUrl}?view=${data}" class="btn btn-sm btn-info text-white me-1" data-bs-toggle="tooltip" title="View Ticket"><i class="ti ti-eye"></i></a>`;

                        return viewBtn;
                    }
                }
            ],
            "createdRow": function (row, data, index) {
                $(row).attr('id', 'tr-' + data.id);
            }
        });
        $(document).on('ticketResolvedAdded', function() {
            dataTableResolved.ajax.reload();
        });


        var dataTableClosed = $('#ticketclosedTable').DataTable({
            "ajax": {
                "url": ticketClosedRoute,
                "type": "GET",
            },
            destroy: true,
            info: true,
            responsive: true,
            lengthChange: true,
            searching: true,
            paging: true,
            "columns": [
                // 1. Ticket Number
                {
                    data: 'ticket_number',
                    render: function(data) {
                        return '<span class="fw-bold">#' + (data ?? 'N/A') + '</span>';
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

                            // Construct Full Name safely
                            if (fname || lname) {
                                fullName = (fname + ' ' + lname + ext).trim();
                            } else if (user.name) {
                                fullName = user.name; // Fallback in case a virtual attribute exists
                            }

                            // Extract Initials safely
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
                                    ${initials}
                                </div>
                                <div>
                                    <div class="fw-semibold small">${fullName}</div>
                                    <small class="text-muted">${officeAbbr}</small>
                                </div>
                            </div>`;
                    }
                },
                // 3. Subject
                {
                    data: 'issue_description',
                    render: renderTicketSubject
                },
                // 4. Category
                {
                    data: 'category',
                    render: function(data, type, row) {
                        return row.category ? row.category.ticketcatname : '-';
                    }
                },
                // 5. Sub-Category
                {
                    data: 'subcategory',
                    render: function(data, type, row) {
                        return row.subcategory ? row.subcategory.ticketsubcatname : '-';
                    }
                },
                // 6. Priority Badge
                {
                    data: 'priority',
                    render: function(data) {
                        var badgeClass = 'bg-secondary';
                        if (data === 'High' || data === 'Urgent') badgeClass = 'bg-danger';
                        else if (data === 'Medium') badgeClass = 'bg-warning text-dark';
                        else if (data === 'Low') badgeClass = 'bg-info';

                        return `<span class="badge ${badgeClass} rounded-pill">${data ?? 'Medium'}</span>`;
                    }
                },
                // 7. Status Badge
                {
                    data: 'status',
                    render: function(data) {
                        var statusClass = 'bg-secondary';
                        if (data === 'Pending') statusClass = 'bg-primary';
                        else if (data === 'In Progress') statusClass = 'bg-warning text-dark';
                        else if (data === 'Resolved' || data === 'Completed') statusClass = 'bg-success';
                        else if (data === 'Cancelled') statusClass = 'bg-danger';

                        return `<span class="badge ${statusClass}">${data ?? 'Pending'}</span>`;
                    }
                },
                // 8. Actions (View / Edit / Delete)
                {
                    data: 'id',
                    render: function(data, type, row) {
                        var baseUrl = "{{ route('tickets.store') }}";
                        var viewBtn = `<a href="${baseUrl}?view=${data}" class="btn btn-sm btn-info text-white me-1" data-bs-toggle="tooltip" title="View Ticket"><i class="ti ti-eye"></i></a>`;

                        return viewBtn;
                    }
                }
            ],
            "createdRow": function (row, data, index) {
                $(row).attr('id', 'tr-' + data.id);
            }
        });
        $(document).on('ticketClosedAdded', function() {
            dataTableClosed.ajax.reload();
        });

        $(document).on('ticketListChanged', function() {
            dataTablePending.ajax.reload(null, false);
            dataTableProgress.ajax.reload(null, false);
            dataTableResolved.ajax.reload(null, false);
            dataTableClosed.ajax.reload(null, false);
        });

        // Realtime: any ticket DB change (created / status) refreshes all tables
        if (typeof Echo !== 'undefined') {
            Echo.channel('tickets')
                .listen('TicketListUpdated', function() {
                    $(document).trigger('ticketListChanged');
                });
        }
    });
</script>
