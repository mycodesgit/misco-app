<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>@yield('title')</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('uilibs/images/cpsulogov4.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('uilibs/images/cpsulogov4.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('uilibs/images/cpsulogov4.png') }}">

    <link rel="stylesheet" href="{{ asset('uilibs/css/main.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('uilibs/css/custom.css') }}?v={{ time() }}">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ asset('uilibs/plugins/fontawesome-free-V6/css/all.min.css') }}">
    <!-- Toastr -->
    <link rel="stylesheet" href="{{ asset('uilibs/plugins/toastr/toastr.min.css') }}">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('uilibs/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('uilibs/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('uilibs/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <!-- daterange picker -->
    <link rel="stylesheet" href="{{ asset('uilibs/plugins/daterangepicker/daterangepicker.css') }}">
    <!-- DataTables  -->
    <link rel="stylesheet" href="{{ asset('uilibs/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('uilibs/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('uilibs/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- fullCalendar -->
    <link rel="stylesheet" href="{{ asset('uilibs/plugins/fullcalendar/fullcalendar.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemPrefersLight = window.matchMedia('(prefers-color-scheme: light)').matches;
            const theme = savedTheme || (systemPrefersLight ? 'light' : 'dark');
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
</head>

<body>
    <div id="overlay" class="overlay"></div>
    <!-- TOPBAR -->
    <nav id="topbar" class="navbar bg-white border-bottom fixed-top topbar px-3 full">
        <button id="toggleBtn" class="d-none d-lg-inline-flex btn btn-light btn-icon btn-sm ">
            <i class="fas fa-bars"></i>
        </button>

        <!-- MOBILE -->
        <button id="mobileBtn" class="btn btn-light btn-icon btn-sm d-lg-none me-2">
            <i class="ti ti-layout-sidebar-left-expand"></i>
        </button>
        <div>
            <!-- Navbar nav -->
            <ul class="list-unstyled d-flex align-items-center mb-0 gap-1">
                <!-- Ticket notifications -->
                <li class="nav-item dropdown">
                    <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" class="position-relative btn-icon btn-sm btn-light btn rounded-circle" title="Ticket notifications">
                        <i class="ti ti-bell"></i>
                        <span id="notifTicketBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none; font-size:0.6rem;">0</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end p-0" style="width: 340px;">
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                            <strong class="small">Ticket Notifications</strong>
                            <button type="button" class="btn btn-link btn-sm p-0 small notif-mark-all" data-kind="ticket">Mark all read</button>
                        </div>
                        <ul id="notifTicketList" class="list-unstyled p-0 m-0" style="max-height: 320px; overflow-y: auto;">
                            <li class="px-3 py-3 text-center text-muted small">Loading...</li>
                        </ul>
                    </div>
                </li>
                <!-- Chat notifications -->
                <li class="nav-item dropdown">
                    <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" class="position-relative btn-icon btn-sm btn-light btn rounded-circle" title="Chat messages">
                        <i class="ti ti-message"></i>
                        <span id="notifChatBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success" style="display:none; font-size:0.6rem;">0</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end p-0" style="width: 340px;">
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                            <strong class="small">Chat Messages</strong>
                            <button type="button" class="btn btn-link btn-sm p-0 small notif-mark-all" data-kind="chat">Mark all read</button>
                        </div>
                        <ul id="notifChatList" class="list-unstyled p-0 m-0" style="max-height: 320px; overflow-y: auto;">
                            <li class="px-3 py-3 text-center text-muted small">Loading...</li>
                        </ul>
                    </div>
                </li>
                <li>
                    <label class="theme-switch" for="themeToggle">
                        <input type="checkbox" id="themeToggle">
                        <span class="slider">
                            <span class="slider-content">
                                <i id="themeIcon" class="ti ti-moon"></i>
                                <span id="themeLabel">Dark</span>
                            </span>
                        </span>
                    </label>
                </li>
                <li class="ms-3 dropdown">
                    <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ asset('uilibs/images/user.png') }}" alt="" class="avatar avatar-sm rounded-circle" />
                    </a>
                    <div class="dropdown-menu dropdown-menu-end p-0" style="min-width: 200px;">
                        <div>
                            <div class="d-flex gap-3 align-items-center border-dashed border-bottom px-3 py-3">
                                <img src="{{ asset('uilibs/images/user.png') }}" alt="" class="avatar avatar-md rounded-circle" />
                                <div>
                                    <h5 class="mb-0 small"></h5>
                                    {{ Auth::guard('web')->user()->fname }} {{ Auth::guard('web')->user()->lname }}
                                </div>
                            </div>
                            <div class="p-3 d-flex flex-column gap-1 medium lh-lg">
                                <a href="{{ route('account.index') }}" class="text-secondary">
                                    <i class="ti ti-user-cog"></i> <span>Account Settings</span>
                                </a>
                                <a href="#" class="text-danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="ti ti-logout"></i><span> Signout</span>
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                                    @csrf
                                </form>
                            </div>

                        </div>
                    </div>
                </li>
            </ul>
        </div>

    </nav>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="sidebar collapsed">
        <div class="logo-area">
            <div class="d-inline-flex">
                <img src="{{ asset('uilibs/images/cpsulogov4.png') }}" alt="logo" width="24">
                <span class="logo-text ms-2" style="font-weight: bold">Ticketing</span>
            </div>
        </div>
        @include('includes.sidebar')

    </aside>

    <!-- MAINmainCONTENT -->
    <main id="content" class="content py-10 full">
        <div class="container-fluid">
            @yield('body')

            <div class="row">
                <div class="col-12">
                    <footer class="text-center py-2 mt-6 text-secondary fixed-bottom bg-white" style="z-index: 99">
                        <p class="mb-0"></p>
                    </footer>
                </div>
            </div>

        </div>
    </main>

    <!-- Bootstrap JS -->

    <script type="text/javascript" src="{{ asset('uilibs/js/main.js') }}"></script>
    <!-- jQuery -->
    <script src="{{ asset('uilibs/plugins/jquery/jquery.min.js') }}"></script>

    <!-- DataTables  & Plugins -->
    <script src="{{ asset('uilibs/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('uilibs/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('uilibs/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('uilibs/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('uilibs/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('uilibs/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('uilibs/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('uilibs/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('uilibs/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('uilibs/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('uilibs/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('uilibs/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
    <!-- fullCalendar 2.2.5 -->
    <script src="{{ asset('uilibs/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('uilibs/plugins/fullcalendar/fullcalendar.js') }}"></script>
    <!-- SweetAlert2 -->
    <script src="{{ asset('uilibs/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- Toastr -->
    <script src="{{ asset('uilibs/plugins/toastr/toastr.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('uilibs/plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- ChartJS -->
    <script src="{{ asset('uilibs/plugins/chart.js/Chart.min.js') }}"></script>
    <!-- date-range-picker -->
    <script src="{{ asset('uilibs/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <!-- Validation JS -->
    <script src="{{ asset('uilibs/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('uilibs/plugins/jquery-validation/additional-methods.min.js') }}"></script>
    <!-- Theme -->
    <script src="{{ asset('uilibs/js/theme.js') }}"></script>
    @include('script.notify.topbarnotifyjs')

    <script>
        $(function () {
            $('.select2').each(function () {
                $(this).select2({
                    dropdownParent: $(this).closest('.modal'),
                });
            });

            $('.select2bs4').each(function () {
                const $select = $(this);
                const $modal = $select.closest('.modal');

                const options = {
                    theme: 'bootstrap4',
                    width: '100%'
                };

                // Only set dropdownParent when inside a modal
                if ($modal.length) {
                    options.dropdownParent = $modal;
                }

                $select.select2(options);
            });
        });
        document.addEventListener("DOMContentLoaded", function () {
            const cards = document.querySelectorAll('.card-animate');

            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('show');
                }, index * 90); // stagger effect
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Initialize DateRangePicker
            $('#reservation').daterangepicker({
                locale: {
                    format: 'YYYY-MM-DD'
                },
                opens: 'right',
                autoApply: true,
                autoUpdateInput: true,
            });

            // Handle Form Submission to update iframe src
            $('#reportForm').submit(function(event) {
                event.preventDefault();

                var dateRange = $('#reservation').val();
                if (!dateRange) {
                    toastr.warning('Please select a date range.');
                    return;
                }

                // Split daterangepicker output "YYYY-MM-DD - YYYY-MM-DD"
                var dates = dateRange.split(' - ');
                var startDate = dates[0];
                var endDate = dates[1];

                // Build PDF view route with parameters
                var pdfUrl = "{{ route('accomplishment.preview') }}" + "?start_date=" + startDate + "&end_date=" + endDate;

                // Show PDF Container, Hide Placeholder
                $('#noReportIcon').hide();
                $('#pdfContainer').show();

                // Set Iframe src
                $('#pdfIframe').attr('src', pdfUrl);
            });
        });
    </script>


    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>

    @if (request()->routeIs('dashboard.index'))
        @include('script.dash.dashboardjs')
        @include('script.dash.dashaddticketjs')
    @endif
    @if (request()->routeIs('tickets.index'))
        @include('script.ticket.supportjs')
    @endif
    @if (request()->routeIs('tickets.store'))
        @include('script.ticket.ticketchatjs')
    @endif
    @if (request()->routeIs('ticketsrequester.index'))
        @include('script.ticket.requesterjs')
    @endif
    @if (request()->routeIs('daily-task.index'))
        @include('script.daily.taskjs')
    @endif
    @if (request()->routeIs('category.index'))
        @include('script.mnge.categoryjs')
        @include('script.mnge.categorysubjs')
    @endif
    @if (request()->routeIs('office.index'))
        @include('script.mnge.officejs')
    @endif
    @if (request()->routeIs('audittrail.index'))
        @include('script.mnge.audittrailjs')
    @endif
    @if (request()->routeIs('user.index'))
        @include('script.usr.userjs')
    @endif
    @if (request()->routeIs('requester.index'))
        @include('script.usr.userjs')
    @endif
    @if (request()->routeIs('monitoring-dashboard.index', 'monitoring.index'))
        @include('script.tvmonitoring.helpdeskjs')
    @endif
    @if (request()->routeIs('roles.index'))
        @include('script.usr.rolejs')
    @endif
    @if (request()->routeIs('account.index'))
        @include('script.usr.accountjs')
    @endif
    @if (request()->routeIs('clientfeedback.index'))
        @include('script.report.clientfeedbackjs')
    @endif

</body>
</html>
