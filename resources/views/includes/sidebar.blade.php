@php
    $current_route=request()->route()->getName();

    $dashActive = in_array($current_route, ['dashboard.index']) ? 'active' : '';
    $projectActive = in_array($current_route, ['project.index']) ? 'active' : '';
    $isDeskStaff = Auth::guard('web')->user()->isDeskStaff();
    $isAdmin = Auth::guard('web')->user()->isAdmin();
    // Feature access, see User::hasFeatureAccess() — the routes enforce the same rules
    $hasDailyTaskAccess = Auth::guard('web')->user()->hasDailyTaskAccess();       // Daily Task + Accomplishment
    $hasWorkProgressAccess = Auth::guard('web')->user()->hasWorkProgressAccess(); // Work Progress
    $hasCalendarAccess = Auth::guard('web')->user()->hasCalendarAccess();         // Calendar
    $hasWifiVoucherAccess = Auth::guard('web')->user()->hasWifiVoucherAccess();   // Wifi Vouchers

    // An open ticket (tickets.store) belongs under "All Tickets" when the user
    // handles it, and under "My Requests" when they filed it
    $viewingHandledTicket = $current_route === 'tickets.store' && !empty($canHandle);
    $viewingOwnTicket = $current_route === 'tickets.store' && empty($canHandle);

    $ticketsActive = ($current_route === 'tickets.index' || $viewingHandledTicket) ? 'active' : '';
    $dailyTaskActive = in_array($current_route, ['daily-task.index']) ? 'active' : '';
    $categoryActive = in_array($current_route, ['category.index']) ? 'active' : '';
    $officeActive = in_array($current_route, ['office.index']) ? 'active' : '';
    $wifiActive = in_array($current_route, ['wifi.index']) ? 'active' : '';
    $auditActive = in_array($current_route, ['audittrail.index']) ? 'active' : '';
    $accomplishmentActive = in_array($current_route, ['accomplishment-report.index']) ? 'active' : '';
    $ticketstatusActive = in_array($current_route, ['ticketstatus-report.index']) ? 'active' : '';
    $clientfeedbackActive = in_array($current_route, ['clientfeedback.index']) ? 'active' : '';
    $usersAllActive = in_array($current_route, ['user.index']) ? 'active' : '';
    $requesterAllActive = in_array($current_route, ['requester.index']) ? 'active' : '';
    $rolesAllActive = in_array($current_route, ['roles.index']) ? 'active' : '';

    $ticketsrequesterActive = ($current_route === 'ticketsrequester.index' || $viewingOwnTicket) ? 'active' : '';
    $archiveActive = $current_route === 'tickets.archive' ? 'active' : '';
    $calendarActive = $current_route === 'calendar.index' ? 'active' : '';
@endphp

