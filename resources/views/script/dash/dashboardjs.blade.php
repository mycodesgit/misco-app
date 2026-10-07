@if(Auth::guard('web')->user()->role != 'Requester')
    <script>
        let dailyTicketsChart = null;
        let topOfficesChart = null;

        // --- LEADERBOARD RENDER FUNCTION ---
        function renderLeaderboard(leaderboardData) {
            let podiumContainer = $('#leaderboard-podium');
            let listContainer = $('#leaderboard-list');

            podiumContainer.empty();
            listContainer.empty();

            if (!leaderboardData || leaderboardData.length === 0) {
                podiumContainer.html('<div class="col-12 text-muted small py-3">No leaderboard data for this period.</div>');
                return;
            }

            let first = leaderboardData[0] || null;
            let second = leaderboardData[1] || null;
            let third = leaderboardData[2] || null;

            let podiumHTML = `
                <div class="col-4 px-1">
                    ${second ? `
                        <div class="position-relative d-inline-block mb-2">
                            <div class="avatar-podium bg-secondary bg-opacity-20 text-light rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto">
                                ${second.initials}
                            </div>
                            <span class="badge bg-info position-absolute start-50 translate-middle-x badge-rank rounded-circle">2</span>
                        </div>
                        <h6 class="fw-bold mb-0 text-truncate small">${second.short_name}</h6>
                        <span class="badge bg-light text-dark border small mt-1">${second.points} pts</span>
                    ` : '<div class="text-muted small py-4">-</div>'}
                </div>

                <div class="col-4 px-1 podium-item-top">
                    ${first ? `
                        <div class="position-relative d-inline-block mb-2">
                            <i class="ti ti-crown fs-4 text-warning crown-icon d-block"></i>
                            <div class="avatar-podium-lg bg-secondary text-light rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto border border-2 border-secondary">
                                ${first.initials}
                            </div>
                            <span class="badge bg-warning text-dark position-absolute start-50 translate-middle-x badge-rank rounded-circle fw-bold">1</span>
                        </div>
                        <h6 class="fw-bold mb-0 text-truncate">${first.short_name}</h6>
                        <span class="badge bg-light text-dark border small mt-1">${first.points} pts</span>
                    ` : '<div class="text-muted small py-4">-</div>'}
                </div>

                <div class="col-4 px-1">
                    ${third ? `
                        <div class="position-relative d-inline-block mb-2">
                            <div class="avatar-podium bg-secondary bg-opacity-20 text-light rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto">
                                ${third.initials}
                            </div>
                            <span class="badge bg-danger position-absolute start-50 translate-middle-x badge-rank rounded-circle">3</span>
                        </div>
                        <h6 class="fw-bold mb-0 text-truncate small">${third.short_name}</h6>
                        <span class="badge bg-light text-dark border small mt-1">${third.points} pts</span>
                    ` : '<div class="text-muted small py-4">-</div>'}
                </div>
            `;

            podiumContainer.html(podiumHTML);

            let remaining = leaderboardData.slice(3);
            if (remaining.length > 0) {
                remaining.forEach(item => {
                    let listItemHTML = `
                        <div class="list-group-item border-0 rounded-3 d-flex align-items-center justify-content-between p-2 mb-1 ${item.is_current_user ? 'bg-primary bg-opacity-10 border border-primary border-opacity-25' : ''}">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold text-muted small ms-1" style="width: 18px;">#${item.rank}</span>
                                <div class="avatar-circle-xs bg-secondary bg-opacity-10 text-secondary fw-semibold">
                                    ${item.initials}
                                </div>
                                <span class="fw-semibold small">${item.name} ${item.is_current_user ? '<span class="badge bg-primary text-white ms-1" style="font-size:0.55rem">YOU</span>' : ''}</span>
                            </div>
                            <span class="fw-bold small me-1">${item.points} pts</span>
                        </div>
                    `;
                    listContainer.append(listItemHTML);
                });
            }
        }

        function renderDailyTicketsChart(labels, createdData, resolvedData) {
            var ctx = document.getElementById('dailyTicketsBarChart').getContext('2d');

            if (dailyTicketsChart) {
                dailyTicketsChart.destroy();
            }

            dailyTicketsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Created Tickets',
                            data: createdData,
                            backgroundColor: '#e66239',
                            borderColor: '#e66239',
                            borderWidth: 1
                        },
                        {
                            label: 'Resolved Tickets',
                            data: resolvedData,
                            backgroundColor: '#65ab85',
                            borderColor: '#65ab85',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { ticks: { maxRotation: 45, minRotation: 45 } },
                        y: { beginAtZero: true, title: { display: true, text: 'Ticket Count' } }
                    }
                }
            });
        }

        function renderTopCategories(categories) {
            let html = '';
            if (!categories || categories.length === 0) {
                html = '<div class="text-center text-muted py-2">No data recorded</div>';
            } else {
                categories.forEach(cat => {
                    html += `
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-medium">${cat.name}</span>
                            <span class="badge bg-primary rounded-pill">${cat.total}</span>
                        </div>
                    `;
                });
            }
            $('#top-categories-container').html(html);
        }

        function renderMonitoring(items) {
            let html = '<div class="row text-center g-2">';
            if (!items || items.length === 0) {
                html += '<div class="text-muted">No monitoring data available</div>';
            } else {
                items.forEach(m => {
                    html += `
                        <div class="col">
                            <div class="p-2 border rounded card-body-bg-color">
                                <i class="ti ${m.icon} fs-5 ${m.color}"></i>
                                <span class="fw-bold fs-5 d-block">${m.value}</span>
                                <small class="d-block fw-semibold">${m.label}</small>
                                <small class="d-block text-muted" style="font-size: 0.7rem;">${m.sub}</small>
                            </div>
                        </div>
                    `;
                });
            }
            html += '</div>';
            $('#monitoring-container').html(html);
        }

        function renderTopOffices(offices) {
            var canvas = document.getElementById('topOfficesBarChart');
            if (!canvas) return;
            var ctx = canvas.getContext('2d');

            var sorted = (offices || []).slice().sort(function (a, b) {
                return (b.total || 0) - (a.total || 0);
            }).slice(0, 10);

            var labels = sorted.map(function (o) {
                return o.name || 'Unknown';
            });
            var values = sorted.map(function (o) { return o.total || 0; });

            if (topOfficesChart) {
                topOfficesChart.destroy();
            }

            topOfficesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Office/College',
                            data: values,
                            backgroundColor: '#4a5e5e',
                            borderColor: '#4a5e5e',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        xAxes: [{
                            ticks: { maxRotation: 45, minRotation: 45, autoSkip: false },
                            gridLines: { display: false }
                        }],
                        yAxes: [{
                            ticks: { beginAtZero: true, precision: 0 },
                            scaleLabel: { display: true, labelString: 'Ticket Count' }
                        }]
                    }
                }
            });
        }

        function renderPersonnelPerformance(personnel) {
            let html = '<div class="table-responsive"><table class="table table-sm table-hover align-middle mb-0"><thead><tr><th>Personnel</th><th>Resolved</th><th>Avg Duration</th></tr></thead><tbody>';
            if (!personnel || personnel.length === 0) {
                html += '<tr><td colspan="3" class="text-center text-muted">No performance records</td></tr>';
            } else {
                personnel.forEach(p => {
                    html += `
                        <tr>
                            <td><span class="fw-semibold small">${p.name}</span></td>
                            <td><span class="badge bg-success bg-opacity-10 text-success">${p.total_resolved}</span></td>
                            <td><span class="small text-muted">${p.avg_time}</span></td>
                        </tr>
                    `;
                });
            }
            html += '<tbody></table></div>';
            $('#personnel-performance-container').html(html);
        }

        function updateDashboard(year, timeframe = null) {
            if (!timeframe) {
                timeframe = $('#timeframeTabs .timeframe-btn.active').data('timeframe') || 'daily';
            }
            $.ajax({
                url: "{{ route('dashboard.index') }}",
                type: "GET",
                data: { year: year, timeframe: timeframe },
                dataType: "json",
                success: function(data) {
                    $('#displaySupportSelectedYear').text(year);
                    // Update Metric Cards
                    $('#card-new-tickets').text(data.metrics.newTickets);
                    $('#card-pending-tickets').text(data.metrics.pendingTickets);
                    $('#card-inprogress-tickets').text(data.metrics.inProgressTickets);
                    $('#card-resolved-tickets').text(data.metrics.resolvedTickets);
                    $('#card-closed-tickets').text(data.metrics.closedTickets);
                    $('#card-total-requests').text(data.metrics.totalRequests);

                    // Dynamic Ticket Status Summary Bar (welcome hero)
                    if (data.summary) {
                        $('#summary-pending-count').text(data.summary.pendingCount);
                        $('#summary-inprogress-count').text(data.summary.inProgressCount);
                        $('#summary-resolved-today').text(data.summary.resolvedTodayCount);
                        $('#summary-resolved-today-text').text(
                            data.summary.resolvedTodayCount + ' ticket' + (data.summary.resolvedTodayCount === 1 ? '' : 's')
                        );
                    }

                    // Update Bar Chart
                    renderDailyTicketsChart(data.chart.labels, data.chart.created, data.chart.resolved);

                    // Dynamic Heatmap Level Updates
                    updateHeatmaps(data.supportHeatmapData, data.requesterHeatmapData, year);

                    // Render Cards
                    renderLeaderboard(data.leaderboard);
                    renderTopCategories(data.topCategories);
                    renderMonitoring(data.monitoring);
                    renderTopOffices(data.topOffices);
                    renderPersonnelPerformance(data.personnelPerformance);
                }
            });
        }

        function formatHeatmapDate(dateObj) {
            const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            const day = String(dateObj.getDate()).padStart(2, '0');
            return `${months[dateObj.getMonth()]} ${day}, ${dateObj.getFullYear()}`;
        }

        /* Beautiful Bootstrap 5 tooltips for heatmap cells (bubble + arrow) */
        function initHeatmapTooltips(containerId) {
            if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;
            $(containerId + ' [data-bs-toggle="tooltip"]').each(function() {
                let instance = bootstrap.Tooltip.getInstance(this);
                if (instance) instance.dispose();
                new bootstrap.Tooltip(this, {
                    html: true,
                    placement: 'top',
                    trigger: 'hover focus',
                    container: 'body',
                    customClass: 'heatmap-tooltip',
                    delay: { show: 100, hide: 50 }
                });
            });
        }

        function renderHeatmapHTML(containerId, heatmapData, selectedYear) {
            let year = parseInt(selectedYear, 10);
            let monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            let monthsHTML = '';

            for (let m = 0; m < 12; m++) {
                let firstDayOfMonth = new Date(year, m, 1);
                let lastDayOfMonth = new Date(year, m + 1, 0);

                // ISO Day of Week: Mon = 1, Tue = 2, ..., Sun = 7
                let startIsoDay = firstDayOfMonth.getDay() === 0 ? 7 : firstDayOfMonth.getDay();

                // Calculate Monday of the first week
                let curr = new Date(year, m, 1 - (startIsoDay - 1));

                let weeksHTML = '';

                while (curr <= lastDayOfMonth) {
                    let weekHTML = '<div class="heatmap-week">';
                    for (let d = 0; d < 7; d++) {
                        let cellYear = curr.getFullYear();
                        let cellMonth = curr.getMonth();

                        if (cellMonth === m && cellYear === year) {
                            let yyyy = curr.getFullYear();
                            let mm = String(curr.getMonth() + 1).padStart(2, '0');
                            let dd = String(curr.getDate()).padStart(2, '0');
                            let dbDate = `${yyyy}-${mm}-${dd}`;

                            let formattedDate = formatHeatmapDate(curr);
                            let count = heatmapData ? (heatmapData[dbDate] || 0) : 0;
                            let level = getHeatmapLevel(count);
                            let taskLabel = count === 1 ? 'Task' : 'Tasks';

                            weekHTML += `
                                <div class="heatmap-cell level-${level}"
                                    data-date="${dbDate}"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    data-bs-html="true"
                                    data-bs-custom-class="heatmap-tooltip"
                                    title="${count} ${taskLabel}, ${formattedDate}">
                                </div>`;
                        } else {
                            weekHTML += `<div class="heatmap-cell level-empty"></div>`;
                        }

                        curr.setDate(curr.getDate() + 1);
                    }
                    weekHTML += '</div>';
                    weeksHTML += weekHTML;
                }

                monthsHTML += `
                    <div class="heatmap-month-group">
                        <div class="month-header text-muted small fw-semibold">${monthNames[m]}</div>
                        <div class="heatmap-month-weeks">${weeksHTML}</div>
                    </div>`;
            }

            let fullHeatmapHTML = `
                <div class="table-responsive">
                    <div class="activity-heatmap">
                        <div class="heatmap-grid-wrapper">
                            <div class="days-label text-muted">
                                <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
                            </div>
                            <div class="heatmap-months-container">
                                ${monthsHTML}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end align-items-center gap-2 mt-3 text-muted small">
                    <span>Less</span>
                    <div class="heatmap-cell level-0"></div>
                    <div class="heatmap-cell level-1"></div>
                    <div class="heatmap-cell level-2"></div>
                    <div class="heatmap-cell level-3"></div>
                    <div class="heatmap-cell level-4"></div>
                    <span>More</span>
                </div>`;

            // Inject newly generated HTML layout, then attach beautiful tooltips (with arrow)
            $(containerId).html(fullHeatmapHTML);
            initHeatmapTooltips(containerId);
        }

        function updateHeatmaps(supportData, requesterData, selectedYear) {
            if (!selectedYear) {
                selectedYear = $('#yearSelect').val() || new Date().getFullYear();
            }
            renderHeatmapHTML('#supportprogress', supportData, selectedYear);
            renderHeatmapHTML('#requesterprogress', requesterData, selectedYear);
        }

        function getHeatmapLevel(count) {
            if (count === 0) return 0;
            if (count <= 2) return 1;
            if (count <= 5) return 2;
            if (count <= 8) return 3;
            return 4;
        }

        $(document).ready(function() {
            var initialLabels = @json($chart['labels'] ?? []);
            var initialCreated = @json($chart['created'] ?? []);
            var initialResolved = @json($chart['resolved'] ?? []);
            renderDailyTicketsChart(initialLabels, initialCreated, initialResolved);

            var initialLeaderboard = @json($leaderboard ?? []);
            renderLeaderboard(initialLeaderboard);

            renderTopCategories(@json($topCategories ?? []));
            renderMonitoring(@json($monitoring ?? []));
            renderTopOffices(@json($topOffices ?? []));
            renderPersonnelPerformance(@json($personnelPerformance ?? []));

            // Beautify tooltips on the initial server-rendered heatmaps (bubble + arrow)
            initHeatmapTooltips('#supportprogress');
            initHeatmapTooltips('#requesterprogress');

            $(document).on('click', '.timeframe-btn', function(e) {
                e.preventDefault();
                $('.timeframe-btn').removeClass('active text-white').addClass('text-muted');$(this).addClass('active text-white').removeClass('text-muted');

                var timeframe = $(this).data('timeframe');
                var selectedYear = $('#yearSelect').val() || new Date().getFullYear();

                updateDashboard(selectedYear, timeframe);
            });

            $('#yearSelect').on('change', function() {
                var selectedYear = $(this).val();
                var activeTimeframe = $('#timeframeTabs .timeframe-btn.active').data('timeframe') || 'daily';
                $('#displaySupportSelectedYear').text(selectedYear);

                updateDashboard(selectedYear, activeTimeframe);
            });

            // Realtime: any ticket DB change busts cache server-side, then refetch here
            if (typeof Echo !== 'undefined') {
                Echo.channel('tickets')
                    .listen('TicketListUpdated', function() {
                        var y = $('#yearSelect').val() || new Date().getFullYear();
                        updateDashboard(y);
                    });
            }
        });
    </script>
