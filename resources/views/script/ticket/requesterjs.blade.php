<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right"
    };
    /* ============ English-only validation (Issue Description & Remarks) ============ */
    // Allows only printable English/ASCII characters: letters, numbers, spaces,
    // newline/tab, and basic punctuation. Rejects non-English scripts & symbols.
    var englishOnlyRegex = /^[\x09\x0A\x0D\x20-\x7E‘’“”–—…]*$/;
    var englishOnlyMessage = 'Only English characters, numbers, and basic punctuation are allowed.';

    function validateEnglishField($field) {
        var isValid = englishOnlyRegex.test(String($field.val() || ''));
        $field.toggleClass('is-invalid', !isValid);
        return isValid;
    }

    /**
     * Apply a 422 JSON error response from the server to the form fields.
     * Highlights each offending field and shows its message in the
     * .invalid-feedback block beneath it.
     */
    function applyServerValidationErrors(xhr) {
        var handled = false;
        var response = null;
        try { response = JSON.parse(xhr.responseText); } catch (e) { response = null; }

        if (response && response.errors) {
            $.each(response.errors, function(field, messages) {
                var $field = $('[name="' + field + '"]');
                if ($field.length) {
                    $field.addClass('is-invalid');
                    $field.closest('.col-md-12, .col-md-6, .mb-3')
                        .find('.invalid-feedback').first().text(messages[0]);
                    handled = true;
                }
            });
            if (handled && response.message) {
                toastr.error(response.message);
            }
        }
        return handled;
    }

    $(document).ready(function() {
        // Live feedback while typing
        $(document).on('input', '#issue_description, #remarks, #edit_issue_description, #edit_remarks', function() {
            validateEnglishField($(this));
        });

        $('#addNewTicket').submit(function(event) {
            event.preventDefault();

            var $issueDesc = $('#issue_description');
            var $remarks = $('#remarks');
            var $invalidField = null;

            if (!validateEnglishField($issueDesc)) {
                $invalidField = $issueDesc;
            } else if (!validateEnglishField($remarks)) {
                $invalidField = $remarks;
            }

            if ($invalidField) {
                toastr.error(englishOnlyMessage);
                $invalidField.trigger('focus');
                return false;
            }

            var formData = new FormData(this);

            // Lock the button right away so the ticket can't be submitted twice
            var $btn = $(this).find('button[type="submit"]');
            if ($btn.prop('disabled')) return false;
            var btnHtml = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Submitting...');

            $.ajax({
                url: ticketCreateRoute,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                complete: function() {
                    $btn.prop('disabled', false).html(btnHtml);
                },
                success: function(response) {
                    if(response.success) {
                        toastr.success(response.message);
                        console.log(response);
                        $(document).trigger('ticketAdded');
                        $('#createNewTicketModal').modal('hide');
                        $('#addNewTicket')[0].reset();
                        $('#issue_description, #remarks').removeClass('is-invalid');
                        // Clear image preview if you have one
                        $('#preview-container').addClass('d-none');
                        $('#image-preview').attr('src', '#');
                    } else {
                        toastr.error(response.message);
                        console.log(response);
                    }
                },
                error: function(xhr, status, error, message) {
                    if (applyServerValidationErrors(xhr)) {
                        return;
                    }
                    var errorMessage = 'An error occurred';
                    try { errorMessage = JSON.parse(xhr.responseText).message || errorMessage; } catch (e) {}
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
                        var editBtn = `<button type="button" class="btn btn-sm btn-success text-white btn-ticketedit me-1" data-id="${data}" data-bs-toggle="tooltip" title="Edit Ticket"><i class="ti ti-pencil"></i></button>`;
                        var deleteBtn = `<button type="button" value="${data}" class="btn btn-sm btn-danger ticket-delete" data-bs-toggle="tooltip" title="Delete Ticket"><i class="ti ti-trash"></i></button>`;

                        return viewBtn + editBtn + deleteBtn;
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
            $('[data-bs-toggle="tooltip"]').tooltip();
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
        dataTableProgress.on('draw', function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
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
                // 12. Actions (View / Feedback / Reopen)
                {
                    data: 'id',
                    render: function(data, type, row) {
                        var baseUrl = "{{ route('tickets.store') }}";
                        var viewBtn = `<a href="${baseUrl}?view=${data}" class="btn btn-sm btn-info text-white me-1" data-bs-toggle="tooltip" title="View Ticket"><i class="ti ti-eye"></i></a>`;
                        var feedbackBtn;
                        if (row.has_feedback) {
                            feedbackBtn = `<button type="button" class="btn btn-sm btn-secondary text-white me-1" disabled data-bs-toggle="tooltip" title="Feedback already submitted"><i class="ti ti-forms me-1"></i>Feedback Submitted</button>`;
                        } else {
                            feedbackBtn = `<button type="button" class="btn btn-sm btn-success text-white btn-submitfeedback me-1 text-nowrap" data-id="${data}" data-bs-toggle="tooltip" title="Submit Feedback"><i class="ti ti-forms me-1"></i>Submit Feedback</button>`;
                        }

                        // Reopen: the requester can send a resolved ticket back to
                        // Pending while the 1-week reopen window is still open.
                        var reopenBtn;
                        if (row.can_reopen) {
                            reopenBtn = `<button type="button" class="btn btn-sm btn-warning text-white btn-reopenticket me-1 text-nowrap" data-id="${data}" data-bs-toggle="tooltip" title="Reopen this ticket (not satisfied with the resolution)"><i class="ti ti-refresh me-1"></i>Reopen</button>`;
                        } else {
                            reopenBtn = `<span data-bs-toggle="tooltip" data-bs-placement="top" title="The 1-week reopen window for this ticket has lapsed"><button type="button" class="btn btn-sm btn-secondary text-white me-1 text-nowrap" disabled><i class="ti ti-lock me-1"></i>Reopen</button></span>`;
                        }

                        return `<div class="text-nowrap">` + viewBtn + reopenBtn + feedbackBtn + `</div>`;
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
        dataTableResolved.on('draw', function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
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



    $(document).ready(function () {
        // Type of Support changed
        $('#support_type').on('change', function () {
            let supportType = $(this).val();

            let category = $('#category');
            let subcategory = $('#subcategory');

            // Get the off_id of the selected role
            let offId = $(this).find(':selected').data('off-id');

            // Store off_id in hidden input
            $('#support_type_hidden').val(offId);
            // Reset category
            category.empty().append('<option value="" selected disabled>Loading categories...</option>').prop('disabled', true);
            // Reset subcategory
            subcategory.empty().append('<option value="" selected disabled>Select Subcategory</option>').prop('disabled', true);
            // Destroy Select2 temporarily
            category.trigger('change');
            subcategory.trigger('change');

            if (!supportType) {
                return;
            }

            let url = "{{ route('tickets.categories', ['supportType' => '__supportType__']) }}";
            url = url.replace('__supportType__',encodeURIComponent(supportType));
            $.ajax({
                url: url,
                type: 'GET',
                success: function (data) {
                    category.empty().append('<option value="" selected disabled>Select Category</option>');
                    if (data.length > 0) {
                        $.each(data, function (index, item) {
                            category.append(
                                $('<option>', {
                                    value: item.id,
                                    text: item.ticketcatname
                                })
                            );
                        });
                        category.prop('disabled', false);
                    } else {
                        category.append(
                            '<option value="" disabled>No categories available</option>'
                        );
                    }
                    category.trigger('change');
                },
                error: function (xhr) {
                    console.error(xhr);
                    category.empty()
                        .append('<option value="" selected disabled>Error loading categories</option>')
                        .prop('disabled', true);

                    category.trigger('change');
                }
            });
        });


        // Category changed
        $('#category').on('change', function () {
            let categoryId = $(this).val();
            let subcategory = $('#subcategory');
            let assignedDisplay = $('#assigned_to_display');
            let assignedInput = $('#assigned_to');

            subcategory.empty().append('<option value="" selected disabled>Loading subcategories...</option>').prop('disabled', true);
            subcategory.trigger('change');

            assignedDisplay.val('Loading assigned personnel...');
            assignedInput.val('');

            if (!categoryId) {
                return;
            }

            let url = "{{ route('tickets.subcategories', ['category' => '__category__']) }}";
            url = url.replace('__category__', categoryId );
            $.ajax({
                url: url,
                type: 'GET',
                success: function (data) {
                    subcategory.empty().append('<option value="" selected disabled>Select Subcategory</option>');
                    if (data.length > 0) {
                        $.each(data, function (index, item) {
                            subcategory.append(
                                $('<option>', {
                                    value: item.id,
                                    text: item.ticketsubcatname
                                })
                            );
                        });
                        subcategory.prop('disabled', false);
                    } else {
                        subcategory.append(
                            '<option value="" disabled>No subcategories available</option>'
                        );
                    }
                    subcategory.trigger('change');
                },
                error: function (xhr) {
                    console.error(xhr);
                    subcategory.empty()
                        .append('<option value="" selected disabled>Error loading subcategories</option>')
                        .prop('disabled', true);
                    subcategory.trigger('change');
                }
            });

            let personnelUrl = "{{ route('tickets.assignedPersonnel', ['category' => '__category__']) }}";
            personnelUrl = personnelUrl.replace('__category__',encodeURIComponent(categoryId));
            $.ajax({
                url: personnelUrl,
                type: 'GET',
                success: function (users) {
                    if (users.length === 0) {
                        assignedDisplay.val('No personnel assigned');
                        assignedInput.val('');
                        return;
                    }

                    // Get names
                    let names = users.map(function (user) {
                        return user.fname + ' ' + user.lname;
                    });
                    // Get IDs
                    let userIds = users.map(function (user) {
                        return user.id;
                    });
                    // Show names
                    assignedDisplay.val(names.join(', '));
                    // Store IDs
                    assignedInput.val(userIds.join(','));
                },
                error: function (xhr) {
                    console.error(xhr);
                    assignedDisplay.val('Error loading assigned personnel');
                    assignedInput.val('');
                }
            });
        });
    });

    /* ================= Edit ticket modal ================= */
    // Open edit modal and pre-fill fields from the server
    $(document).on('click', '.btn-ticketedit', function() {
        var ticketId = $(this).data('id');

        $('#editTicketForm')[0].reset();
        $('#edit_issue_description, #edit_remarks').removeClass('is-invalid');
        $('#edit_ticket_id').val('');
        $('#edit_category, #edit_subcategory').val('');

        $.ajax({
            url: ticketEditBase + '/' + ticketId,
            type: 'GET',
            success: function(res) {
                if (!res.success) {
                    toastr.error(res.message || 'Unable to load ticket.');
                    return;
                }

                var t = res.data;
                $('#edit_ticket_id').val(t.id);
                $('#editTicketNumber').text('#' + (t.ticket_number || ''));
                $('#edit_category').val(t.category);
                $('#edit_subcategory').val(t.subcategory);
                $('#edit_priority').val(t.priority);
                $('#edit_contactno').val(t.contactno);
                $('#edit_issue_description').val(t.issue_description);
                $('#edit_remarks').val(t.remarks);

                $('#editTicketModal').modal('show');
            },
            error: function(xhr) {
                var msg = 'Unable to load ticket.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch (e) {}
                toastr.error(msg);
            }
        });
    });

    // Submit edited ticket
    $('#editTicketForm').submit(function(event) {
        event.preventDefault();

        var $issueDesc = $('#edit_issue_description');
        var $remarks = $('#edit_remarks');
        var $invalidField = null;

        if (!validateEnglishField($issueDesc)) {
            $invalidField = $issueDesc;
        } else if (!validateEnglishField($remarks)) {
            $invalidField = $remarks;
        }

        if ($invalidField) {
            toastr.error(englishOnlyMessage);
            $invalidField.trigger('focus');
            return false;
        }

        var $btn = $(this).find('button[type="submit"]').prop('disabled', true);

        $.ajax({
            url: ticketUpdateRoute,
            type: 'POST',
            data: $(this).serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#editTicketModal').modal('hide');
                    $(document).trigger('ticketListChanged');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                if (applyServerValidationErrors(xhr)) {
                    return;
                }
                var msg = 'An error occurred';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch (e) {}
                toastr.error(msg);
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });

    /* ================= Feedback modal (ClientSatisfactory) ================= */
    var feedbackLabels = {
        1: 'Very Poor',
        2: 'Poor',
        3: 'Average',
        4: 'Good',
        5: 'Excellent'
    };

    function setFeedbackRating(value) {
        $('#feedback_rating').val(value || '');
        $('#emojiRatingGroup .emoji-btn').removeClass('active');
        if (value) {
            $('#emojiRatingGroup .emoji-btn[data-value="' + value + '"]').addClass('active');
            $('#emojiRatingLabel').text(feedbackLabels[value]).css('color', '#111');
        } else {
            $('#emojiRatingLabel').text('Choose your experience').css('color', '#e5b8c4');
        }
    }

    $(document).on('click', '#emojiRatingGroup .emoji-btn', function() {
        setFeedbackRating($(this).data('value'));
    });

    // Open modal from Resolved table feedback button
    $(document).on('click', '.btn-submitfeedback', function() {
        var ticketId = $(this).data('id');
        $('#submitFeedbackForm')[0].reset();
        setFeedbackRating('');
        $('#feedback_ticket_id').val(ticketId);
        $('#feedbackTicketNo').text('Ticket #');

        $.ajax({
            url: ticketFeedbackShowBase + '/' + ticketId,
            type: 'GET',
            success: function(res) {
                $('#feedbackTicketNo').text('Ticket #' + (res.ticket_number || ticketId));
                if (res.rating) setFeedbackRating(res.rating);
                if (res.feedback) $('#feedback_text').val(res.feedback);
            }
        });

        $('#submitfeedTicketModal').modal('show');
    });

    $('#submitFeedbackForm').submit(function(event) {
        event.preventDefault();

        if (!$('#feedback_rating').val()) {
            toastr.warning('Please choose your experience rating.');
            return;
        }

        $.ajax({
            url: ticketFeedbackSubmitRoute,
            type: 'POST',
            data: $(this).serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#submitfeedTicketModal').modal('hide');
                    // Refresh resolved table so the button flips to disabled "Feedback Submitted"
                    $(document).trigger('ticketResolvedAdded');
                    $(document).trigger('ticketListChanged');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                var msg = 'An error occurred';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch (e) {}
                toastr.error(msg);
            }
        });
    });

    /* ================= Reopen ticket (Resolved -> Pending) ================= */
    // The requester can send a resolved ticket back to Pending when they are
    // not satisfied with the resolution. Same ticket number, status only.
    $(document).on('click', '.btn-reopenticket', function() {
        var ticketId = $(this).data('id');
        var $btn = $(this);

        Swal.fire({
            title: 'Reopen this ticket?',
            text: 'The ticket will go back to Pending so the support team can work on it again. The ticket number stays the same.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, reopen it!',
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6c757d'
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }

            $btn.prop('disabled', true);

            $.ajax({
                url: ticketReopenRoute,
                type: 'POST',
                data: { ticket_id: ticketId },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    if (response.success) {
                        toastr.success(response.message);
                        // The ticket moved back to Pending — refresh every list
                        $(document).trigger('ticketListChanged');
                        $(document).trigger('ticketResolvedAdded');
                        $(document).trigger('ticketAdded');
                    } else {
                        toastr.error(response.message);
                        $btn.prop('disabled', false);
                    }
                },
                error: function (xhr) {
                    var msg = 'An error occurred';
                    try { msg = JSON.parse(xhr.responseText).message || msg; } catch (e) {}
                    toastr.error(msg);
                    $btn.prop('disabled', false);
                }
            });
        });
    });
</script>
