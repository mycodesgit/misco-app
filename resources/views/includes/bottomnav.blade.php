@php
    $isDeskStaff = Auth::guard('web')->user()->isDeskStaff();
    // An open ticket highlights "Tickets" when the user handles it, "Requests" when they filed it
    $viewingTicket = request()->routeIs('tickets.store');
    $navDash = request()->routeIs('dashboard.index') ? 'active' : '';
    $navTickets = (request()->routeIs('tickets.index') || ($viewingTicket && !empty($canHandle))) ? 'active' : '';
    $navMyTickets = (request()->routeIs('ticketsrequester.index') || ($viewingTicket && empty($canHandle))) ? 'active' : '';
    $navDailyTask = request()->routeIs('daily-task.index') ? 'active' : '';
    $navProjects = request()->routeIs('project.index') ? 'active' : '';
@endphp

<nav class="mobile-bottom-nav" aria-label="Primary">
    <a href="{{ route('dashboard.index') }}" class="mbn-item {{ $navDash }}">
        <i class="ti ti-layout-grid"></i>
        <span>Home</span>
    </a>
    @if ($isDeskStaff)
        <a href="{{ route('tickets.index') }}" class="mbn-item {{ $navTickets }}">
            <i class="ti ti-ticket"></i>
            <span>Tickets</span>
        </a>
    @endif
    {{-- Everyone can request support --}}
    <a href="{{ route('ticketsrequester.index') }}" class="mbn-item {{ $navMyTickets }}">
        <i class="ti {{ $isDeskStaff ? 'ti-send' : 'ti-ticket' }}"></i>
        <span>{{ $isDeskStaff ? 'Requests' : 'Tickets' }}</span>
    </a>
    @if ($isDeskStaff)
        @if (Auth::guard('web')->user()->hasDailyTaskAccess())
            <a href="{{ route('daily-task.index') }}" class="mbn-item {{ $navDailyTask }}">
                <i class="ti ti-calendar"></i>
                <span>Tasks</span>
            </a>
        @endif
        @if (Auth::guard('web')->user()->hasWorkProgressAccess())
            <a href="{{ route('project.index') }}" class="mbn-item {{ $navProjects }}">
                <i class="ti ti-chart-line"></i>
                <span>Projects</span>
            </a>
        @endif
    @endif
</nav>
