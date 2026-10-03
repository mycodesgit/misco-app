@if(Auth::guard('web')->user()->role != 'Requester')
    <script>
        let dailyTicketsChart = null;

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

            // Top 3 Podium (1st, 2nd, 3rd)
            let first = leaderboardData[0] || null;
            let second = leaderboardData[1] || null;
            let third = leaderboardData[2] || null;

            let podiumHTML = `
                <!-- Rank 2 -->
                <div class="col-4 px-1">
                    ${second ? `
                        <div class="position-relative d-inline-block mb-2">
                            <div class="avatar-podium bg-secondary bg-opacity-20 text-light rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto">
                                ${second.initials}
                            </div>
                            <span class="badge bg-secondary position-absolute start-50 translate-middle-x badge-rank rounded-circle">2</span>
                        </div>
                        <h6 class="fw-bold mb-0 text-truncate small">${second.short_name}</h6>
                        <span class="badge bg-light text-dark border small mt-1">${second.points} pts</span>
                    ` : '<div class="text-muted small py-4">-</div>'}
                </div>

                <!-- Rank 1 -->
                <div class="col-4 px-1 podium-item-top">
                    ${first ? `
                        <div class="position-relative d-inline-block mb-2">
                            <i class="ti ti-crown fs-4 text-warning crown-icon d-block"></i>
                            <div class="avatar-podium-lg bg-warning bg-opacity-25 text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto border border-2 border-warning">
                                ${first.initials}
                            </div>
                            <span class="badge bg-warning text-dark position-absolute start-50 translate-middle-x badge-rank rounded-circle fw-bold">1</span>
                        </div>
                        <h6 class="fw-bold mb-0 text-truncate">${first.short_name}</h6>
                        <span class="badge bg-warning bg-opacity-20 border border-warning border-opacity-25 small mt-1">${first.points} pts</span>
                    ` : '<div class="text-muted small py-4">-</div>'}
                </div>

                <!-- Rank 3 -->
                <div class="col-4 px-1">
                    ${third ? `
                        <div class="position-relative d-inline-block mb-2">
                            <div class="avatar-podium bg-danger bg-opacity-20 text-light rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto">
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

            // Remaining Ranks (4th onwards)
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
                            backgroundColor: '#ffb020',
                            borderColor: '#e69d19',
                            borderWidth: 1
                        },
                        {
                            label: 'Resolved Tickets',
                            data: resolvedData,
                            backgroundColor: '#1d1a27',
                            borderColor: '#110f18',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Ticket Count'
                            }
                        }
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

        function renderPredictedVolume(predictions) {
            let html = '<div class="row text-center g-2">';
            if (!predictions || predictions.length === 0) {
                html += '<div class="text-muted">No prediction available</div>';
            } else {
                predictions.forEach(p => {
                    html += `
                        <div class="col">
                            <div class="p-2 border rounded bg-light dark-bg-dark">
                                <small class="text-muted d-block">${p.day}</small>
                                <span class="fw-bold fs-5 text-primary">${p.predicted_count}</span>
                                <small class="d-block text-muted" style="font-size: 0.7rem;">${p.date}</small>
                            </div>
                        </div>
                    `;
                });
            }
            html += '</div>';
            $('#predicted-volume-container').html(html);
        }

        function renderTopOffices(offices) {
            let html = '<div class="list-group list-group-flush">';
            if (!offices || offices.length === 0) {
                html += '<div class="text-center text-muted py-2">No office records available</div>';
            } else {
                offices.forEach((o, idx) => {
                    html += `
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-1">
                            <span class="small">${idx + 1}. ${o.name}</span>
                            <span class="badge bg-secondary">${o.total} tickets</span>
                        </div>
                    `;
                });
            }
            html += '</div>';
            $('#top-offices-container').html(html);
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
                    // Update Metric Cards
                    $('#card-new-tickets').text(data.metrics.newTickets);
                    $('#card-pending-tickets').text(data.metrics.pendingTickets);
                    $('#card-inprogress-tickets').text(data.metrics.inProgressTickets);
                    $('#card-resolved-tickets').text(data.metrics.resolvedTickets);
                    $('#card-closed-tickets').text(data.metrics.closedTickets);
                    $('#card-total-requests').text(data.metrics.totalRequests);

                    // Update Bar Chart
                    renderDailyTicketsChart(data.chart.labels, data.chart.created, data.chart.resolved);

                    // Dynamic Heatmap Level Updates
                    updateHeatmaps(data.supportHeatmapData, data.requesterHeatmapData);

                    // Render Cards
                    renderLeaderboard(data.leaderboard);
                    renderTopCategories(data.topCategories);
                    renderPredictedVolume(data.predictedVolume);
                    renderTopOffices(data.topOffices);
                    renderPersonnelPerformance(data.personnelPerformance);
                }
            });
        }

        function updateHeatmaps(supportData, requesterData) {
            $('#supportprogress .heatmap-cell[data-date]').each(function() {
                let date = $(this).data('date');
                let count = supportData[date] || 0;
                let level = getHeatmapLevel(count);
                $(this).attr('class', 'heatmap-cell level-' + level);
            });

            $('#requesterprogress .heatmap-cell[data-date]').each(function() {
                let date = $(this).data('date');
                let count = requesterData[date] || 0;
                let level = getHeatmapLevel(count);
                $(this).attr('class', 'heatmap-cell level-' + level);
            });
        }

        function getHeatmapLevel(count) {
            if (count === 0) return 0;
            if (count <= 2) return 1;
            if (count <= 5) return 2;
            if (count <= 8) return 3;
            return 4;
        }

        $(document).ready(function() {
            // 1. Render Chart Initial Data
            var initialLabels = @json($chart['labels'] ?? []);
            var initialCreated = @json($chart['created'] ?? []);
            var initialResolved = @json($chart['resolved'] ?? []);
            renderDailyTicketsChart(initialLabels, initialCreated, initialResolved);

            // 2. Render Initial Leaderboard Data
            var initialLeaderboard = @json($leaderboard ?? []);
            renderLeaderboard(initialLeaderboard);

            // 3. Render Other Initial Cards
            renderTopCategories(@json($topCategories ?? []));
            renderPredictedVolume(@json($predictedVolume ?? []));
            renderTopOffices(@json($topOffices ?? []));
            renderPersonnelPerformance(@json($personnelPerformance ?? []));

            // 4. Timeframe Tab Click Handler (Daily / Monthly / All time)
            $(document).on('click', '.timeframe-btn', function(e) {
                e.preventDefault();

                // Update active styles on tabs
                $('.timeframe-btn').removeClass('active text-white').addClass('text-muted');
                $(this).addClass('active text-white').removeClass('text-muted');

                // Fetch refreshed data for current year & selected timeframe
                var timeframe = $(this).data('timeframe');
                var selectedYear = $('#yearSelect').val() || new Date().getFullYear();

                updateDashboard(selectedYear, timeframe);
            });

            // 5. Year Select Change Handler
            $('#yearSelect').on('change', function() {
                var selectedYear = $(this).val();
                var activeTimeframe = $('#timeframeTabs .timeframe-btn.active').data('timeframe') || 'daily';

                updateDashboard(selectedYear, activeTimeframe);
            });
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

                    // Dynamic Heatmap Level Updates
                    updateHeatmaps(data.supportHeatmapData, data.requesterHeatmapData);
                }
            });
        }

        function updateHeatmaps(supportData, requesterData) {
            $('#supportprogress .heatmap-cell[data-date]').each(function() {
                let date = $(this).data('date');
                let count = supportData[date] || 0;
                let level = getHeatmapLevel(count);
                $(this).attr('class', 'heatmap-cell level-' + level);
            });

            $('#requesterprogress .heatmap-cell[data-date]').each(function() {
                let date = $(this).data('date');
                let count = requesterData[date] || 0;
                let level = getHeatmapLevel(count);
                $(this).attr('class', 'heatmap-cell level-' + level);
            });
        }

        function getHeatmapLevel(count) {
            if (count === 0) return 0;
            if (count <= 2) return 1;
            if (count <= 5) return 2;
            if (count <= 8) return 3;
            return 4;
        }

        $(document).ready(function() {
            // 4. Timeframe Tab Click Handler (Daily / Monthly / All time)
            $(document).on('click', '.timeframe-btn', function(e) {
                e.preventDefault();

                // Update active styles on tabs
                $('.timeframe-btn').removeClass('active text-white').addClass('text-muted');
                $(this).addClass('active text-white').removeClass('text-muted');

                // Fetch refreshed data for current year & selected timeframe
                var timeframe = $(this).data('timeframe');
                var selectedYear = $('#yearSelect').val() || new Date().getFullYear();

                updateDashboard(selectedYear, timeframe);
            });

            // 5. Year Select Change Handler
            $('#yearSelect').on('change', function() {
                var selectedYear = $(this).val();
                var activeTimeframe = $('#timeframeTabs .timeframe-btn.active').data('timeframe') || 'daily';

                updateDashboard(selectedYear, activeTimeframe);
            });
        });
    </script>
@endif