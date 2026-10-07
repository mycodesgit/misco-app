<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right"
    };
    $(document).ready(function() {
        $('#addNewTicket').submit(function(event) {
            event.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: ticketCreateRoute,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if(response.success) {
                        toastr.success(response.message);
                        console.log(response);
                        $(document).trigger('ticketAdded');
                        $('#createNewTicketModal').modal('hide');
                        $('#addNewTicket')[0].reset();
                        // Clear image preview if you have one
                        $('#preview-container').addClass('d-none');
                        $('#image-preview').attr('src', '#');
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
                // 7. Actions (View / Feedback)
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

                        return `<div class="text-nowrap">` + viewBtn + feedbackBtn + `</div>`;
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

    /* ================= Feedback modal (ClientSatisfactory) ================= */
    var feedbackLabels = {
        1: 'Very Dissatisfied',
        2: 'Dissatisfied',
        3: 'Neutral',
        4: 'Satisfied',
        5: 'Very Satisfied'
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
</script>
