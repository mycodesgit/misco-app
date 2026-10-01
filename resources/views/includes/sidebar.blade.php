@php
    $current_route=request()->route()->getName();

    $dashActive = in_array($current_route, ['dashboard.index']) ? 'active' : '';
    $ticketsActive = in_array($current_route, ['tickets.index', 'tickets.store']) ? 'active' : '';
    $dailyTaskActive = in_array($current_route, ['daily-task.index']) ? 'active' : '';
    $categoryActive = in_array($current_route, ['category.index']) ? 'active' : '';
    $officeActive = in_array($current_route, ['office.index']) ? 'active' : '';
    $usersAllActive = in_array($current_route, ['user.index']) ? 'active' : '';
@endphp

<ul class="nav flex-column">
    <li class="px-4 py-2">
        <small class="nav-text text-muted">Main Navigation</small>
    </li>
    <li>
        <a class="nav-link {{ $dashActive }}" href="{{ route('dashboard.index') }}" data-tooltip="Dashboard">
            <i class="ti ti-layout-grid"></i><span class="nav-text">Dashboard</span>
        </a>
    </li>
    <li>
        <a class="nav-link {{ $ticketsActive }}" href="{{ route('tickets.index') }}" data-tooltip="All Tickets">
            <i class="ti ti-ticket"></i><span class="nav-text">All Tickets</span>
        </a>
    </li>
    <li>
        <a class="nav-link {{ $dailyTaskActive }}" href="{{ route('daily-task.index') }}" data-tooltip="Daily Task">
            <i class="ti ti-calendar"></i><span class="nav-text">Daily Task</span>
        </a>
    </li>

    <li class="px-4 py-2">
        <small class="nav-text text-muted">Control Management</small>
    </li>
    <li>
        <a class="nav-link {{ $officeActive }}" href="{{ route('office.index') }}" data-tooltip="Offices">
            <i class="ti ti-building"></i><span class="nav-text">Offices</span>
        </a>
    </li>
    <li>
        <a class="nav-link {{ $categoryActive }}" href="{{ route('category.index') }}" data-tooltip="Categories">
            <i class="ti ti-server"></i><span class="nav-text">Categories</span>
        </a>
    </li>

    <li class="px-4 py-2">
        <small class="nav-text text-muted">Reports Generation</small>
    </li>
    <li>
        <a class="nav-link" href="#" data-tooltip="Accomplishment">
            <i class="ti ti-file-type-pdf"></i><span class="nav-text">Accomplishment</span>
        </a>
    </li>
    <li>
        <a class="nav-link" href="#" data-tooltip="Client Satisfactory">
            <i class="ti ti-forms"></i><span class="nav-text">Client Satisfactory</span>
        </a>
    </li>
    <li>
        <a class="nav-link" href="#" data-tooltip="Client Feedback">
            <i class="ti ti-file-report"></i><span class="nav-text">Client Feedback</span>
        </a>
    </li>

    <li class="px-4 py-2">
        <small class="nav-text text-muted">User Management</small>
    </li>
    <li>
        <a class="nav-link {{$usersAllActive}}" href="{{ route('user.index') }}" data-tooltip="Users">
            <i class="ti ti-users"></i><span class="nav-text">Users</span>
        </a>
    </li>
</ul>
