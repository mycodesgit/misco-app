@extends('layouts.app')

@section('title')
    {{ auth()->user()->pageTitle('Work Progress Tracker') }}
@endsection

@section('body')
    <div class="row">
        <div class="col-12">
            <div class="mb-4">

                <!-- Dashboard Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1">{{ auth()->user()->pageTitle('Work Progress Tracker') }}</h1>
                        <p class="text-muted small mb-0">Track projects, timelines, team members and completion progress.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-success text-white" data-bs-toggle="modal" data-bs-target="#createProjectModal">
                            <i class="ti ti-plus me-1"></i> New Project
                        </button>
                    </div>
                </div>

                <!-- Top Row: Metric Cards -->
                <div class="row g-3 mb-5">
                    <div class="col-md-12">
                        <div class="card card-animate">
                            <div class="card-header pt-3 d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0">
                                    <i class="ti ti-search"></i> Search to show data
                                </h6>
                                <span class="badge bg-primary">My Office: {{ $myOffice->office_abbr ?? 'N/A' }}</span>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="#" id="projectFilterForm" class="mb-3">
                                    <div class="form-group">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Status:</label>
                                                <select id="filterStatus" class="form-control form-control-sm">
                                                    <option value="">All Status</option>
                                                    @foreach ($statuses as $status)
                                                        <option value="{{ $status }}">{{ $status }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="d-flex flex-column h-100">
                                                    <label class="form-label fw-semibold opacity-0 d-none d-md-block">Action</label>
                                                    <button type="submit" class="btn btn-success btn-sm btn-block">Search</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <div class="page-header" style="border-bottom: 1px solid #04401f;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="card card-animate">
                            <div class="card-header pt-3">
                                <h6 class="card-title">
                                    <i class="ti ti-chart-gantt"></i> Project Timeline (Gantt)
                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="projectGantt" class="gantt-wrapper">
                                    <div class="text-center text-muted small py-3">No projects to display.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="card card-animate">
                            <div class="card-header pt-3">
                                <h6 class="card-title">
                                    <i class="ti ti-list"></i> Project List Section
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive p-2">
                                    <table id="projectTable" class="table table-hover" style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th>Project Name</th>
                                                <th>Office</th>
                                                <th>Team Members</th>
                                                <th>Start - End</th>
                                                <th>Duration</th>
                                                <th>Days Remaining</th>
                                                <th>Progress</th>
                                                <th>Remarks</th>
                                                <th>Status</th>
                                                <th width="10%">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Project Modal -->
    <div class="modal fade" id="createProjectModal" tabindex="-1" aria-labelledby="createProjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createProjectModalLabel"><i class="ti ti-plus me-1"></i> New Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createProjectForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Project Name: <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control form-control-sm" placeholder="Enter project name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Office:</label>
                                <input type="text" class="form-control form-control-sm" value="{{ $myOffice->office_abbr ?? 'N/A' }} - {{ $myOffice->office_name ?? '' }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status: <span class="text-danger">*</span></label>
                                <select name="status" class="form-control form-control-sm" required>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}">{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Team Members: <small class="text-muted">(within my office only)</small></label>
                                <select name="member_ids[]" id="createMembers" class="form-control form-control-sm select2bs4" multiple="multiple" data-placeholder="Select team members">
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Start Date: <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">End Date: <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Progress: <span id="createProgressLabel" class="text-primary">0%</span></label>
                                <input type="range" name="progress" id="createProgress" class="form-range" min="0" max="100" value="0">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Remarks:</label>
                                <textarea name="remarks" rows="2" class="form-control" placeholder="Notes, blockers, next steps..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Close</button>
                        <button type="submit" class="btn btn-success text-white"><i class="fas fa-save me-1"></i> Save Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Project Modal -->
    <div class="modal fade" id="editProjectModal" tabindex="-1" aria-labelledby="editProjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProjectModalLabel"><i class="ti ti-pencil me-1"></i> Update Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editProjectForm">
                    @csrf
                    <input type="hidden" name="id" id="editProjectId">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Project Name: <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="editProjectName" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Office:</label>
                                <input type="text" class="form-control form-control-sm" value="{{ $myOffice->office_abbr ?? 'N/A' }} - {{ $myOffice->office_name ?? '' }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status: <span class="text-danger">*</span></label>
                                <select name="status" id="editProjectStatus" class="form-control form-control-sm" required>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}">{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Team Members: <small class="text-muted">(within my office only)</small></label>
                                <select name="member_ids[]" id="editProjectMembers" class="form-control form-control-sm select2bs4" multiple="multiple" data-placeholder="Select team members">
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Start Date: <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="editProjectStart" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">End Date: <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" id="editProjectEnd" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Progress: <span id="editProgressLabel" class="text-primary">0%</span></label>
                                <input type="range" name="progress" id="editProjectProgress" class="form-range" min="0" max="100" value="0">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Remarks:</label>
                                <textarea name="remarks" id="editProjectRemarks" rows="2" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Close</button>
                        <button type="submit" class="btn btn-success text-white"><i class="fas fa-save me-1"></i> Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Kanban Board Modal -->
    <div class="modal fade" id="kanbanModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="kanbanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 1140px;">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="overflow-hidden" style="min-width:0;">
                        <h5 class="modal-title mb-0 text-truncate" id="kanbanModalLabel"><i class="ti ti-kanban me-1"></i> <span id="kanbanProjectName">Project Board</span></h5>
                        <small class="text-muted" id="kanbanProjectMeta"></small>
                    </div>
                    {{-- <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-success text-white" id="kanbanAddTaskBtn">
                            <i class="ti ti-plus me-1"></i> Add Task
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div> --}}
                </div>
                <div class="modal-body">
                    <div class="kanban-board" id="kanbanBoard">
                        <div class="kanban-column" data-status="todo">
                            <div class="kanban-col-header">
                                <span class="badge bg-secondary">To Do <span class="kanban-count" data-count="todo">0</span></span>
                                <button type="button" class="btn btn-sm btn-link text-secondary p-0 kanban-col-add" data-status="todo" title="Add task"><i class="ti ti-plus"></i></button>
                            </div>
                            <div class="kanban-list" data-status="todo"></div>
                        </div>
                        <div class="kanban-column" data-status="in_progress">
                            <div class="kanban-col-header">
                                <span class="badge bg-info">In Progress <span class="kanban-count" data-count="in_progress">0</span></span>
                                <button type="button" class="btn btn-sm btn-link text-secondary p-0 kanban-col-add" data-status="in_progress" title="Add task"><i class="ti ti-plus"></i></button>
                            </div>
                            <div class="kanban-list" data-status="in_progress"></div>
                        </div>
                        <div class="kanban-column" data-status="on_hold">
                            <div class="kanban-col-header">
                                <span class="badge bg-warning text-dark">On Hold <span class="kanban-count" data-count="on_hold">0</span></span>
                                <button type="button" class="btn btn-sm btn-link text-secondary p-0 kanban-col-add" data-status="on_hold" title="Add task"><i class="ti ti-plus"></i></button>
                            </div>
                            <div class="kanban-list" data-status="on_hold"></div>
                        </div>
                        <div class="kanban-column" data-status="done">
                            <div class="kanban-col-header">
                                <span class="badge bg-success">Done <span class="kanban-count" data-count="done">0</span></span>
                                <button type="button" class="btn btn-sm btn-link text-secondary p-0 kanban-col-add" data-status="done" title="Add task"><i class="ti ti-plus"></i></button>
                            </div>
                            <div class="kanban-list" data-status="done"></div>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2"><i class="ti ti-arrows-move me-1"></i>Drag cards between columns — the board saves automatically and stays for the whole project duration.</small>
                </div>
                <div class="modal-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Kanban Task Modal (Add / Edit) -->
    <div class="modal fade" id="kanbanTaskModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="kanbanTaskModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="kanbanTaskModalLabel"><i class="ti ti-plus me-1"></i> <span id="kanbanTaskFormTitle">Add Task</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="kanbanTaskForm">
                    @csrf
                    <input type="hidden" name="id" id="kanbanTaskId">
                    <input type="hidden" name="project_id" id="kanbanTaskProjectId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">What are you working on? <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="kanbanTaskTitle" class="form-control form-control-sm" placeholder="e.g. Configure server backup" required maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Details:</label>
                            <textarea name="description" id="kanbanTaskDescription" rows="3" class="form-control" placeholder="Steps, notes, links..."></textarea>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Assigned To:</label>
                                <select name="assigned_to" id="kanbanTaskAssignee" class="form-control form-control-sm">
                                    <option value="">Unassigned</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Column:</label>
                                <select name="status" id="kanbanTaskStatus" class="form-control form-control-sm">
                                    <option value="todo">To Do</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="on_hold">On Hold</option>
                                    <option value="done">Done</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Close</button>
                        <button type="submit" class="btn btn-success text-white"><i class="fas fa-save me-1"></i> Save Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* Kanban board */
        .kanban-board { display: flex; gap: 12px; overflow-x: auto; padding-bottom: 8px; min-height: 320px; }
        .kanban-column { flex: 1 0 240px; min-width: 240px; max-width: 320px; background: var(--bs-light, #f8f9fa); border: 1px solid var(--bs-border-color, #dee2e6); border-radius: 10px; display: flex; flex-direction: column; max-height: 60vh; }
        [data-bs-theme="dark"] .kanban-column { background: #2b3035; }
        .kanban-col-header { display: flex; justify-content: space-between; align-items: center; padding: 10px 12px 6px; }
        .kanban-list { flex-grow: 1; overflow-y: auto; padding: 6px 10px 12px; display: flex; flex-direction: column; gap: 8px; min-height: 120px; border-radius: 0 0 10px 10px; }
        .kanban-list.drag-over { outline: 2px dashed #198754; outline-offset: -4px; background: rgba(25, 135, 84, 0.06); }
        .kanban-card { background: var(--bs-body-bg, #fff); border: 1px solid var(--bs-border-color, #dee2e6); border-radius: 8px; padding: 10px 12px; cursor: grab; box-shadow: 0 1px 2px rgba(0,0,0,0.06); }
        .kanban-card:active { cursor: grabbing; }
        .kanban-card.dragging { opacity: 0.45; }
        .kanban-card .kanban-title { font-weight: 600; }
        .kanban-card.done-card .kanban-title { text-decoration: line-through; color: var(--bs-secondary, #6c757d); }
        .kanban-desc { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        .kanban-assignee { display: inline-flex; align-items: center; gap: 6px; }
        .kanban-assignee .member-avatar { width: 24px; height: 24px; font-size: 0.6rem; border: none; margin: 0; }
        /* SweetAlert must sit above stacked Bootstrap modals (board + task form) */
        .swal2-container { z-index: 2060 !important; }
        .kanban-empty { border: 1px dashed var(--bs-border-color, #dee2e6); border-radius: 8px; text-align: center; color: var(--bs-secondary, #6c757d); font-size: 0.75rem; padding: 14px 6px; }
        .gantt-wrapper { overflow-x: auto; }
        .gantt-chart { min-width: 100%; font-size: 0.75rem; }
        .gantt-row { display: flex; align-items: center; border-bottom: 1px solid var(--bs-border-color, #eee); min-height: 38px; }
        .gantt-label { width: 200px; min-width: 200px; padding: 6px 8px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .gantt-track { position: relative; flex-grow: 1; height: 38px; }
        .gantt-bar { position: absolute; top: 9px; height: 20px; border-radius: 10px; background: #e9ecef; overflow: hidden; min-width: 4px; }
        .gantt-fill { height: 100%; background: linear-gradient(90deg, #65ab85, #28a745); border-radius: 10px; }
        .gantt-today { position: absolute; top: 0; bottom: 0; width: 2px; background: #dc3545; }
        .gantt-months { display: flex; border-bottom: 2px solid var(--bs-border-color, #ddd); }
        .gantt-month { text-align: center; font-weight: 600; color: var(--bs-secondary, #6c757d); padding: 4px 0; border-left: 1px solid var(--bs-border-color, #eee); }
        [data-bs-theme="dark"] .gantt-bar { background: #343a40; }

        /* Overlapping team member avatars */
        .avatar-stack { display: inline-flex; align-items: center; }
        .avatar-stack .member-avatar {
            width: 28px; height: 28px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.65rem; font-weight: 700; color: #fff;
            border: 2px solid #fff; margin-left: -9px; cursor: default;
        }
        .avatar-stack .member-avatar:first-child { margin-left: 0; }
        .avatar-stack .member-more { background: #6c757d !important; font-size: 0.6rem; }
        [data-bs-theme="dark"] .avatar-stack .member-avatar { border-color: #212529; }
    </style>

    <script>
        var myOfficeId = "{{ auth()->user()->office_id }}";
        var projectReadRoute = "{{ route('project.show') }}";
        var projectCreateRoute = "{{ route('project.create') }}";
        var projectUpdateRoute = "{{ route('project.update') }}";
        var projectDeleteBase = "{{ url('/projects/delete') }}";
        var projectMembersBase = "{{ url('/projects/members') }}";
        var kanbanAuthUserId = "{{ auth()->id() }}";
        var kanbanAuthUserName = @json(trim(auth()->user()->fname . ' ' . auth()->user()->lname));
        var kanbanBoardBase = "{{ url('/projects/kanban') }}";
        var kanbanCreateRoute = "{{ route('project.kanban.store') }}";
        var kanbanUpdateRoute = "{{ route('project.kanban.update') }}";
        var kanbanReorderRoute = "{{ route('project.kanban.reorder') }}";
        var kanbanDeleteBase = "{{ url('/projects/kanban') }}";
    </script>
@endsection
