@extends('layouts.app')

@section('title')
    System Monitoring
@endsection

@section('body')
    <style>
        .marquee-wrapper {
            max-height: 160px; /* Fixed height to match main display */
            overflow: hidden;
            position: relative;
        }
        .marquee-content {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            animation: scrollUp 10s linear infinite;
        }
        /* Pause scrolling on hover so users can easily read dynamic data */
        .marquee-wrapper:hover .marquee-content {
            animation-play-state: paused;
        }
        @keyframes scrollUp {
            0% { transform: translateY(0); }
            100% { transform: translateY(-50%); }
        }
    </style>
    <div class="row">
        <div class="col-12">
            <div class="mb-4">

                <!-- Dashboard Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1">System Monitoring</h1>
                        <p class="text-muted small mb-0">Live status and real-time monitoring across all active campus modules</p>
                    </div>

                    <!-- Controls & Status Bar -->
                    <div class="d-flex align-items-center gap-2">
                        <!-- Live Clock & Date Badge (Single Line Style) -->
                        <span class="badge bg-warning-subtle text-warning border border-warning px-3 py-2 fs-6 d-flex align-items-center fw-semibold">
                            <i class="ti ti-clock me-2 text-warning fs-6"></i>
                            <span id="tv-clock-date"></span>
                        </span>
                        <!-- Overall Status Badge -->
                        <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-6 d-flex align-items-center">
                            <i class="ti ti-shield-check me-2 fs-6"></i> All Systems Operational
                        </span>
                    </div>
                </div>



                <!-- Middle Row: System Monitoring Cards -->
                <div class="row g-3">

                    <!-- 1. ENROLLMENT QUEUEING -->
                    <div class="col-md-6 col-xl-4">
                        <div class="card card-animate h-100" id="card-enrollment">
                            <div class="card-header pt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-semibold">
                                        <i class="ti ti-users me-1"></i> Enrollment Queueing
                                    </h6>
                                    {{-- <span class="spinner-grow spinner-grow-sm text-success me-2" role="status"></span> --}}
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">
                                        <i class="fas fa-circle me-1 style-dot blink-dot"></i>Live
                                    </span>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="row g-2 align-items-center">
                                    <!-- Left Column: Main Serving Window -->
                                    <div class="col-7 border-end pe-2">
                                        <div class="text-center py-2">
                                            <span class="badge bg-info-subtle text-info mb-1">Counter 02 Serving</span>
                                            <h2 class="fw-bold mb-0 text-success display-6">A-084</h2>
                                            <p class="text-muted small mb-0">Current Ticket</p>
                                        </div>
                                    </div>

                                    <!-- Right Column: Vertical Mini Cards for Other Counters -->
                                    <div class="col-5 ps-2">
                                        <!-- Infinite Scroll Wrapper -->
                                        <div class="marquee-wrapper">
                                            <div class="marquee-content">
                                                <!-- ORIGINAL SET (1 to 6) -->
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <small class="fw-medium text-muted">Win 1</small>
                                                    <span class="fw-bold small">A-083</span>
                                                </div>
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <small class="fw-medium text-muted">Win 2</small>
                                                    <span class="fw-bold small">B-012</span>
                                                </div>
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <small class="fw-medium text-muted">Win 3</small>
                                                    <span class="fw-bold small">A-085</span>
                                                </div>
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <small class="fw-medium text-muted">Win 4</small>
                                                    <span class="fw-bold small">A-086</span>
                                                </div>
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <small class="fw-medium text-muted">Win 5</small>
                                                    <span class="fw-bold small">A-087</span>
                                                </div>
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <small class="fw-medium text-muted">Win 6</small>
                                                    <span class="fw-bold small">B-014</span>
                                                </div>

                                                <!-- DUPLICATE SET (Required for seamless infinite loop) -->
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <small class="fw-medium text-muted">Win 1</small>
                                                    <span class="fw-bold small">A-083</span>
                                                </div>
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <small class="fw-medium text-muted">Win 2</small>
                                                    <span class="fw-bold small">B-012</span>
                                                </div>
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <small class="fw-medium text-muted">Win 3</small>
                                                    <span class="fw-bold small">A-085</span>
                                                </div>
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <small class="fw-medium text-muted">Win 4</small>
                                                    <span class="fw-bold small">A-086</span>
                                                </div>
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <small class="fw-medium text-muted">Win 5</small>
                                                    <span class="fw-bold small">A-087</span>
                                                </div>
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <small class="fw-medium text-muted">Win 6</small>
                                                    <span class="fw-bold small">B-014</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-auto">
                                    <hr class="my-3">
                                    <div class="d-flex justify-content-between text-muted small">
                                        <span>Waiting in Queue: <strong>18</strong></span>
                                        <span>Avg Service: <strong>4 mins</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. FACULTY EVALUATION -->
                    <div class="col-md-6 col-xl-4">
                        <div class="card card-animate h-100" id="card-faculty">
                            <div class="card-header pt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-semibold">
                                        <i class="ti ti-clipboard-check me-1 text-warning"></i> Faculty Evaluation
                                    </h6>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">
                                        <i class="fas fa-circle me-1 style-dot blink-dot"></i>Live
                                    </span>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <!-- Split Content Row -->
                                <div class="row g-2 align-items-center">

                                    <!-- Left Column: Primary Metrics & Progress Bar -->
                                    <div class="col-5 border-end pe-2 text-center py-1">
                                        <h2 class="fw-bold mb-0 text-warning display-6">78%</h2>
                                        <p class="text-muted extra-small mb-1">1,420 / 1,800 Submitted</p>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-warning progress-bar-striped progress-bar-animated" role="progressbar" style="width: 78%;" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                    <!-- Right Column: Real-time Evaluation Submissions Live Feed -->
                                    <div class="col-7 ps-2">
                                        <div class="marquee-wrapper" style="max-height: 160px; overflow: hidden; position: relative;">
                                            <div class="marquee-content d-flex flex-column gap-1">

                                                <!-- SUBMISSION ITEM 1 -->
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <div class="text-truncate me-1">
                                                        <small class="fw-bold d-block text-truncate" style="max-width: 95px;">BSIT-3A • ITE 301</small>
                                                        <span class="text-muted extra-small d-block text-truncate" style="max-width: 95px;">Prof. R. Santos</span>
                                                    </div>
                                                    <span class="badge bg-success-subtle text-success extra-small">Just now</span>
                                                </div>

                                                <!-- SUBMISSION ITEM 2 -->
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <div class="text-truncate me-1">
                                                        <small class="fw-bold d-block text-truncate" style="max-width: 95px;">BSCS-2B • CS 204</small>
                                                        <span class="text-muted extra-small d-block text-truncate" style="max-width: 95px;">Dr. M. Cruz</span>
                                                    </div>
                                                    <span class="badge bg-success-subtle text-success extra-small">1m ago</span>
                                                </div>

                                                <!-- SUBMISSION ITEM 3 -->
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <div class="text-truncate me-1">
                                                        <small class="fw-bold d-block text-truncate" style="max-width: 95px;">BSEMC-1A • EMC 101</small>
                                                        <span class="text-muted extra-small d-block text-truncate" style="max-width: 95px;">Engr. A. Reyes</span>
                                                    </div>
                                                    <span class="badge bg-success-subtle text-success extra-small">3m ago</span>
                                                </div>

                                                <!-- SUBMISSION ITEM 4 -->
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <div class="text-truncate me-1">
                                                        <small class="fw-bold d-block text-truncate" style="max-width: 95px;">BSIS-4A • IS 402</small>
                                                        <span class="text-muted extra-small d-block text-truncate" style="max-width: 95px;">Prof. J. Garcia</span>
                                                    </div>
                                                    <span class="badge bg-success-subtle text-success extra-small">5m ago</span>
                                                </div>

                                                <!-- DUPLICATE SET FOR SMOOTH INFINITE MARQUEE LOOP -->
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <div class="text-truncate me-1">
                                                        <small class="fw-bold d-block text-truncate" style="max-width: 95px;">BSIT-3A • ITE 301</small>
                                                        <span class="text-muted extra-small d-block text-truncate" style="max-width: 95px;">Prof. R. Santos</span>
                                                    </div>
                                                    <span class="badge bg-success-subtle text-success extra-small">Just now</span>
                                                </div>
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <div class="text-truncate me-1">
                                                        <small class="fw-bold d-block text-truncate" style="max-width: 95px;">BSCS-2B • CS 204</small>
                                                        <span class="text-muted extra-small d-block text-truncate" style="max-width: 95px;">Dr. M. Cruz</span>
                                                    </div>
                                                    <span class="badge bg-success-subtle text-success extra-small">1m ago</span>
                                                </div>
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <div class="text-truncate me-1">
                                                        <small class="fw-bold d-block text-truncate" style="max-width: 95px;">BSEMC-1A • EMC 101</small>
                                                        <span class="text-muted extra-small d-block text-truncate" style="max-width: 95px;">Engr. A. Reyes</span>
                                                    </div>
                                                    <span class="badge bg-success-subtle text-success extra-small">3m ago</span>
                                                </div>
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <div class="text-truncate me-1">
                                                        <small class="fw-bold d-block text-truncate" style="max-width: 95px;">BSIS-4A • IS 402</small>
                                                        <span class="text-muted extra-small d-block text-truncate" style="max-width: 95px;">Prof. J. Garcia</span>
                                                    </div>
                                                    <span class="badge bg-success-subtle text-success extra-small">5m ago</span>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <!-- Footer Section (Pinned to bottom via mt-auto) -->
                                <div class="mt-auto">
                                    <hr class="my-3">
                                    <div class="d-flex justify-content-between text-muted extra-small">
                                        <span>Active Term: <strong>Midterm 2026</strong></span>
                                        <span>Days Left: <strong class="text-danger">5 Days</strong></span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- 3. MIS STAFF TASK & LOCATION TRACKER -->
                    <div class="col-md-6 col-xl-4">
                        <div class="card card-animate h-100" id="card-staff-tracker">
                            <div class="card-header pt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-semibold">
                                        <i class="ti ti-user-check me-1 text-info"></i> MIS Personnel Tracker
                                    </h6>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">
                                        <i class="fas fa-circle me-1 style-dot blink-dot"></i>Live
                                    </span>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="row g-2 align-items-center">
                                    <!-- Left Column: Summary Metric -->
                                    <div class="col-5 border-end pe-2 text-center py-1">
                                        <h2 class="fw-bold mb-0 text-primary display-6">5/6</h2>
                                        <p class="text-muted extra-small mb-2">On-Duty Staff</p>
                                        <span class="badge bg-success-subtle text-success extra-small">
                                            <i class="ti ti-point-filled"></i> Active Field
                                        </span>
                                    </div>

                                    <!-- Right Column: Vertical Marquee of Staff Tasks -->
                                    <div class="col-7 ps-2">
                                        <div class="marquee-wrapper" style="max-height: 160px; overflow: hidden; position: relative;">
                                            <div class="marquee-content d-flex flex-column gap-1">
                                                <!-- STAFF ITEM 1 -->
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <div class="text-truncate me-1">
                                                        <small class="fw-bold d-block text-truncate" style="max-width: 90px;">J. Dela Cruz</small>
                                                        <span class="text-muted extra-small d-block text-truncate" style="max-width: 90px;">Lab 1 • PC Repair</span>
                                                    </div>
                                                    <span class="badge bg-warning-subtle text-warning extra-small">Busy</span>
                                                </div>

                                                <!-- STAFF ITEM 2 -->
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <div class="text-truncate me-1">
                                                        <small class="fw-bold d-block text-truncate" style="max-width: 90px;">M. Santos</small>
                                                        <span class="text-muted extra-small d-block text-truncate" style="max-width: 90px;">Server Room • Backup</span>
                                                    </div>
                                                    <span class="badge bg-info-subtle text-info extra-small">In Progress</span>
                                                </div>

                                                <!-- STAFF ITEM 3 -->
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <div class="text-truncate me-1">
                                                        <small class="fw-bold d-block text-truncate" style="max-width: 90px;">R. Reyes</small>
                                                        <span class="text-muted extra-small d-block text-truncate" style="max-width: 90px;">Admin Office • Cable</span>
                                                    </div>
                                                    <span class="badge bg-warning-subtle text-warning extra-small">Busy</span>
                                                </div>

                                                <!-- STAFF ITEM 4 -->
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <div class="text-truncate me-1">
                                                        <small class="fw-bold d-block text-truncate" style="max-width: 90px;">A. Garcia</small>
                                                        <span class="text-muted extra-small d-block text-truncate" style="max-width: 90px;">MIS Office • HelpDesk</span>
                                                    </div>
                                                    <span class="badge bg-success-subtle text-success extra-small">Available</span>
                                                </div>

                                                <!-- DUPLICATE SET FOR SMOOTH INFINITE MARQUEE LOOP -->
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <div class="text-truncate me-1">
                                                        <small class="fw-bold d-block text-truncate" style="max-width: 90px;">J. Dela Cruz</small>
                                                        <span class="text-muted extra-small d-block text-truncate" style="max-width: 90px;">Lab 1 • PC Repair</span>
                                                    </div>
                                                    <span class="badge bg-warning-subtle text-warning extra-small">Busy</span>
                                                </div>
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <div class="text-truncate me-1">
                                                        <small class="fw-bold d-block text-truncate" style="max-width: 90px;">M. Santos</small>
                                                        <span class="text-muted extra-small d-block text-truncate" style="max-width: 90px;">Server Room • Backup</span>
                                                    </div>
                                                    <span class="badge bg-info-subtle text-info extra-small">In Progress</span>
                                                </div>
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <div class="text-truncate me-1">
                                                        <small class="fw-bold d-block text-truncate" style="max-width: 90px;">R. Reyes</small>
                                                        <span class="text-muted extra-small d-block text-truncate" style="max-width: 90px;">Admin Office • Cable</span>
                                                    </div>
                                                    <span class="badge bg-warning-subtle text-warning extra-small">Busy</span>
                                                </div>
                                                <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                    <div class="text-truncate me-1">
                                                        <small class="fw-bold d-block text-truncate" style="max-width: 90px;">A. Garcia</small>
                                                        <span class="text-muted extra-small d-block text-truncate" style="max-width: 90px;">MIS Office • HelpDesk</span>
                                                    </div>
                                                    <span class="badge bg-success-subtle text-success extra-small">Available</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer: Deployment Stats -->
                                <div class="mt-auto">
                                    <hr class="my-3">
                                    <div class="d-flex justify-content-between text-muted extra-small">
                                        <span>Field Deployment: <strong>3 Staff</strong></span>
                                        <span>Office Support: <strong>2 Staff</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. IT HELPDESK TICKETING -->
                    <div class="col-md-6 col-xl-4">
                        <div class="card card-animate h-100" id="card-helpdesk">
                            <div class="card-header pt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-semibold">
                                        <i class="ti ti-headset me-1 text-primary"></i> IT HelpDesk Ticketing
                                    </h6>
                                    <div class="d-flex align-items-center gap-1">
                                        <button type="button" id="helpdesk-sound-toggle" class="btn btn-sm btn-light border" title="Toggle sound alerts">
                                            <i class="ti ti-volume" id="helpdesk-sound-icon"></i>
                                        </button>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">
                                            <i class="fas fa-circle me-1 style-dot blink-dot"></i>Live
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="row g-2 align-items-center">
                                    <!-- Left Column: Vertical Ticket Metrics -->
                                    <div class="col-5 border-end pe-2">
                                        <div class="d-flex flex-column gap-1">
                                            <div class="p-1 px-2 border rounded text-center">
                                                <h6 class="fw-bold text-warning mb-0" id="helpdesk-pending-count">{{ $counts['pending'] ?? 0 }}</h6>
                                                <small class="text-muted extra-small">Pending</small>
                                            </div>
                                            <div class="p-1 px-2 border rounded text-center">
                                                <h6 class="fw-bold text-info mb-0" id="helpdesk-progress-count">{{ $counts['in_progress'] ?? 0 }}</h6>
                                                <small class="text-muted extra-small">In Progress</small>
                                            </div>
                                            <div class="p-1 px-2 border rounded text-center">
                                                <h6 class="fw-bold text-success mb-0" id="helpdesk-resolved-count">{{ $counts['resolved'] ?? 0 }}</h6>
                                                <small class="text-muted extra-small">Resolved</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right Column: Live Active Tickets (looping marquee, fixed height) -->
                                    <div class="col-7 ps-2">
                                        <div class="marquee-wrapper">
                                            <div class="marquee-content" id="helpdesk-latest-list">
                                                @if (!empty($latest) && count($latest))
                                                    {{-- Rendered twice for the seamless infinite marquee loop --}}
                                                    @for ($r = 0; $r < 2; $r++)
                                                        @foreach ($latest as $t)
                                                            <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                                <div class="text-truncate me-1">
                                                                    <small class="fw-medium d-block text-truncate" style="max-width: 90px;">{{ \Illuminate\Support\Str::limit($t['issue_description'] ?? 'Ticket', 18) }}</small>
                                                                    <span class="text-muted extra-small">#{{ $t['ticket_number'] ?? $t['id'] }}</span>
                                                                </div>
                                                                <span class="badge extra-small {{ ($t['priority'] ?? '') === 'High' ? 'bg-danger-subtle text-danger' : ((($t['priority'] ?? '') === 'Medium') ? 'bg-warning-subtle text-warning' : 'bg-secondary-subtle text-secondary') }}">{{ $t['priority'] ?? '-' }}</span>
                                                            </div>
                                                        @endforeach
                                                    @endfor
                                                @else
                                                    <div class="text-muted extra-small text-center py-2">No active tickets</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer: Priority Levels Breakdown -->
                                <div class="mt-auto">
                                    <hr class="my-3">
                                    <div class="d-flex justify-content-between text-muted extra-small">
                                        <span>High: <strong class="text-danger" id="helpdesk-prio-high">{{ $priority['High'] ?? 0 }}</strong></span>
                                        <span>Medium: <strong class="text-warning" id="helpdesk-prio-medium">{{ $priority['Medium'] ?? 0 }}</strong></span>
                                        <span>Low: <strong class="text-secondary" id="helpdesk-prio-low">{{ $priority['Low'] ?? 0 }}</strong></span>
                                        <span>Urgent: <strong class="text-danger" id="helpdesk-prio-urgent">{{ $priority['Urgent'] ?? 0 }}</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5. PURCHASE REQUEST & ICT SPECS -->
                    <div class="col-md-4">
                        <div class="card card-animate h-100" id="card-pr-ict">
                            <div class="card-header pt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-semibold">
                                        <i class="ti ti-file-certificate me-1 text-secondary"></i> Purchase Request ICT Specs
                                    </h6>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">
                                        <i class="fas fa-circle me-1 style-dot blink-dot"></i>Live
                                    </span>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center py-1 mb-2 border-bottom pb-2">
                                    <div>
                                        <span class="fw-bold d-block small">PR #2026-089</span>
                                        <small class="text-muted">Desktop Specs Verification</small>
                                    </div>
                                    <span class="badge bg-warning text-dark">MIS Review</span>
                                </div>
                                <div class="d-flex justify-content-between text-muted small">
                                    <span>Pending Requests: <strong>4</strong></span>
                                    <span>Approved Today: <strong>8</strong></span>
                                </div>
                                <div class="mt-auto">
                                    <hr class="my-3">
                                    <div class="d-flex justify-content-between text-muted small">
                                        <span>Queue Status: <strong>Normal</strong></span>
                                        <span>Avg Turnaround: <strong>1 Day</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 6. STUDENT CAMPUS WIFI -->
                    <div class="col-md-6 col-xl-4">
                        <div class="card card-animate h-100" id="card-wifi">
                            <div class="card-header pt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-semibold">
                                        <i class="ti ti-antenna me-1 text-danger"></i> Student Campus Wifi
                                    </h6>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">
                                        <i class="fas fa-circle me-1 style-dot blink-dot"></i>Live
                                    </span>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="row g-2 align-items-center">
                                    <!-- Left Column: Connected Devices & Main Status -->
                                    <div class="col-6 border-end pe-2 text-center py-1">
                                        <h3 class="fw-bold mb-0 text-dark display-6">842</h3>
                                        <p class="text-muted extra-small mb-2">Connected Devices</p>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: 65%;" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                    <!-- Right Column: Voucher Code Mini Cards -->
                                    <div class="col-6 ps-2">
                                        <div class="d-flex flex-column gap-1">
                                            <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                <div class="text-truncate">
                                                    <small class="fw-medium text-muted d-block extra-small">Availed</small>
                                                    <span class="fw-bold text-success small">1,250</span>
                                                </div>
                                                <i class="ti ti-ticket text-success fs-5"></i>
                                            </div>
                                            <div class="p-1 px-2 border rounded card-body-content-bg-color d-flex justify-content-between align-items-center">
                                                <div class="text-truncate">
                                                    <small class="fw-medium text-muted d-block extra-small">Remaining</small>
                                                    <span class="fw-bold text-warning small">750</span>
                                                </div>
                                                <i class="ti ti-ticket-off text-warning fs-5"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer: Network Stats & Current A.Y. / Semester -->
                                <div class="mt-auto">
                                    <hr class="my-3">
                                    <div class="d-flex justify-content-between text-muted extra-small">
                                        <span>Active APs: <strong>28 / 30</strong></span>
                                        <span>A.Y.: <strong>2026–2027 (1st Sem)</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        // Updated Fullscreen Function for Multiple Dynamic Cards
        function toggleZoomDisplay(cardId, buttonEl) {
            const element = document.getElementById(cardId);
            const icon = buttonEl.querySelector('.zoom-icon');
            const text = buttonEl.querySelector('.zoom-text');

            if (!element) return;

            if (!document.fullscreenElement) {
                if (element.requestFullscreen) {
                    element.requestFullscreen();
                } else if (element.webkitRequestFullscreen) {
                    element.webkitRequestFullscreen();
                } else if (element.msRequestFullscreen) {
                    element.msRequestFullscreen();
                }
                if (icon) icon.className = 'ti ti-minimize me-1 zoom-icon';
                if (text) text.innerText = 'Exit Zoom';
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
            }
        }

        document.addEventListener('fullscreenchange', () => {
            if (!document.fullscreenElement) {
                document.querySelectorAll('.zoom-icon').forEach(icon => icon.className = 'ti ti-maximize me-1 zoom-icon');
                document.querySelectorAll('.zoom-text').forEach(text => text.innerText = 'Zoom');
            }
        });
    </script>

    <script>
        // Live Digital Clock Function with Combined Date/Time
        function updateTVClock() {
            const now = new Date();

            // Format Time (10:30:01 PM)
            const timeStr = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });

            // Format Date (Sat, Sep 26, 2026)
            const dateStr = now.toLocaleDateString('en-US', {
                weekday: 'short',
                month: 'short',
                day: '2-digit',
                year: 'numeric'
            });

            const clockEl = document.getElementById('tv-clock-date');
            if (clockEl) {
                clockEl.innerText = `${timeStr} | ${dateStr}`;
            }
        }

        setInterval(updateTVClock, 1000);
        updateTVClock();
    </script>
@endsection
