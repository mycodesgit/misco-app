@php
    $isRequester = Auth::guard('web')->user()->role == 'Requester';
    $navDash = request()->routeIs('dashboard.index') ? 'active' : '';
    $navTickets = request()->routeIs('tickets.index', 'tickets.store') && !$isRequester ? 'active' : '';
    $navMyTickets = request()->routeIs('ticketsrequester.index', 'tickets.store') && $isRequester ? 'active' : '';
    $navDailyTask = request()->routeIs('daily-task.index') ? 'active' : '';
    $navProjects = request()->routeIs('project.index') ? 'active' : '';
@endphp

<nav class="mobile-bottom-nav" aria-label="Primary">
    <a href="{{ route('dashboard.index') }}" class="mbn-item {{ $navDash }}">
        <i class="ti ti-layout-grid"></i>
        <span>Home</span>
    </a>
    @if ($isRequester)
        <a href="{{ route('ticketsrequester.index') }}" class="mbn-item {{ $navMyTickets }}">
            <i class="ti ti-ticket"></i>
            <span>Tickets</span>
        </a>
    @else
        <a href="{{ route('tickets.index') }}" class="mbn-item {{ $navTickets }}">
            <i class="ti ti-ticket"></i>
            <span>Tickets</span>
        </a>
        <a href="{{ route('daily-task.index') }}" class="mbn-item {{ $navDailyTask }}">
            <i class="ti ti-calendar"></i>
            <span>Tasks</span>
        </a>
        <a href="{{ route('project.index') }}" class="mbn-item {{ $navProjects }}">
            <i class="ti ti-chart-line"></i>
            <span>Projects</span>
        </a>
    @endif
</nav>
