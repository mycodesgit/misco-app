@php
    $current_route=request()->route()->getName();

    $dashActive = in_array($current_route, ['dashboard.index']) ? 'active' : '';
@endphp

<ul class="nav flex-column">
    <li class="px-4 py-2">
        <small class="nav-text text-muted">Main Navigation</small>
    </li>
    <li>
        <a class="nav-link {{ $dashActive }}" href="{{ route('dashboard.index') }}">
            <i class="ti ti-layout-grid"></i><span class="nav-text">Dashboard</span>
        </a>
    </li>
</ul>