<ul class="nav flex-column">
    <li class="nav-section px-4 py-2">
        <small class="nav-text text-muted">Main Navigation</small>
    </li>
    <li>
        <a class="nav-link {{ $dashActive }}" href="{{ route('dashboard.index') }}">
            <i class="ti ti-layout-grid"></i><span class="nav-text">Dashboard</span>
        </a>
    </li>
    @if($isDeskStaff)
        {{-- Tickets filed to this user's desk --}}
        <li>
            <a class="nav-link {{ $ticketsActive }}" href="{{ route('tickets.index') }}">
                <i class="ti ti-ticket"></i><span class="nav-text">All Tickets</span>
            </a>
        </li>
    @endif
    {{-- Tickets this user filed to any desk — everyone can request support --}}
    <li>
        <a class="nav-link {{ $ticketsrequesterActive }}" href="{{ route('ticketsrequester.index') }}">
            <i class="ti ti-send"></i><span class="nav-text">My Requests</span>
        </a>
    </li>
    {{-- Finished tickets from past years — every role --}}
    <li>
        <a class="nav-link {{ $archiveActive }}" href="{{ route('tickets.archive') }}">
            <i class="ti ti-archive"></i><span class="nav-text">Archive</span>
        </a>
    </li>
    @if($isDeskStaff)
        @if($hasDailyTaskAccess)
            <li>
                <a class="nav-link {{ $dailyTaskActive }}" href="{{ route('daily-task.index') }}">
                    <i class="ti ti-calendar"></i><span class="nav-text">Daily Task</span>
                </a>
            </li>
        @endif
        @if($hasWorkProgressAccess)
            <li>
                <a class="nav-link {{ $projectActive }}" href="{{ route('project.index') }}">
                    <i class="ti ti-chart-line"></i><span class="nav-text">Work Progress</span>
                </a>
            </li>
        @endif
    @endif
    {{-- Office's shared Google Calendar --}}
    @if($hasCalendarAccess)
        <li>
            <a class="nav-link {{ $calendarActive }}" href="{{ route('calendar.index') }}">
                <i class="ti ti-calendar-event"></i><span class="nav-text">Calendar</span>
            </a>
        </li>
    @endif

    {{-- Administrators manage everything here; each desk manages its own
         categories; Wifi Vouchers: Administrators + one user --}}
    @if($isDeskStaff || $hasWifiVoucherAccess)
        <li class="nav-section px-4 py-2">
            <small class="nav-text text-muted">Control Management</small>
        </li>
        @if($isAdmin)
            <li>
                <a class="nav-link {{ $officeActive }}" href="{{ route('office.index') }}">
                    <i class="ti ti-building"></i><span class="nav-text">Offices</span>
                </a>
            </li>
        @endif
        @if($isDeskStaff)
            <li>
                <a class="nav-link {{ $categoryActive }}" href="{{ route('category.index') }}">
                    <i class="ti ti-server"></i><span class="nav-text">Categories</span>
                </a>
            </li>
        @endif
        @if($hasWifiVoucherAccess)
            <li>
                <a class="nav-link {{ $wifiActive }}" href="{{ route('wifi.index') }}">
                    <i class="ti ti-wifi"></i><span class="nav-text">Wifi Vouchers</span>
                </a>
            </li>
        @endif
        @if($isAdmin)
            <li>
                <a class="nav-link {{ $auditActive }}" href="{{ route('audittrail.index') }}">
                    <i class="ti ti-history"></i><span class="nav-text">Audit Trail</span>
                </a>
            </li>
        @endif
    @endif

    @if($isDeskStaff)
        <li class="nav-section px-4 py-2">
            <small class="nav-text text-muted">Reports Generation</small>
        </li>
        @if($hasDailyTaskAccess)
            <li>
                <a class="nav-link {{ $accomplishmentActive }}" href="{{ route('accomplishment-report.index') }}">
                    <i class="ti ti-file-type-pdf"></i><span class="nav-text">Accomplishment</span>
                </a>
            </li>
        @endif
        <li>
            <a class="nav-link {{ $ticketstatusActive }}" href="{{ route('ticketstatus-report.index') }}">
                <i class="ti ti-report-analytics"></i><span class="nav-text">Ticket Status</span>
            </a>
        </li>
        <li>
            <a class="nav-link" href="#">
                <i class="ti ti-forms"></i><span class="nav-text">Client Satisfactory</span>
            </a>
        </li>
        <li>
            <a class="nav-link {{ $clientfeedbackActive }}" href="{{ route('clientfeedback.index') }}">
                <i class="ti ti-file-report"></i><span class="nav-text">Client Feedback</span>
            </a>
        </li>
    @endif

    @if(Auth::guard('web')->user()->role == 'Administrator')
        <li class="nav-section px-4 py-2">
            <small class="nav-text text-muted">User Management</small>
        </li>
        <li>
            <a class="nav-link {{$usersAllActive}}" href="{{ route('user.index') }}">
                <i class="ti ti-user-cog"></i><span class="nav-text">{{ auth()->user()->pageTitle(' Support Team') }}</span>
            </a>
        </li>
        <li>
            <a class="nav-link {{ $requesterAllActive }}" href="{{ route('requester.index') }}">
                <i class="ti ti-users"></i><span class="nav-text">Requester</span>
            </a>
        </li>
        <li>
            <a class="nav-link {{ $rolesAllActive }}" href="{{ route('roles.index') }}">
                <i class="ti ti-id-badge"></i><span class="nav-text">User Roles</span>
            </a>
        </li>
    @endif
</ul>
