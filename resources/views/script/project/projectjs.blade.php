<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right"
    };

    var projectStatusBadge = {
        'Planning': 'bg-secondary',
        'In Progress': 'bg-info',
        'On Hold': 'bg-warning text-dark',
        'Completed': 'bg-success',
        'Cancelled': 'bg-danger'
    };

    var avatarPalette = ['#0d6efd', '#198754', '#6f42c1', '#fd7e14', '#20c997', '#d63384', '#0dcaf0', '#ffc107'];

    function memberInitials(name) {
        if (!name) return 'U';
        var parts = name.trim().split(/\s+/);
        if (parts.length >= 2) {
            return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
        }
        return name.substring(0, 2).toUpperCase();
    }

    function renderMemberAvatars(members) {
        members = members || [];
        if (members.length === 0) {
            return '<span class="text-muted">-</span>';
        }
        var shown = members.slice(0, 5);
        var html = '<span class="avatar-stack">';
        shown.forEach(function(m, i) {
            var color = avatarPalette[i % avatarPalette.length];
            var safeName = $('<div>').text(m.name).html();
            html += '<span class="member-avatar" style="background:' + color + ';" data-bs-toggle="tooltip" data-bs-placement="top" title="' + safeName + '">' + memberInitials(m.name) + '</span>';
        });
        if (members.length > 5) {
            html += '<span class="member-avatar member-more" title="' + members.length + ' members">+' + (members.length - 5) + '</span>';
        }
        html += '</span>';
        return html;
    }

    function initMemberTooltips() {
        if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;
        $('#projectTable [data-bs-toggle="tooltip"]').each(function() {
            var instance = bootstrap.Tooltip.getInstance(this);
            if (instance) instance.dispose();
            new bootstrap.Tooltip(this, { placement: 'top', container: 'body' });
        });
    }

    function loadOfficeMembers(officeId, $select, selectedIds) {
        selectedIds = selectedIds || [];
        $select.empty().prop('disabled', true);
        if (!officeId) {
            $select.append('<option disabled>Select an office first</option>');
            return;
        }
        $select.append('<option disabled>Loading...</option>');
        $.ajax({
            url: projectMembersBase + '/' + officeId,
            type: 'GET',
            success: function(users) {
                $select.empty();
                if (!users || users.length === 0) {
                    $select.append('<option disabled>No users in this office</option>');
                    return;
                }
                users.forEach(function(u) {
                    var name = ((u.fname || '') + ' ' + (u.lname || '')).trim() || ('User #' + u.id);
                    var sel = selectedIds.map(String).includes(String(u.id)) ? ' selected' : '';
                    $select.append('<option value="' + u.id + '"' + sel + '>' + name + '</option>');
                });
                $select.prop('disabled', false);
            },
            error: function() {
                $select.empty().append('<option disabled>Error loading members</option>');
            }
        });
    }

    function renderProjectGantt(projects) {
        var $gantt = $('#projectGantt');
        if (!projects || projects.length === 0) {
            $gantt.html('<div class="text-center text-muted small py-3">No projects to display.</div>');
            return;
        }

        var minStart = null, maxEnd = null;
        projects.forEach(function(p) {
            var s = new Date(p.start_date + 'T00:00:00');
            var e = new Date(p.end_date + 'T00:00:00');
            if (!minStart || s < minStart) minStart = s;
            if (!maxEnd || e > maxEnd) maxEnd = e;
        });
        minStart = new Date(minStart.getFullYear(), minStart.getMonth(), 1);
        maxEnd = new Date(maxEnd.getFullYear(), maxEnd.getMonth() + 1, 0);

        var dayMs = 86400000;
        var totalDays = Math.round((maxEnd - minStart) / dayMs) + 1;
        var pxPerDay = Math.max(4, Math.min(14, Math.floor(900 / totalDays)));
        var trackWidth = totalDays * pxPerDay;

        var monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var monthsHtml = '<div class="gantt-row"><div class="gantt-label"></div><div class="gantt-track" style="width:' + trackWidth + 'px;flex-grow:0;"><div class="gantt-months" style="width:' + trackWidth + 'px;">';
        var cursor = new Date(minStart);
        while (cursor <= maxEnd) {
            var mStart = new Date(cursor.getFullYear(), cursor.getMonth(), 1);
            var mEnd = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 0);
            if (mEnd > maxEnd) mEnd = maxEnd;
            var days = Math.round((mEnd - (mStart < minStart ? minStart : mStart)) / dayMs) + 1;
            monthsHtml += '<div class="gantt-month" style="width:' + (days * pxPerDay) + 'px;">' + monthNames[cursor.getMonth()] + ' ' + cursor.getFullYear() + '</div>';
            cursor = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 1);
        }
        monthsHtml += '</div></div></div>';

        var today = new Date(); today.setHours(0, 0, 0, 0);
        var todayOffset = Math.round((today - minStart) / dayMs);
        var showToday = todayOffset >= 0 && todayOffset <= totalDays;

        var rowsHtml = '';
        projects.forEach(function(p) {
            var s = new Date(p.start_date + 'T00:00:00');
            var e = new Date(p.end_date + 'T00:00:00');
            var offset = Math.round((s - minStart) / dayMs) * pxPerDay;
            var width = (Math.round((e - s) / dayMs) + 1) * pxPerDay;
            rowsHtml += '<div class="gantt-row"><div class="gantt-label" title="' + p.name.replace(/"/g, '&quot;') + '">' + p.name + '</div>' +
                '<div class="gantt-track" style="width:' + trackWidth + 'px;flex-grow:0;">' +
                (showToday ? '<div class="gantt-today" style="left:' + (todayOffset * pxPerDay) + 'px;" title="Today"></div>' : '') +
                '<div class="gantt-bar" style="left:' + offset + 'px;width:' + width + 'px;" title="' + p.start_label + ' - ' + p.end_label + ' (' + p.progress + '%)">' +
                '<div class="gantt-fill" style="width:' + p.progress + '%;"></div></div>' +
                '</div></div>';
        });

        $gantt.html('<div class="gantt-chart">' + monthsHtml + rowsHtml + '</div>');
    }

    $(document).ready(function() {
        // Team picker is fixed to my office — preload once into both selects
        loadOfficeMembers(myOfficeId, $('#createMembers'));
        loadOfficeMembers(myOfficeId, $('#editProjectMembers'));

        var dataTable = $('#projectTable').DataTable({
            "ajax": {
                "url": projectReadRoute,
                "type": "GET",
                "data": function(d) {
                    d.status = $('#filterStatus').val();
                },
                "dataSrc": function(res) {
                    renderProjectGantt(res.data || []);
                    return res.data || [];
                }
            },
            destroy: true,
            info: true,
            responsive: true,
            lengthChange: true,
            searching: true,
            paging: true,
            order: [[3, "asc"]],
            "columns": [
                {
                    data: 'name',
                    render: function(data) {
                        return '<span class="fw-bold">' + $('<div>').text(data).html() + '</span>';
                    }
                },
                { data: 'office' },
                {
                    data: 'members',
                    orderable: false,
                    render: function(data) {
                        return renderMemberAvatars(data);
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return '<span class="small text-nowrap">' + row.start_label + ' - ' + row.end_label + '</span>';
                    }
                },
                {
                    data: 'duration',
                    render: function(data) {
                        return '<span class="badge bg-light text-dark border">' + data + ' day(s)</span>';
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        var cls = row.remaining < 0 ? 'bg-danger' : (row.remaining === 0 ? 'bg-warning text-dark' : 'bg-info');
                        return '<span class="badge ' + cls + '">' + row.remaining_label + '</span>';
                    }
                },
                {
                    data: 'progress',
                    render: function(data) {
                        return '<div class="d-flex align-items-center gap-1"><div class="progress flex-grow-1" style="height:8px;min-width:60px;"><div class="progress-bar bg-success" style="width:' + data + '%;"></div></div><small class="fw-bold">' + data + '%</small></div>';
                    }
                },
                {
                    data: 'remarks',
                    render: function(data) {
                        return data ? '<span class="small">' + $('<div>').text(data).html() + '</span>' : '<span class="text-muted">-</span>';
                    }
                },
                {
                    data: 'status',
                    render: function(data) {
                        return '<span class="badge ' + (projectStatusBadge[data] || 'bg-secondary') + '">' + data + '</span>';
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    render: function(data, type, row) {
                        if (!row.can_manage) {
                            return '<span class="text-muted" data-bs-toggle="tooltip" title="Only team members can manage this project"><i class="ti ti-lock"></i></span>';
                        }
                        return '<button type="button" class="btn btn-sm btn-success text-white btn-project-edit me-1" data-row=\'' + JSON.stringify(row).replace(/'/g, "&#39;") + '\' title="Edit"><i class="ti ti-pencil"></i></button>' +
                            '<button type="button" class="btn btn-sm btn-danger btn-project-delete" data-id="' + data + '" data-name="' + $('<div>').text(row.name).html() + '" title="Delete"><i class="ti ti-trash"></i></button>';
                    }
                }
            ]
        });

        dataTable.on('draw', function() {
            initMemberTooltips();
        });

        $('#projectFilterForm').submit(function(event) {
            event.preventDefault();
            dataTable.ajax.reload();
        });

        $('#createProgress').on('input', function() {
            $('#createProgressLabel').text($(this).val() + '%');
        });
        $('#editProjectProgress').on('input', function() {
            $('#editProgressLabel').text($(this).val() + '%');
        });

        $('#createProjectForm').submit(function(event) {
            event.preventDefault();
            $.ajax({
                url: projectCreateRoute,
                type: 'POST',
                data: $(this).serialize(),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#createProjectModal').modal('hide');
                        $('#createProjectForm')[0].reset();
                        $('#createMembers').val(null).trigger('change');
                        $('#createProgressLabel').text('0%');
                        dataTable.ajax.reload();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred');
                }
            });
        });

        $(document).on('click', '.btn-project-edit', function() {
            var row = JSON.parse($(this).attr('data-row').replace(/&#39;/g, "'"));
            $('#editProjectId').val(row.id);
            $('#editProjectName').val(row.name);
            $('#editProjectStatus').val(row.status);
            $('#editProjectStart').val(row.start_date);
            $('#editProjectEnd').val(row.end_date);
            $('#editProjectProgress').val(row.progress);
            $('#editProgressLabel').text(row.progress + '%');
            $('#editProjectRemarks').val(row.remarks);
            $('#editProjectMembers').val((row.member_ids || []).map(String)).trigger('change');
            $('#editProjectModal').modal('show');
        });

        $('#editProjectForm').submit(function(event) {
            event.preventDefault();
            $.ajax({
                url: projectUpdateRoute,
                type: 'POST',
                data: $(this).serialize(),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#editProjectModal').modal('hide');
                        dataTable.ajax.reload();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred');
                }
            });
        });

        $(document).on('click', '.btn-project-delete', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            Swal.fire({
                title: 'Delete project?',
                text: ' "' + name + '" will be permanently removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, delete it!'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: projectDeleteBase + '/' + id,
                        type: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                dataTable.ajax.reload();
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function() {
                            toastr.error('Failed to delete project.');
                        }
                    });
                }
            });
        });
    });
</script>
