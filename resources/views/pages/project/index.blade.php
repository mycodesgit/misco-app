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

    <style>
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
    </script>
@endsection
