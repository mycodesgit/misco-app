@extends('layouts.app')

@section('title')
    {{ auth()->user()->pageTitle(' Ticketing Dashboard') }}
@endsection

@section('body')
    <div class="row">
        <div class="col-12">
            <div class="mb-4">

                <!-- Dashboard Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h1 class="h4 fw-bold mb-1">{{ auth()->user()->pageTitle('Dashboard Overview') }}</h1>
                        <p class="text-muted small mb-0">Track your daily task progress and activity metrics across the year.</p>
                    </div>

                    <!-- Controls & Status Bar -->
                    <div class="d-flex align-items-center gap-2">
                        <form action="{{ url()->current() }}" method="GET" class="d-flex align-items-center gap-2">
                            <label for="yearSelect" class="form-label mb-0 small text-muted">Year:</label>
                            <select id="yearSelect" name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                                @php
                                    $selectedYear = request('year', date('Y'));
                                @endphp
                                @for ($y = date('Y'); $y >= date('Y') - 4; $y--)
                                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </form>
                    </div>
                </div>

                <!-- Stats & Welcome Grid -->
                <div class="row g-3 mb-4">

                    <!-- Welcome Hero Card (col-md-6) -->
                    <div class="col-md-4">
                        <div class="card overflow-hidden h-100 position-relative welcome-hero-card">
                            <div class="card-body p-4 d-flex flex-column justify-content-between position-relative z-1">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 small">
                                            <i class="bi bi-circle-fill me-1 small"></i> Active Session
                                        </span>
                                    </div>
                                    <h3 class="fw-bold mb-2">
                                        Welcome, {{ auth()->user()->fname ?? 'Personnel' }}! 👋
                                    </h3>
                                    <p class="text-secondary mb-3 fs-6">
                                        You've completed <strong class="text-dark dark-text-light">94.2%</strong> of your assigned tasks this month. Keep up the consistent pace!
                                    </p>
                                </div>

                                <div class="pt-2">
                                    <a href="#" class="btn btn-success btn-sm rounded-pill px-3 me-2">
                                        <i class="bi bi-plus-lg me-1"></i> New Activity
                                    </a>
                                    <a href="#" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                        View Schedule
                                    </a>
                                </div>
                            </div>
                            <!-- Decorative SVG background pattern -->
                            <div class="hero-bg-accent position-absolute end-0 bottom-0 pointer-events-none me-3 mb-2">

                            </div>
                        </div>
                    </div>

                    <!-- Activity Grid Card (Grouped by Month with Gaps) -->
                    <div class="col-md-8">
                        <div class="card card-animate h-100">
                            <div class="card-header pt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-semibold">
                                        <i class="ti ti-device-laptop me-1"></i> Activity Progress (Jan 1 - Dec 31, {{ $selectedYear }})
                                    </h6>
                                    {{-- <span class="spinner-grow spinner-grow-sm text-success me-2" role="status"></span> --}}
                                    <span class="small">
                                        <i class="fas fa-square me-1 text-success style-dot blink-dot"></i> Total: <strong>348 completed tasks</strong>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                @php
                                    // Build structure grouped by Month -> Weeks -> Days
                                    $yearStart = \Carbon\Carbon::createFromDate($selectedYear, 1, 1);
                                    $yearEnd = \Carbon\Carbon::createFromDate($selectedYear, 12, 31);

                                    $monthsData = [];

                                    for ($m = 1; $m <= 12; $m++) {
                                        $monthStart = \Carbon\Carbon::createFromDate($selectedYear, $m, 1);
                                        $monthEnd = $monthStart->copy()->endOfMonth();

                                        // Find first Monday on or prior to month start
                                        $curr = $monthStart->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                                        $weeks = [];

                                        while ($curr->lte($monthEnd)) {
                                            $weekDays = [];
                                            for ($d = 0; $d < 7; $d++) {
                                                // Include day if it falls within the current month & year
                                                if ($curr->month == $m && $curr->year == $selectedYear) {
                                                    $weekDays[] = [
                                                        'date' => $curr->format('M j, Y'),
                                                        'day_idx' => $curr->dayOfWeekIso, // 1 (Mon) to 7 (Sun)
                                                        'active' => true
                                                    ];
                                                } else {
                                                    // Placeholder empty cell for alignment
                                                    $weekDays[] = [
                                                        'date' => null,
                                                        'day_idx' => $curr->dayOfWeekIso,
                                                        'active' => false
                                                    ];
                                                }
                                                $curr->addDay();
                                            }
                                            $weeks[] = $weekDays;
                                        }

                                        $monthsData[$m] = [
                                            'name' => $monthStart->format('M'),
                                            'weeks' => $weeks
                                        ];
                                    }
                                @endphp

                                <!-- Heatmap Container -->
                                <div class="table-responsive">
                                    <div class="activity-heatmap">

                                        <div class="heatmap-grid-wrapper">
                                            <!-- Days Label Column -->
                                            <div class="days-label text-muted">
                                                <span>Mon</span>
                                                <span>Tue</span>
                                                <span>Wed</span>
                                                <span>Thu</span>
                                                <span>Fri</span>
                                                <span>Sat</span>
                                                <span>Sun</span>
                                            </div>

                                            <!-- Grouped Months Container -->
                                            <div class="heatmap-months-container">
                                                @foreach ($monthsData as $monthNum => $month)
                                                    <div class="heatmap-month-group">
                                                        <!-- Month Label Header -->
                                                        <div class="month-header text-muted small fw-semibold">
                                                            {{ $month['name'] }}
                                                        </div>

                                                        <!-- Month Weeks -->
                                                        <div class="heatmap-month-weeks">
                                                            @foreach ($month['weeks'] as $week)
                                                                <div class="heatmap-week">
                                                                    @foreach ($week as $day)
                                                                        @if ($day['active'])
                                                                            @php
                                                                                $randomLevel = rand(0, 4);
                                                                                $taskCount = $randomLevel * 2;
                                                                            @endphp
                                                                            <div class="heatmap-cell level-{{ $randomLevel }}"
                                                                                data-bs-toggle="tooltip"
                                                                                data-bs-placement="top"
                                                                                title="{{ $taskCount }} {{ Str::plural('task', $taskCount) }} completed on {{ $day['date'] }}">
                                                                            </div>
                                                                        @else
                                                                            <div class="heatmap-cell level-empty"></div>
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- Legend -->
                                <div class="d-flex justify-content-end align-items-center gap-2 mt-3 text-muted small">
                                    <span>Less</span>
                                    <div class="heatmap-cell level-0"></div>
                                    <div class="heatmap-cell level-1"></div>
                                    <div class="heatmap-cell level-2"></div>
                                    <div class="heatmap-cell level-3"></div>
                                    <div class="heatmap-cell level-4"></div>
                                    <span>More</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Performers & Category Leaderboard (col-md-4) -->
                    <div class="col-md-4">

                        <!-- Top Ticket Resolvers Leaderboard (Podium Style) -->
                        <div class="card card-animate mb-3 overflow-hidden">
                            <!-- Card Header -->
                            <div class="card-header pt-3 px-3 pb-2 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="p-2 bg-warning bg-opacity-10 rounded-3 text-warning d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="ti ti-trophy fs-6"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0">Leaderboard</h6>
                                </div>
                                <!-- Timeframe Pills -->
                                <ul class="nav nav-pills nav-pills-custom gap-1">
                                    <li class="nav-item">
                                        <a class="nav-link active px-2.5 py-1" href="#">Daily</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-muted px-2.5 py-1" href="#">Monthly</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-muted px-2.5 py-1" href="#">All time</a>
                                    </li>
                                </ul>
                            </div>

                            <div class="card-body p-3">
                                <!-- Top 3 Podium Layout -->
                                <div class="row align-items-end text-center mb-4 pt-3 pb-3 rounded-4 mx-0 podium-wrapper">

                                    <!-- Rank 2 (Left) -->
                                    <div class="col-4 px-1">
                                        <div class="podium-item">
                                            <div class="position-relative d-inline-block mb-2">
                                                <div class="avatar-podium border border-2 border-primary rounded-circle mx-auto d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary fw-bold shadow-sm">
                                                    MA
                                                </div>
                                                <span class="badge bg-primary rounded-circle position-absolute start-50 translate-middle-x badge-rank">2</span>
                                            </div>
                                            <h6 class="mb-0 fw-semibold text-truncate small">Mary Ann</h6>
                                            <small class="text-muted d-block fw-medium text-nowrap" style="font-size: 0.7rem;">1,490 pts</small>
                                        </div>
                                    </div>

                                    <!-- Rank 1 (Center - Elevated) -->
                                    <div class="col-4 px-1">
                                        <div class="podium-item podium-item-top">
                                            <div class="position-relative d-inline-block mb-2">
                                                <i class="ti ti-crown text-warning fs-5 position-absolute top-0 start-50 translate-middle-x crown-icon"></i>
                                                <div class="avatar-podium avatar-podium-lg border border-3 border-warning rounded-circle mx-auto d-flex align-items-center justify-content-center bg-warning bg-opacity-15 text-dark fw-bold shadow">
                                                    HS
                                                </div>
                                                <span class="badge bg-warning text-dark rounded-circle position-absolute start-50 translate-middle-x badge-rank fw-bold">1</span>
                                            </div>
                                            <h6 class="mb-0 fw-bold text-truncate small">Hazel Shawn</h6>
                                            <small class="text-warning-emphasis fw-bold d-block text-nowrap" style="font-size: 0.72rem;">1,800 pts</small>
                                        </div>
                                    </div>

                                    <!-- Rank 3 (Right) -->
                                    <div class="col-4 px-1">
                                        <div class="podium-item">
                                            <div class="position-relative d-inline-block mb-2">
                                                <div class="avatar-podium border border-2 border-info rounded-circle mx-auto d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info fw-bold shadow-sm">
                                                    TN
                                                </div>
                                                <span class="badge bg-info rounded-circle position-absolute start-50 translate-middle-x badge-rank">3</span>
                                            </div>
                                            <h6 class="mb-0 fw-semibold text-truncate small">Troy Newton</h6>
                                            <small class="text-muted d-block fw-medium text-nowrap" style="font-size: 0.7rem;">1,205 pts</small>
                                        </div>
                                    </div>

                                </div>

                                <!-- Leaderboard Table Header -->
                                <div class="d-flex justify-content-between px-2 pb-2 text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                                    <span>Rank & Player</span>
                                    <span>Points</span>
                                </div>

                                <!-- Remaining Rankings List (Rank 4+) -->
                                <div class="list-group list-group-flush gap-1">

                                    <!-- Rank 4 -->
                                    <div class="list-group-item rounded-3 border-0 bg-opacity-50 d-flex align-items-center justify-content-between p-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold text-muted small text-center" style="width: 14px; font-size: 0.75rem;">4</span>
                                            <div class="avatar-circle-xs bg-primary text-white fw-bold shadow-sm">JD</div>
                                            <span class="fw-medium small text-truncate" style="max-width: 120px;">John Doe</span>
                                        </div>
                                        <span class="fw-semibold small text-secondary">1,000 pts</span>
                                    </div>

                                    <!-- Rank 5 -->
                                    <div class="list-group-item rounded-3 border-0 bg-opacity-50 d-flex align-items-center justify-content-between p-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold text-muted small text-center" style="width: 14px; font-size: 0.75rem;">5</span>
                                            <div class="avatar-circle-xs bg-success text-white fw-bold shadow-sm">RK</div>
                                            <span class="fw-medium small text-truncate" style="max-width: 120px;">Rico Karl</span>
                                        </div>
                                        <span class="fw-semibold small text-secondary">900 pts</span>
                                    </div>

                                    <!-- Current User (Highlighted Rank 8) -->
                                    <div class="list-group-item rounded-3 border border-success border-opacity-25 bg-success bg-opacity-10 d-flex align-items-center justify-content-between p-2 shadow-sm">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold text-primary small text-center" style="width: 14px; font-size: 0.75rem;">8</span>
                                            <div class="avatar-circle-xs bg-primary text-white fw-bold shadow-sm">SJ</div>
                                            <div class="d-flex align-items-center gap-1">
                                                <span class="fw-bold small">Shane Jane</span>
                                                <span class="badge bg-info text-white rounded-pill px-1.5 py-0.5" style="font-size: 0.55rem; letter-spacing: 0.3px;">YOU</span>
                                            </div>
                                        </div>
                                        <span class="fw-bold text-info small">720 pts</span>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- Top Issue Categories -->
                        <div class="card card-animate">
                            <div class="card-header bg-transparent pt-3 border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold small">
                                    <i class="ti ti-chart-pie me-1"></i> Top Ticket Categories
                                </h6>
                                <span class="badge bg-light text-dark border">Volume</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center small mb-1">
                                        <span><i class="ti ti-server me-1 text-primary"></i> Infrastructure & Server</span>
                                        <span class="fw-bold">42%</span>
                                    </div>
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 42%"></div>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center small mb-1">
                                        <span><i class="ti ti-printer me-1 text-info"></i> Hardware & Devices</span>
                                        <span class="fw-bold">30%</span>
                                    </div>
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 30%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between align-items-center small mb-1">
                                        <span><i class="ti ti-user-lock me-1 text-warning"></i> Account Access</span>
                                        <span class="fw-bold">28%</span>
                                    </div>
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 28%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- IT Ticketing Metric Cards (col-md-8 container) -->
                    <div class="col-md-8">
                        <div class="row g-3 mb-3">
                            <!-- 1. New Ticket -->
                            <div class="col-md-4">
                                <div class="card card-animate">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="text-muted fw-semibold">New Ticket</span>
                                                <h3 class="fw-bold mb-0 mt-1">2</h3>
                                            </div>
                                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                                                <i class="ti ti-ticket fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="mt-2 small text-muted">
                                            <span class="text-primary fw-semibold"><i class="ti ti-ticket me-1"></i></span> Today's Request
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Pending Requests -->
                            <div class="col-md-4">
                                <div class="card card-animate">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="text-muted small fw-semibold">Pending Ticket</span>
                                                <h3 class="fw-bold mb-0 mt-1 text-warning">12</h3>
                                            </div>
                                            <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                                                <i class="ti ti-clock fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="mt-2 small text-muted">
                                            <span class="text-danger fw-semibold"><i class="ti ti-alert-circle me-1"></i></span> 3 High Priority awaiting tech
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Work In Progress -->
                            <div class="col-md-4">
                                <div class="card card-animate">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="text-muted small fw-semibold">In Progress / Working</span>
                                                <h3 class="fw-bold mb-0 mt-1 text-info">24</h3>
                                            </div>
                                            <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                                                <i class="ti ti-progress fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="mt-2 small text-muted">
                                            <span class="text-info fw-semibold"><i class="ti ti-user-check me-1"></i></span> Assigned to IT personnel
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Resolved / Closed -->
                            <div class="col-md-4">
                                <div class="card card-animate">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="text-muted small fw-semibold">Resolved</span>
                                                <h3 class="fw-bold mb-0 mt-1 text-success">312</h3>
                                            </div>
                                            <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                                                <i class="ti ti-circle-check fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="mt-2 small text-muted">
                                            <span class="text-success fw-semibold"><i class="ti ti-check me-1"></i></span> 89.6% resolution rate
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. Closed -->
                            <div class="col-md-4">
                                <div class="card card-animate">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="text-muted small fw-semibold">Closed</span>
                                                <h3 class="fw-bold mb-0 mt-1 text-danger">312</h3>
                                            </div>
                                            <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
                                                <i class="ti ti-circle-check fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="mt-2 small text-muted">
                                            <span class="text-danger fw-semibold"><i class="ti ti-check me-1"></i>89.6%</span> resolution rate
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 6. Total Request -->
                            <div class="col-md-4">
                                <div class="card card-animate">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="text-muted small fw-semibold">Total Requests</span>
                                                <h3 class="fw-bold mb-0 mt-1">348</h3>
                                            </div>
                                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                                                <i class="ti ti-ticket fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="mt-2 small text-muted">
                                            <span class="text-success fw-semibold"><i class="ti ti-trending-up me-1"></i>+12%</span> vs last month
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="card card-animate">
                                    <div class="card-header pt-3">
                                        <h6 class="card-title">
                                            <i class="ti ti-calendar"></i> Daily Tickets Created vs Resolved
                                        </h6>
                                    </div>
                                    <div class="card-body"></div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="card card-animate">
                                    <div class="card-header pt-3">
                                        <h6 class="card-title">
                                            <i class="ti ti-calendar"></i> Predicted Ticket Volume for the Next 5 Working Days
                                        </h6>
                                    </div>
                                    <div class="card-body"></div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="card card-animate">
                                    <div class="card-header pt-3">
                                        <h6 class="card-title">
                                            <i class="ti ti-building"></i> Top 10 Offices/Colleges
                                        </h6>
                                    </div>
                                    <div class="card-body"></div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="card card-animate">
                                    <div class="card-header pt-3">
                                        <h6 class="card-title">
                                            <i class="ti ti-building"></i> Average Ticket Performance of MIS Personnel
                                        </h6>
                                    </div>
                                    <div class="card-body"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Heatmap & Hero Styles -->
    <style>
        .welcome-hero-card {
            background: linear-gradient(135deg, rgba(3, 255, 137, 0.04) 0%, rgba(13, 253, 173, 0.12) 100%);
            border: 1px solid rgba(13, 165, 253, 0.15) !important;
        }

        [data-bs-theme="dark"] .welcome-hero-card {
            background: linear-gradient(135deg, hsla(0, 5%, 85%, 0.102) 0%, rgba(92, 93, 93, 0.2) 100%);
            border-color: #343a40 !important;
        }

        [data-bs-theme="dark"] .welcome-hero-card p{
            color: #d0dbe6 !important;
        }

        [data-bs-theme="dark"] .dark-text-light {
            color: #f8f9fa !important;
        }

        .activity-heatmap {
            min-width: 720px;
        }

        .heatmap-grid-wrapper {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .days-label {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-size: 0.65rem;
            height: 95px;
            padding-bottom: 1px;
        }

        .heatmap-months-container {
            display: flex;
            gap: 8px; /* Gap between month columns */
            flex-grow: 1;
        }

        .heatmap-month-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .month-header {
            font-size: 0.72rem;
            text-align: left;
        }

        .heatmap-month-weeks {
            display: flex;
            gap: 3px;
        }

        .heatmap-week {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .heatmap-cell {
            width: 11px;
            height: 11px;
            border-radius: 2px;
            background-color: #ebedf0;
            cursor: pointer;
        }

        .heatmap-cell.level-empty {
            visibility: hidden; /* Keeps week column alignment clean without rendering empty squares */
        }

        [data-bs-theme="dark"] .heatmap-cell {
            background-color: #161b22;
        }

        .heatmap-cell.level-0 { background-color: var(--bs-border-color, #ebedf0); }
        .heatmap-cell.level-1 { background-color: #0e4429; }
        .heatmap-cell.level-2 { background-color: #006d32; }
        .heatmap-cell.level-3 { background-color: #26a641; }
        .heatmap-cell.level-4 { background-color: #39d353; }

        /* Timeframe Navigation Pills */
        .nav-pills-custom {
            background-color: var(--bs-light, #f8f9fa);
            padding: 3px;
            border-radius: 10px;
        }
        [data-bs-theme="dark"] .nav-pills-custom {
            background-color: #2f3338;
        }
        .nav-pills-custom .nav-link {
            border-radius: 10px;
            font-size: 0.72rem;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
        }
        .nav-pills-custom .nav-link.active {
            background-color: #65ab85;
            color: var(--bs-dark, #212529) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
        }

        /* Podium Items & Crown */
        .podium-wrapper {
            background-color: #17171708;
        }
        [data-bs-theme="dark"] .podium-wrapper {
            background-color: #ffffff08;
        }
        [data-bs-theme="dark"] .podium-wrapper h6 {
            color: #ffffff !important;
        }
        .podium-item-top {
            transform: translateY(-20px);
        }
        .crown-icon {
            margin-top: 5px;
            filter: drop-shadow(0 2px 4px rgba(255, 193, 7, 0.4));
        }

        /* Avatar Styles */
        .avatar-podium {
            margin-top: 20px;
            width: 48px;
            height: 48px;
            font-size: 0.85rem;
        }
        .avatar-podium-lg {
            width: 58px;
            height: 58px;
            font-size: 1.05rem;
        }

        /* Overlapping Rank Badge */
        .badge-rank {
            width: 20px;
            height: 20px;
            font-size: 0.65rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            bottom: -6px !important;
            box-shadow: 0 0 0 2px var(--bs-card-bg, #fff);
        }

        /* List Items Avatar */
        .avatar-circle-xs {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
        }

        /* List Group Item */
        .list-group-item {
            background-color: #17171708;
        }
        [data-bs-theme="dark"] .list-group-item {
            background-color: #ffffff08;
            color: #ffffff
        }
    </style>


@endsection