@else
    <script>
        let dailyTicketsChart = null;

        function updateDashboard(year, timeframe = null) {
            if (!timeframe) {
                timeframe = $('#timeframeTabs .timeframe-btn.active').data('timeframe') || 'daily';
            }
            $.ajax({
                url: "{{ route('dashboard.index') }}",
                type: "GET",
                data: { year: year, timeframe: timeframe },
                dataType: "json",
                success: function(data) {
                    $('#displayRequesterSelectedYear').text(year);
                    // Own-ticket metric cards (logged-in requester only)
                    if (data.requesterMetrics) {
                        var rm = data.requesterMetrics;
                        $('#card-req-new-tickets').text(rm.newTickets);
                        $('#card-req-pending-tickets').text(rm.pendingTickets);
                        $('#card-req-inprogress-tickets').text(rm.inProgressTickets);
                        $('#card-req-resolved-tickets').text(rm.resolvedTickets);
                        $('#card-req-resolution-rate').text(rm.resolutionRate);
                        $('#card-req-closed-tickets').text(rm.closedTickets);
                        $('#card-req-closed-rate').text(rm.closedRate);
                        $('#card-req-total-requests').text(rm.totalRequests);
                        $('#req-urgent-count').text(rm.urgentPendingTickets);
                        $('#req-high-count').text(rm.highPendingTickets);
                        $('#req-urgent-wrap').toggle(rm.urgentPendingTickets > 0);
                        $('#req-high-wrap').toggle(rm.highPendingTickets > 0);
                        $('#req-nohigh-wrap').toggle(rm.urgentPendingTickets == 0 && rm.highPendingTickets == 0);
                    }
                    // Pass selected 'year' to updateHeatmaps
                    updateHeatmaps(data.supportHeatmapData, data.requesterHeatmapData, year);
                }
            });
        }

        function formatHeatmapDate(dateObj) {
            const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            const day = String(dateObj.getDate()).padStart(2, '0');
            return `${months[dateObj.getMonth()]} ${day}, ${dateObj.getFullYear()}`;
        }

        /* Beautiful Bootstrap 5 tooltips for heatmap cells (bubble + arrow) */
        function initHeatmapTooltips(containerId) {
            if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;
            $(containerId + ' [data-bs-toggle="tooltip"]').each(function() {
                let instance = bootstrap.Tooltip.getInstance(this);
                if (instance) instance.dispose();
                new bootstrap.Tooltip(this, {
                    html: true,
                    placement: 'top',
                    trigger: 'hover focus',
                    container: 'body',
                    customClass: 'heatmap-tooltip',
                    delay: { show: 100, hide: 50 }
                });
            });
        }

        function renderHeatmapHTML(containerId, heatmapData, selectedYear) {
            let year = parseInt(selectedYear, 10);
            let monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            let monthsHTML = '';

            for (let m = 0; m < 12; m++) {
                let firstDayOfMonth = new Date(year, m, 1);
                let lastDayOfMonth = new Date(year, m + 1, 0);

                // ISO Day of Week: Mon = 1, Tue = 2, ..., Sun = 7
                let startIsoDay = firstDayOfMonth.getDay() === 0 ? 7 : firstDayOfMonth.getDay();

                // Calculate Monday of the first week
                let curr = new Date(year, m, 1 - (startIsoDay - 1));

                let weeksHTML = '';

                while (curr <= lastDayOfMonth) {
                    let weekHTML = '<div class="heatmap-week">';
                    for (let d = 0; d < 7; d++) {
                        let cellYear = curr.getFullYear();
                        let cellMonth = curr.getMonth();

                        if (cellMonth === m && cellYear === year) {
                            let yyyy = curr.getFullYear();
                            let mm = String(curr.getMonth() + 1).padStart(2, '0');
                            let dd = String(curr.getDate()).padStart(2, '0');
                            let dbDate = `${yyyy}-${mm}-${dd}`;

                            let formattedDate = formatHeatmapDate(curr);
                            let count = heatmapData ? (heatmapData[dbDate] || 0) : 0;
                            let level = getHeatmapLevel(count);
                            let taskLabel = count === 1 ? 'Task' : 'Tasks';

                            weekHTML += `
                                <div class="heatmap-cell level-${level}"
                                    data-date="${dbDate}"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    data-bs-html="true"
                                    data-bs-custom-class="heatmap-tooltip"
                                    title="${count} ${taskLabel}, ${formattedDate}">
                                </div>`;
                        } else {
                            weekHTML += `<div class="heatmap-cell level-empty"></div>`;
                        }

                        curr.setDate(curr.getDate() + 1);
                    }
                    weekHTML += '</div>';
                    weeksHTML += weekHTML;
                }

                monthsHTML += `
                    <div class="heatmap-month-group">
                        <div class="month-header text-muted small fw-semibold">${monthNames[m]}</div>
                        <div class="heatmap-month-weeks">${weeksHTML}</div>
                    </div>`;
            }

            let fullHeatmapHTML = `
                <div class="table-responsive">
                    <div class="activity-heatmap">
                        <div class="heatmap-grid-wrapper">
                            <div class="days-label text-muted">
                                <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
                            </div>
                            <div class="heatmap-months-container">
                                ${monthsHTML}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end align-items-center gap-2 mt-3 text-muted small">
                    <span>Less</span>
                    <div class="heatmap-cell level-0"></div>
                    <div class="heatmap-cell level-1"></div>
                    <div class="heatmap-cell level-2"></div>
                    <div class="heatmap-cell level-3"></div>
                    <div class="heatmap-cell level-4"></div>
                    <span>More</span>
                </div>`;

            // Inject newly generated HTML layout, then attach beautiful tooltips (with arrow)
            $(containerId).html(fullHeatmapHTML);
            initHeatmapTooltips(containerId);
        }

        function updateHeatmaps(supportData, requesterData, selectedYear) {
            if (!selectedYear) {
                selectedYear = $('#yearSelect').val() || new Date().getFullYear();
            }
            renderHeatmapHTML('#supportprogress', supportData, selectedYear);
            renderHeatmapHTML('#requesterprogress', requesterData, selectedYear);
        }

        function getHeatmapLevel(count) {
            if (count === 0) return 0;
            if (count <= 2) return 1;
            if (count <= 5) return 2;
            if (count <= 8) return 3;
            return 4;
        }

        $(document).ready(function() {
            // Beautify tooltips on the initial server-rendered heatmaps (bubble + arrow)
            initHeatmapTooltips('#supportprogress');
            initHeatmapTooltips('#requesterprogress');

            $(document).on('click', '.timeframe-btn', function(e) {
                e.preventDefault();
                $('.timeframe-btn').removeClass('active text-white').addClass('text-muted');$(this).addClass('active text-white').removeClass('text-muted');

                var timeframe = $(this).data('timeframe');
                var selectedYear = $('#yearSelect').val() || new Date().getFullYear();

                updateDashboard(selectedYear, timeframe);
            });

            $('#yearSelect').on('change', function() {
                var selectedYear = $(this).val();
                var activeTimeframe = $('#timeframeTabs .timeframe-btn.active').data('timeframe') || 'daily';
                $('#displayRequesterSelectedYear').text(selectedYear);

                updateDashboard(selectedYear, activeTimeframe);
            });

            // Realtime: any ticket DB change busts cache server-side, then refetch heatmap here
            if (typeof Echo !== 'undefined') {
                Echo.channel('tickets')
                    .listen('TicketListUpdated', function() {
                        var y = $('#yearSelect').val() || new Date().getFullYear();
                        updateDashboard(y);
                    });
            }
        });
    </script>
@endif
