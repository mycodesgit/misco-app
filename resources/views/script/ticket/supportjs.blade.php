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
                // 3. Category
                {
                    data: 'category',
                    render: function(data, type, row) {
                        return row.category ? row.category.ticketcatname : '-';
                    }
                },
                // 4. Sub-Category
                {
                    data: 'subcategory',
                    render: function(data, type, row) {
                        return row.subcategory ? row.subcategory.ticketsubcatname : '-';
                    }
                },
                // 5. Priority Badge
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
                // 6. Status Badge
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
                // 7. Actions (View / Edit / Delete)
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
                // 3. Category
                {
                    data: 'category',
                    render: function(data, type, row) {
                        return row.category ? row.category.ticketcatname : '-';
                    }
                },
                // 4. Sub-Category
                {
                    data: 'subcategory',
                    render: function(data, type, row) {
                        return row.subcategory ? row.subcategory.ticketsubcatname : '-';
                    }
                },
                // 5. Priority Badge
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
                // 6. Status Badge
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
                // 7. Actions (View / Edit / Delete)
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
                // 3. Category
                {
                    data: 'category',
                    render: function(data, type, row) {
                        return row.category ? row.category.ticketcatname : '-';
                    }
                },
                // 4. Sub-Category
                {
                    data: 'subcategory',
                    render: function(data, type, row) {
                        return row.subcategory ? row.subcategory.ticketsubcatname : '-';
                    }
                },
                // 5. Priority Badge
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
                // 6. Status Badge
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
                // 7. Actions (View / Edit / Delete)
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
                // 3. Category
                {
                    data: 'category',
                    render: function(data, type, row) {
                        return row.category ? row.category.ticketcatname : '-';
                    }
                },
                // 4. Sub-Category
                {
                    data: 'subcategory',
                    render: function(data, type, row) {
                        return row.subcategory ? row.subcategory.ticketsubcatname : '-';
                    }
                },
                // 5. Priority Badge
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
                // 6. Status Badge
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
                // 7. Actions (View / Edit / Delete)
                {
                    data: 'id',
                    render: function(data, type, row) {
                        var baseUrl = "{{ url('/tickets') }}";
                        var viewBtn = `<a href="${baseUrl}/${data}" class="btn btn-sm btn-info text-white me-1" data-bs-toggle="tooltip" title="View Ticket"><i class="ti ti-eye"></i></a>`;

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
    });
</script>
