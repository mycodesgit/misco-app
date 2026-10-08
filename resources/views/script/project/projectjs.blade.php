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
                        return '<button type="button" class="btn btn-sm btn-info text-white btn-project-kanban me-1" data-id="' + data + '" data-name="' + $('<div>').text(row.name).html() + '" title="Kanban Board"><i class="ti ti-artboard"></i></button>' +
                            '<button type="button" class="btn btn-sm btn-success text-white btn-project-edit me-1" data-row=\'' + JSON.stringify(row).replace(/'/g, "&#39;") + '\' title="Edit"><i class="ti ti-pencil"></i></button>' +
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
        /* ================= Kanban Board ================= */
        var kanbanProjectId = null;
        var kanbanProjectMembers = [];
        var kanbanStatuses = ['todo', 'in_progress', 'on_hold', 'done'];

        function kanbanCsrf() {
            return $('meta[name="csrf-token"]').attr('content');
        }

        function kanbanAssigneeHtml(task) {
            if (!task.assigned_to) {
                return '<span class="text-muted" style="font-size:0.72rem;">Unassigned</span>';
            }
            var name = task.assignee_name || ('User #' + task.assigned_to);
            var safeName = $('<div>').text(name).html();
            return '<span class="kanban-assignee"><span class="member-avatar avatar-stack" style="background:#0d6efd;display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;color:#fff;font-weight:700;" title="' + safeName + '">' + memberInitials(name) + '</span>' +
                '<small class="fw-semibold text-truncate" style="max-width:110px;" title="' + safeName + '">' + safeName + '</small></span>';
        }

        function kanbanCardHtml(task) {
            var safeTitle = $('<div>').text(task.title).html();
            var desc = task.description
                ? '<div class="kanban-desc small text-muted mt-1">' + $('<div>').text(task.description).html() + '</div>'
                : '';
            // Only the member who added the card may drag / edit / delete it
            var isMine = parseInt(task.created_by, 10) === parseInt(kanbanAuthUserId, 10);
            var controls = isMine
                ? '<button type="button" class="btn btn-sm btn-link text-secondary p-0 kanban-edit" data-id="' + task.id + '" title="Edit"><i class="ti ti-pencil"></i></button>' +
                  '<button type="button" class="btn btn-sm btn-link text-danger p-0 kanban-delete" data-id="' + task.id + '" title="Delete"><i class="ti ti-trash"></i></button>'
                : '<span class="text-muted" title="Only the member who added this task can edit it"><i class="ti ti-lock"></i></span>';
            return '<div class="kanban-card' + (task.status === 'done' ? ' done-card' : '') + '" draggable="' + (isMine ? 'true' : 'false') + '" data-id="' + task.id + '" data-mine="' + (isMine ? '1' : '0') + '">' +
                '<div class="d-flex justify-content-between align-items-start gap-2">' +
                    '<span class="kanban-title small" title="' + safeTitle + '">' + safeTitle + '</span>' +
                    '<span class="d-flex gap-1 flex-shrink-0">' + controls + '</span>' +
                '</div>' + desc +
                '<div class="d-flex justify-content-between align-items-center mt-2">' +
                    kanbanAssigneeHtml(task) +
                    '<small class="text-muted" style="font-size:0.68rem;">' + (task.updated_label || '') + '</small>' +
                '</div>' +
            '</div>';
        }

        function renderKanbanBoard(tasks) {
            tasks = tasks || [];
            var grouped = { todo: [], in_progress: [], on_hold: [], done: [] };
            tasks.forEach(function (t) {
                if (grouped[t.status]) grouped[t.status].push(t);
                else grouped.todo.push(t);
            });
            kanbanStatuses.forEach(function (status) {
                var $list = $('.kanban-list[data-status="' + status + '"]');
                if (grouped[status].length === 0) {
                    $list.html('<div class="kanban-empty">Drop tasks here</div>');
                } else {
                    $list.html(grouped[status].map(kanbanCardHtml).join(''));
                }
                $('.kanban-count[data-count="' + status + '"]').text(grouped[status].length);
            });
        }

        function loadKanbanBoard() {
            if (!kanbanProjectId) return;
            $.ajax({
                url: kanbanBoardBase + '/' + kanbanProjectId,
                type: 'GET',
                success: function (res) {
                    kanbanProjectMembers = (res.project && res.project.members) || [];
                    renderKanbanBoard(res.tasks || []);
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to load board.');
                }
            });
        }

        function collectBoardOrder() {
            var order = { todo: [], in_progress: [], on_hold: [], done: [] };
            kanbanStatuses.forEach(function (status) {
                $('.kanban-list[data-status="' + status + '"] .kanban-card').each(function () {
                    order[status].push(parseInt($(this).data('id'), 10));
                });
            });
            return order;
        }

        function saveBoardOrder() {
            $.ajax({
                url: kanbanReorderRoute,
                type: 'POST',
                data: { project_id: kanbanProjectId, order: collectBoardOrder() },
                headers: { 'X-CSRF-TOKEN': kanbanCsrf() },
                error: function () {
                    toastr.error('Could not save board order — reloading.');
                    loadKanbanBoard();
                }
            });
        }

        // Open board from table row button
        $(document).on('click', '.btn-project-kanban', function () {
            kanbanProjectId = $(this).data('id');
            var name = $(this).data('name') || 'Project Board';
            $('#kanbanProjectName').text(name);
            $('#kanbanProjectMeta').text('Team board — everything members work on, for the whole project duration.');
            renderKanbanBoard([]);
            $('#kanbanModal').modal('show');
            loadKanbanBoard();
        });

        // Native drag & drop between columns
        var draggedCardId = null;
        $(document).on('dragstart', '.kanban-card', function (e) {
            draggedCardId = $(this).data('id');
            e.originalEvent.dataTransfer.effectAllowed = 'move';
            try { e.originalEvent.dataTransfer.setData('text/plain', String(draggedCardId)); } catch (err) {}
            $(this).addClass('dragging');
        });
        $(document).on('dragend', '.kanban-card', function () {
            $('.kanban-card').removeClass('dragging');
            $('.kanban-list').removeClass('drag-over');
        });
        $(document).on('dragover', '.kanban-list', function (e) {
            e.preventDefault();
            e.originalEvent.dataTransfer.dropEffect = 'move';
            $(this).addClass('drag-over');
        });
        $(document).on('dragleave', '.kanban-list', function () {
            $(this).removeClass('drag-over');
        });
        $(document).on('drop', '.kanban-list', function (e) {
            e.preventDefault();
            var $list = $(this);
            $list.removeClass('drag-over');
            var $card = $('.kanban-card[data-id="' + draggedCardId + '"]');
            if ($card.length === 0) return;
            if ($card.data('mine') != 1) {
                toastr.warning('Only the member who added this task can move it.');
                loadKanbanBoard();
                return;
            }

            // Insert before the card under the cursor, else append at end
            var $after = null;
            $list.find('.kanban-card').not($card).each(function () {
                var rect = this.getBoundingClientRect();
                if (e.originalEvent.clientY < rect.top + rect.height / 2 && !$after) {
                    $after = $(this);
                }
            });
            if ($after) { $card.insertBefore($after); } else { $list.append($card); }
            $list.find('.kanban-empty').remove();

            var newStatus = $list.data('status');
            $card.toggleClass('done-card', newStatus === 'done');
            renderKanbanCountsOnly();
            saveBoardOrder();
        });

        function renderKanbanCountsOnly() {
            kanbanStatuses.forEach(function (status) {
                $('.kanban-count[data-count="' + status + '"]').text($('.kanban-list[data-status="' + status + '"] .kanban-card').length);
            });
        }

        // Assignee picker shows only Unassigned + the logged-in user.
        // In edit mode the task's current assignee is kept as an extra
        // option so saving never silently reassigns someone else's pick.
        function fillAssigneeSelect(selectedId, keepAssignee) {
            var $sel = $('#kanbanTaskAssignee');
            $sel.empty().append('<option value="">Unassigned</option>');
            $sel.append($('<option>', { value: kanbanAuthUserId, text: kanbanAuthUserName }));
            if (keepAssignee && keepAssignee.id && String(keepAssignee.id) !== String(kanbanAuthUserId)) {
                $sel.append($('<option>', { value: keepAssignee.id, text: keepAssignee.name }));
            }
            if (selectedId) {
                $sel.val(String(selectedId));
            }
        }

        function openKanbanTaskModal(task, presetStatus) {
            if (task) {
                $('#kanbanTaskFormTitle').text('Edit Task');
                $('#kanbanTaskId').val(task.id);
                $('#kanbanTaskTitle').val(task.title);
                $('#kanbanTaskDescription').val(task.description || '');
                $('#kanbanTaskStatus').val(task.status).prop('disabled', true);
                fillAssigneeSelect(task.assigned_to, task.assigned_to ? {
                    id: task.assigned_to,
                    name: task.assignee_name || ('User #' + task.assigned_to)
                } : null);
            } else {
                $('#kanbanTaskFormTitle').text('Add Task');
                $('#kanbanTaskId').val('');
                $('#kanbanTaskForm')[0].reset();
                $('#kanbanTaskStatus').val(presetStatus || 'todo').prop('disabled', false);
                fillAssigneeSelect(kanbanAuthUserId, null);
            }
            $('#kanbanTaskProjectId').val(kanbanProjectId);
            $('#kanbanTaskModal').modal('show');
        }

        $(document).on('click', '#kanbanAddTaskBtn', function () {
            openKanbanTaskModal(null, 'todo');
        });

        $(document).on('click', '.kanban-col-add', function () {
            openKanbanTaskModal(null, $(this).data('status'));
        });

        $(document).on('click', '.kanban-edit', function (e) {
            e.stopPropagation();
            var id = $(this).data('id');
            $.ajax({
                url: kanbanBoardBase + '/' + kanbanProjectId,
                type: 'GET',
                success: function (res) {
                    var found = null;
                    (res.tasks || []).forEach(function (t) {
                        if (parseInt(t.id, 10) === parseInt(id, 10)) found = t;
                    });
                    if (found) {
                        kanbanProjectMembers = (res.project && res.project.members) || kanbanProjectMembers;
                        openKanbanTaskModal(found, null);
                    }
                }
            });
        });

        $('#kanbanTaskForm').submit(function (event) {
            event.preventDefault();
            var id = $('#kanbanTaskId').val();
            var isEdit = !!id;
            var payload = {
                title: $('#kanbanTaskTitle').val(),
                description: $('#kanbanTaskDescription').val(),
                assigned_to: $('#kanbanTaskAssignee').val() || null
            };
            var url;
            if (isEdit) {
                url = kanbanUpdateRoute;
                payload.id = id;
            } else {
                url = kanbanCreateRoute;
                payload.project_id = kanbanProjectId;
                payload.status = $('#kanbanTaskStatus').val();
            }
            $.ajax({
                url: url,
                type: 'POST',
                data: payload,
                headers: { 'X-CSRF-TOKEN': kanbanCsrf() },
                success: function (response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#kanbanTaskModal').modal('hide');
                        loadKanbanBoard();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred');
                }
            });
        });

        // Moving a card to another column via the edit form is done by drag & drop;
        // status select is locked when editing to keep board order consistent.
        $(document).on('click', '.kanban-delete', function (e) {
            e.stopPropagation();
            var id = $(this).data('id');
            Swal.fire({
                title: 'Remove task?',
                text: 'This task will be removed from the board.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, remove it!'
            }).then(function (result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: kanbanDeleteBase + '/' + id,
                        type: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': kanbanCsrf() },
                        success: function (response) {
                            if (response.success) {
                                toastr.success(response.message);
                                loadKanbanBoard();
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function () {
                            toastr.error('Failed to delete task.');
                        }
                    });
                }
            });
        });
    });
</script>
