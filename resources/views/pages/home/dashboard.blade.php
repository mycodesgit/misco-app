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
                        <form id="yearForm" class="d-flex align-items-center gap-2">
                            <label for="yearSelect" class="form-label mb-0 small text-muted">Year:</label>
                            <select id="yearSelect" name="year" class="form-select form-select-sm">
                                @php $selectedYear = request('year', date('Y')); @endphp
                                @for ($y = date('Y'); $y >= date('Y') - 4; $y--)
                                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </form>
                    </div>
                </div>

                <!-- Stats & Welcome Grid -->
                <div class="row g-3 mb-4">
                    @if(Auth::guard('web')->user()->role != 'Requester')
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
                                            Welcome, {{ auth()->user()->fname }} {{ auth()->user()->lname }}!
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
                                            <i class="fas fa-square me-1 text-success style-dot blink-dot"></i> Total: <strong>{{ array_sum($supportHeatmapData ?? []) }}</strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body" id="supportprogress">
                                    @php
                                        $monthsData = [];
                                        for ($m = 1; $m <= 12; $m++) {
                                            $monthStart = \Carbon\Carbon::createFromDate($selectedYear, $m, 1);
                                            $monthEnd = $monthStart->copy()->endOfMonth();
                                            $curr = $monthStart->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                                            $weeks = [];

                                            while ($curr->lte($monthEnd)) {
                                                $weekDays = [];
                                                for ($d = 0; $d < 7; $d++) {
                                                    if ($curr->month == $m && $curr->year == $selectedYear) {
                                                        $weekDays[] = [
                                                            'date' => $curr->format('M j, Y'),
                                                            'db_date' => $curr->format('Y-m-d'),
                                                            'day_idx' => $curr->dayOfWeekIso,
                                                            'active' => true
                                                        ];
                                                    } else {
                                                        $weekDays[] = [
                                                            'date' => null,
                                                            'db_date' => null,
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
                                                            <div class="month-header text-muted small fw-semibold">
                                                                {{ $month['name'] }}
                                                            </div>

                                                            <div class="heatmap-month-weeks">
                                                                @foreach ($month['weeks'] as $week)
                                                                    <div class="heatmap-week">
                                                                        @foreach ($week as $day)
                                                                            @if ($day['active'])
                                                                                @php
                                                                                    $taskCount = $supportHeatmapData[$day['db_date']] ?? 0;
                                                                                    $level = 0;
                                                                                    if ($taskCount > 0 && $taskCount <= 2) $level = 1;
                                                                                    elseif ($taskCount > 2 && $taskCount <= 5) $level = 2;
                                                                                    elseif ($taskCount > 5 && $taskCount <= 9) $level = 3;
                                                                                    elseif ($taskCount > 9) $level = 4;
                                                                                @endphp
                                                                                <div class="heatmap-cell level-{{ $level }}"
                                                                                    data-date="{{ $day['db_date'] }}"
                                                                                    data-bs-toggle="tooltip"
                                                                                    data-bs-placement="top"
                                                                                    title="{{ $taskCount }} {{ Str::plural('task', $taskCount) }} resolved on {{ $day['date'] }}">
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
                        <!-- Leaderboard & Category Section -->
                        <div class="col-md-4">
                            <!-- Leaderboard Card -->
                            <div class="card card-animate mb-3 overflow-hidden">
                                <div class="card-header pt-3 px-3 pb-2 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="p-2 bg-warning bg-opacity-10 rounded-3 text-warning d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="ti ti-trophy fs-6"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0">Leaderboard</h6>
                                    </div>
                                    <ul class="nav nav-pills nav-pills-custom gap-1" id="timeframeTabs">
                                        <li class="nav-item">
                                            <a class="nav-link timeframe-btn active px-2.5 py-1" data-timeframe="daily" href="#">Daily</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link timeframe-btn text-muted px-2.5 py-1" data-timeframe="monthly" href="#">Monthly</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link timeframe-btn text-muted px-2.5 py-1" data-timeframe="all_time" href="#">All time</a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="card-body p-3">
                                    <!-- Podium Section -->
                                    <div class="row align-items-end text-center mb-4 pt-3 pb-3 rounded-4 mx-0 podium-wrapper" id="leaderboard-podium">
                                        <!-- Rendered via JS -->
                                    </div>

                                    <div class="d-flex justify-content-between px-2 pb-2 text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                                        <span>Rank & Player</span>
                                        <span>Points</span>
                                    </div>

                                    <!-- Remaining List -->
                                    <div class="list-group list-group-flush gap-1" id="leaderboard-list">
                                        <!-- Rendered via JS -->
                                    </div>
                                </div>
                            </div>

                            <!-- Top Issue Categories Card -->
                            <div class="card card-animate">
                                <div class="card-header bg-transparent pt-3 border-bottom d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold small mb-0">
                                        <i class="ti ti-chart-pie me-1"></i> Top Ticket Categories
                                    </h6>
                                    <span class="badge bg-light text-dark border">Volume</span>
                                </div>
                                <div class="card-body p-3" id="top-categories-container">
                                    <!-- Rendered via JS -->
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
                                                    <h3 id="card-new-tickets" class="fw-bold mb-0 mt-1">{{ $metrics['newTickets'] ?? 0 }}</h3>
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
                                                    <h3 id="card-pending-tickets" class="fw-bold mb-0 mt-1 text-warning">{{ $metrics['pendingTickets'] ?? 0 }}</h3>
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
                                                    <h3 id="card-inprogress-tickets" class="fw-bold mb-0 mt-1 text-info">{{ $metrics['inProgressTickets'] ?? 0 }}</h3>
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
                                                    <h3 id="card-resolved-tickets" class="fw-bold mb-0 mt-1 text-success">{{ $metrics['resolvedTickets'] ?? 0 }}</h3>
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
                                                    <h3 id="card-closed-tickets" class="fw-bold mb-0 mt-1 text-danger">{{ $metrics['closedTickets'] ?? 0 }}</h3>
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
                                                    <h3 id="card-total-requests" class="fw-bold mb-0 mt-1">{{ $metrics['totalRequests'] ?? 0 }}</h3>
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
                                        <div class="card-body"  style="height: 350px; position: relative;">
                                            <canvas id="dailyTicketsBarChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <!-- Predicted Ticket Volume Card -->
                                <div class="col-md-12">
                                    <div class="card card-animate">
                                        <div class="card-header pt-3">
                                            <h6 class="card-title">
                                                <i class="ti ti-calendar"></i> Predicted Ticket Volume for the Next 5 Working Days
                                            </h6>
                                        </div>
                                        <div class="card-body" id="predicted-volume-container">
                                            <!-- Dynamically populated -->
                                        </div>
                                    </div>
                                </div>

                                <!-- Top 10 Offices/Colleges Card -->
                                <div class="col-md-12">
                                    <div class="card card-animate">
                                        <div class="card-header pt-3">
                                            <h6 class="card-title">
                                                <i class="ti ti-building"></i> Top 10 Offices/Colleges
                                            </h6>
                                        </div>
                                        <div class="card-body" id="top-offices-container">
                                            <!-- Dynamically populated -->
                                        </div>
                                    </div>
                                </div>

                                <!-- Average Ticket Performance Card -->
                                <div class="col-md-12">
                                    <div class="card card-animate">
                                        <div class="card-header pt-3">
                                            <h6 class="card-title">
                                                <i class="ti ti-user-check"></i> Average Ticket Performance of MIS Personnel
                                            </h6>
                                        </div>
                                        <div class="card-body" id="personnel-performance-container">
                                            <!-- Dynamically populated -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Welcome Hero Card (col-md-6) -->
                        <div class="col-md-4">
                            <div class="card overflow-hidden position-relative welcome-hero-card mb-3">
                                <div class="card-body p-4 d-flex flex-column justify-content-between position-relative z-1">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 small">
                                                <i class="bi bi-circle-fill me-1 small"></i> Active Session
                                            </span>
                                        </div>
                                        <h3 class="fw-bold mb-2">
                                            Welcome, {{ auth()->user()->fname }} {{ auth()->user()->lname }}! <br> How can we assist you today? 
                                        </h3>
                                        <p class="text-secondary mb-3 fs-6">
                                            Check your daily tasks, submit new tickets, and stay updated with the latest support activities.
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
                                <div class="hero-bg-accent position-absolute end-0 bottom-0 pointer-events-none me-3 mb-2"></div>
                            </div>

                            <div class="card card-animate">
                                <div class="card-header pt-3">
                                    <h6 class="card-title mb-0 fw-bold">Quick Actions</h6>
                                </div>
                                <div class="card-body d-flex flex-column gap-2">
                                    <a href="#" class="btn btn-primary text-start py-2 px-3 d-flex align-items-center justify-content-between">
                                        <span><i class="ti ti-plus me-2 fs-5"></i> New Ticket - Create a new support ticket</span>
                                        <i class="ti ti-chevron-right small"></i>
                                    </a>
                                    <a href="#" class="btn btn-success text-start py-2 px-3 d-flex align-items-center justify-content-between">
                                        <span><i class="ti ti-bulb me-2 fs-5"></i> Knowledge Base - Browse Help Articles & FAQs</span>
                                        <i class="ti ti-chevron-right small"></i>
                                    </a>
                                    <a href="#" class="btn btn-info text-start py-2 px-3 d-flex align-items-center justify-content-between">
                                        <span><i class="ti ti-help me-2 fs-5"></i> Get Help - Contact Support Team</span>
                                        <i class="ti ti-chevron-right small"></i>
                                    </a>
                                    <a href="#" class="btn btn-warning text-start py-2 px-3 d-flex align-items-center justify-content-between">
                                        <span><i class="ti ti-lock me-2 fs-5"></i> Self Reset Password - Institutional Email / MS Teams</span>
                                        <i class="ti ti-chevron-right small"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Activity Grid Card (Grouped by Month with Gaps) -->
                        <div class="col-md-8">
                            <div class="card card-animate">
                                <div class="card-header pt-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="fw-semibold">
                                            <i class="ti ti-device-laptop me-1"></i> Ticket Progress (Jan 1 - Dec 31, {{ $selectedYear }})
                                        </h6>
                                        {{-- <span class="spinner-grow spinner-grow-sm text-success me-2" role="status"></span> --}}
                                        <span class="small">
                                            <i class="fas fa-square me-1 text-success style-dot blink-dot"></i> Total: <strong>{{ array_sum($requesterHeatmapData ?? []) }}</strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body" id="requesterprogress">
                                    @php
                                        $monthsData = [];
                                        for ($m = 1; $m <= 12; $m++) {
                                            $monthStart = \Carbon\Carbon::createFromDate($selectedYear, $m, 1);
                                            $monthEnd = $monthStart->copy()->endOfMonth();
                                            $curr = $monthStart->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                                            $weeks = [];

                                            while ($curr->lte($monthEnd)) {
                                                $weekDays = [];
                                                for ($d = 0; $d < 7; $d++) {
                                                    if ($curr->month == $m && $curr->year == $selectedYear) {
                                                        $weekDays[] = [
                                                            'date' => $curr->format('M j, Y'),
                                                            'db_date' => $curr->format('Y-m-d'),
                                                            'day_idx' => $curr->dayOfWeekIso,
                                                            'active' => true
                                                        ];
                                                    } else {
                                                        $weekDays[] = [
                                                            'date' => null,
                                                            'db_date' => null,
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
                                                            <div class="month-header text-muted small fw-semibold">
                                                                {{ $month['name'] }}
                                                            </div>

                                                            <div class="heatmap-month-weeks">
                                                                @foreach ($month['weeks'] as $week)
                                                                    <div class="heatmap-week">
                                                                        @foreach ($week as $day)
                                                                            @if ($day['active'])
                                                                                @php
                                                                                    $taskCount = $requesterHeatmapData[$day['db_date']] ?? 0;
                                                                                    $level = 0;
                                                                                    if ($taskCount > 0 && $taskCount <= 2) $level = 1;
                                                                                    elseif ($taskCount > 2 && $taskCount <= 5) $level = 2;
                                                                                    elseif ($taskCount > 5 && $taskCount <= 9) $level = 3;
                                                                                    elseif ($taskCount > 9) $level = 4;
                                                                                @endphp
                                                                                <div class="heatmap-cell level-{{ $level }}"
                                                                                    data-date="{{ $day['db_date'] }}"
                                                                                    data-bs-toggle="tooltip"
                                                                                    data-bs-placement="top"
                                                                                    title="{{ $taskCount }} {{ Str::plural('task', $taskCount) }} resolved on {{ $day['date'] }}">
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
                    @endif
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
