<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use Carbon\Carbon;
use PDF;

use Jenssegers\Agent\Agent;

use App\Models\TicketDB\User;
use App\Models\TicketDB\ClientFeedback;
use App\Models\TicketDB\UserRole;
use App\Models\TicketDB\Office;
use App\Models\TicketDB\Category;
use App\Models\TicketDB\Subcategory;
use App\Models\TicketDB\DailyTask;
use App\Models\TicketDB\DailyTicketRequest;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $selectedYear = $request->get('year', date('Y'));
        $timeframe = $request->get('timeframe', 'daily'); // Default timeframe
        $user = auth()->user();

        $baseQuery = DailyTicketRequest::whereRaw('FIND_IN_SET(?, REPLACE(assigned_to, " ", ""))', [$user->id]);

        $pendingCount       = (clone $baseQuery)->where('status', 'Pending')->count();
        $inProgressCount    = (clone $baseQuery)->where('status', 'In Progress')->count();
        $resolvedTodayCount = (clone $baseQuery)->where('status', 'Resolved')->whereDate('resolved_at', now()->today())->count();

        // Versioned key: bumped on every ticket write, so cache stays fast
        // yet realtime updates are never stale (no flush = no file race)
        $version = Cache::rememberForever('dashboard_version', fn() => 1);
        $cacheKey = "dashboard_data_v{$version}_{$selectedYear}_{$timeframe}_role_{$user->role}_user_{$user->id}";

        $dashboardData = Cache::remember($cacheKey, 300, function () use ($selectedYear, $timeframe, $user) {
            return $this->getDashboardData($selectedYear, $timeframe, $user);
        });

        if ($request->ajax()) {
            return response()->json(array_merge($dashboardData, [
                'summary' => [
                    'pendingCount'       => $pendingCount,
                    'inProgressCount'    => $inProgressCount,
                    'resolvedTodayCount' => $resolvedTodayCount,
                ],
            ]));
        }

        $currentUserRole = Auth::user()->role;

        $urole = UserRole::where('status', 1)
            ->whereNotIn('rolename', ['Administrator', 'Requester'])
            ->when($currentUserRole == 'Administrator', function ($query) use ($currentUserRole) {
                $query->where('rolename', $currentUserRole);
            })
            ->get();

        $cat = Category::with('user')->where('status', 1)->get();

        return view('pages.home.dashboard', array_merge([
            'selectedYear' => $selectedYear,
            'selectedTimeframe' => $timeframe,
            'pendingCount'       => $pendingCount,
            'inProgressCount'    => $inProgressCount,
            'resolvedTodayCount' => $resolvedTodayCount,
            'urole' => $urole
        ], $dashboardData));
    }

    private function getDashboardData($year, $timeframe, $user)
    {
        // Support accounts (any role except Requester and Administrator) only
        // ever see data that belongs to their own office.
        $isAdmin = $user->role === 'Administrator';
        $officeScope = (!$isAdmin && $user->role !== 'Requester') ? $user->office_id : null;

        // --- 1. Metric Cards ---
        $totalRequests = DailyTicketRequest::whereYear('created_at', $year)
            ->when(!$isAdmin, fn ($q) => $q->where('off_id', $user->office_id))
            ->count();

        $newTickets = DailyTicketRequest::whereYear('created_at', $year)
            ->whereIn('status', ['Pending', 'Open'])
            ->whereDate('created_at', now()->today())
            ->when($officeScope, fn ($q) => $q->where('off_id', $officeScope))
            ->count();

        $pendingTickets = DailyTicketRequest::whereYear('created_at', $year)
            ->whereIn('status', ['Pending', 'Open'])
            ->when($officeScope, fn ($q) => $q->where('off_id', $officeScope))
            ->count();
        $highPendingTickets = DailyTicketRequest::whereYear('created_at', $year)
            ->whereIn('status', ['Pending', 'Open'])
            ->where('priority', 'High')
            ->when($officeScope, fn ($q) => $q->where('off_id', $officeScope))
            ->count();
        $urgentPendingTickets = DailyTicketRequest::whereYear('created_at', $year)
            ->whereIn('status', ['Pending', 'Open'])
            ->where('priority', 'Urgent')
            ->when($officeScope, fn ($q) => $q->where('off_id', $officeScope))
            ->count();

        $inProgressTickets = DailyTicketRequest::whereYear('created_at', $year)
            ->whereIn('status', ['In Progress', 'Working'])
            ->when($officeScope, fn ($q) => $q->where('off_id', $officeScope))
            ->count();

        $resolvedTickets = DailyTicketRequest::whereYear('created_at', $year)
            ->where('status', 'Resolved')
            ->when($officeScope, fn ($q) => $q->where('off_id', $officeScope))
            ->count();
        $resolutionRate = $totalRequests > 0
            ? round(($resolvedTickets / $totalRequests) * 100, 1)
            : 0;

        $closedTickets = DailyTicketRequest::whereYear('created_at', $year)
            ->where('status', 'Cancelled')
            ->when($officeScope, fn ($q) => $q->where('off_id', $officeScope))
            ->count();
        $closedRate = $totalRequests > 0
            ? round(($closedTickets / $totalRequests) * 100, 1)
            : 0;

        // --- 1b. Requester personal metrics (own tickets of the logged-in user only) ---
        $reqBase = DailyTicketRequest::where('user_id', $user->id)->whereYear('created_at', $year);

        $reqTotalRequests = (clone $reqBase)->count();
        $reqNewTickets = (clone $reqBase)->whereIn('status', ['Pending', 'Open'])->whereDate('created_at', now()->today())->count();
        $reqPendingTickets = (clone $reqBase)->whereIn('status', ['Pending', 'Open'])->count();
        $reqHighPendingTickets = (clone $reqBase)->whereIn('status', ['Pending', 'Open'])->where('priority', 'High')->count();
        $reqUrgentPendingTickets = (clone $reqBase)->whereIn('status', ['Pending', 'Open'])->where('priority', 'Urgent')->count();
        $reqInProgressTickets = (clone $reqBase)->whereIn('status', ['In Progress', 'Working'])->count();
        $reqResolvedTickets = (clone $reqBase)->where('status', 'Resolved')->count();
        $reqResolutionRate = $reqTotalRequests > 0
            ? round(($reqResolvedTickets / $reqTotalRequests) * 100, 1)
            : 0;
        $reqClosedTickets = (clone $reqBase)->where('status', 'Cancelled')->count();
        $reqClosedRate = $reqTotalRequests > 0
            ? round(($reqClosedTickets / $reqTotalRequests) * 100, 1)
            : 0;

        $requesterMetrics = [
            'newTickets' => $reqNewTickets,
            'pendingTickets' => $reqPendingTickets,
            'highPendingTickets' => $reqHighPendingTickets,
            'urgentPendingTickets' => $reqUrgentPendingTickets,
            'inProgressTickets' => $reqInProgressTickets,
            'resolvedTickets' => $reqResolvedTickets,
            'resolutionRate' => $reqResolutionRate,
            'closedTickets' => $reqClosedTickets,
            'closedRate' => $reqClosedRate,
            'totalRequests' => $reqTotalRequests,
        ];

        // --- 2. Bar Chart Data ---
        $createdPerDay = DailyTicketRequest::whereYear('created_at', $year)
            ->when($officeScope, fn ($q) => $q->where('off_id', $officeScope))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $resolvedPerDay = DailyTicketRequest::whereYear('created_at', $year)
            ->whereNotNull('resolved_at')
            ->when($officeScope, fn ($q) => $q->where('off_id', $officeScope))
            ->selectRaw('DATE(resolved_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $allDates = $createdPerDay->keys()->merge($resolvedPerDay->keys())->unique()->sort()->values();

        $chartLabels = [];
        $createdData = [];
        $resolvedData = [];

        foreach ($allDates as $date) {
            $chartLabels[] = \Carbon\Carbon::parse($date)->format('M d, Y');
            $createdData[] = $createdPerDay->get($date, 0);
            $resolvedData[] = $resolvedPerDay->get($date, 0);
        }

        // --- 3. Heatmap Data ---
        $supportHeatmapData = [];
        $requesterHeatmapData = [];

        if ($user->role !== 'Requester') {
            // Fetch completed tasks grouped by date
            $resolvedTasks = DailyTask::whereYear('completed_at', $year)
                ->where('status', 'Completed')
                ->selectRaw('DATE(completed_at) as date, COUNT(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date')
                ->toArray();

            // Fetch resolved tickets grouped by date
            $resolvedTicketsMap = DailyTicketRequest::whereYear('resolved_at', $year)
                ->where('status', 'Resolved')
                ->where('off_id', $user->office_id)
                ->whereRaw('FIND_IN_SET(?, REPLACE(assigned_to, " ", ""))', [$user->id])
                ->selectRaw('DATE(resolved_at) as date, COUNT(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date')
                ->toArray();

            // Collect all unique string dates
            $allResolvedDates = array_unique(array_merge(array_keys($resolvedTasks), array_keys($resolvedTicketsMap)));

            // Combine task counts + ticket counts for each date
            foreach ($allResolvedDates as $date) {
                $taskCount = $resolvedTasks[$date] ?? 0;
                $ticketCount = $resolvedTicketsMap[$date] ?? 0;
                $supportHeatmapData[(string)$date] = $taskCount + $ticketCount;
            }
        }

        // Requester progress heatmap (filtered by current logged-in user)
        $requesterHeatmapData = DailyTicketRequest::whereYear('created_at', $year)
            ->where('user_id', $user->id) // Filter by logged-in user (or use off_id depending on requirements)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();

        // --- 4. Dynamic Timeframe Filtered Leaderboard ---
        $leaderboardQuery = DailyTicketRequest::whereYear('resolved_at', $year)
            ->where('status', 'Resolved')
            ->whereNotNull('assigned_to');

        if ($timeframe === 'daily') {
            $leaderboardQuery->whereDate('resolved_at', now()->toDateString());
        } elseif ($timeframe === 'monthly') {
            $leaderboardQuery->whereMonth('resolved_at', now()->month);
        }

        if ($officeScope) {
            // Office view: rank only personnel of the viewer's office
            $leaderboardRaw = (clone $leaderboardQuery)
                ->join('users', function ($join) {
                    $join->whereRaw('FIND_IN_SET(users.id, REPLACE(dailyticketrequest.assigned_to, " ", ""))');
                })
                ->where('users.office_id', $officeScope)
                ->selectRaw('users.id as user_id, COUNT(*) as resolved_count')
                ->groupBy('users.id')
                ->orderByDesc('resolved_count')
                ->get();
        } else {
            $leaderboardRaw = $leaderboardQuery
                ->selectRaw('assigned_to as user_id, COUNT(*) as resolved_count')
                ->groupBy('assigned_to')
                ->orderByDesc('resolved_count')
                ->get();
        }

        $leaderboard = [];
        $rank = 1;

        foreach ($leaderboardRaw as $item) {
            $performer = User::find($item->user_id);
            if ($performer) {
                $initials = strtoupper(substr($performer->fname, 0, 1) . substr($performer->lname, 0, 1));
                $leaderboard[] = [
                    'rank' => $rank++,
                    'user_id' => $performer->id,
                    'name' => $performer->fname . ' ' . $performer->lname,
                    'short_name' => $performer->fname,
                    'initials' => $initials,
                    'points' => $item->resolved_count,
                    'is_current_user' => ($performer->id === $user->id)
                ];
            }
        }

        // --- 5. Top Issue Categories ---
        $topCategories = DailyTicketRequest::whereYear('dailyticketrequest.created_at', $year)
            ->join('categories', 'dailyticketrequest.cat_id', '=', 'categories.id')
            ->when($officeScope, fn ($q) => $q->where('dailyticketrequest.off_id', $officeScope))
            ->selectRaw('categories.ticketcatname as name, COUNT(*) as total')
            ->groupBy('categories.id', 'categories.ticketcatname')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // --- 6. Monitoring (replaces predicted volume) ---
        // Overdue = still unresolved and created more than 7 days ago
        $overdueTickets = DailyTicketRequest::whereIn('status', ['Pending', 'Open', 'In Progress', 'Working'])
            ->where('created_at', '<', now()->subDays(7))
            ->when($officeScope, fn ($q) => $q->where('off_id', $officeScope))
            ->count();

        // Resolved tickets whose requester has not submitted feedback yet
        $pendingFeedback = ClientFeedback::whereNull('rating')
            ->whereHas('ticket', fn ($q) => $q->where('status', 'Resolved')
                ->when($officeScope, fn ($q2) => $q2->where('off_id', $officeScope)))
            ->count();

        $requesterUsers = User::where('role', 'Requester')->where('ustatus', '!=', 3)->count();
        $supportUsers = User::where('role', '!=', 'Requester')->where('ustatus', '!=', 3)->count();

        $monitoring = [
            ['label' => 'Overdue Tickets', 'value' => $overdueTickets, 'sub' => 'unresolved > 7 days', 'icon' => 'ti-alarm', 'color' => 'text-danger'],
            ['label' => 'Pending Feedback', 'value' => $pendingFeedback, 'sub' => 'resolved, no feedback', 'icon' => 'ti-forms', 'color' => 'text-warning'],
            ['label' => 'Requesters', 'value' => $requesterUsers, 'sub' => 'requester accounts', 'icon' => 'ti-users', 'color' => 'text-info'],
            ['label' => 'Support Users', 'value' => $supportUsers, 'sub' => 'non-requester accounts', 'icon' => 'ti-user-cog', 'color' => 'text-success'],
        ];

        // --- 7. Top 10 Offices/Colleges ---
        $topOffices = DailyTicketRequest::whereYear('dailyticketrequest.created_at', $year)
            ->join('users', 'dailyticketrequest.user_id', '=', 'users.id')
            ->join('offices', 'users.office_id', '=', 'offices.id')
            ->when($officeScope, fn ($q) => $q->where('dailyticketrequest.off_id', $officeScope))
            ->selectRaw('offices.office_abbr as name, COUNT(dailyticketrequest.id) as total')
            ->groupBy('offices.id', 'offices.office_abbr')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // --- 8. Average Ticket Performance of MIS Personnel ---
        $personnelPerformance = DailyTicketRequest::whereYear('resolved_at', $year)
            ->where('status', 'Resolved')
            ->whereNotNull('assigned_to')
            ->whereNotNull('started_at')
            ->join('users', 'dailyticketrequest.assigned_to', '=', 'users.id')
            ->when($officeScope, fn ($q) => $q->where('users.office_id', $officeScope))
            ->selectRaw('users.fname, users.lname, COUNT(*) as total_resolved, AVG(TIMESTAMPDIFF(MINUTE, started_at, resolved_at)) as avg_time_minutes')
            ->groupBy('users.id', 'users.fname', 'users.lname')
            ->get()
            ->map(function ($item) {
                $hours = floor($item->avg_time_minutes / 60);
                $mins = round($item->avg_time_minutes % 60);
                return [
                    'name' => $item->fname . ' ' . $item->lname,
                    'total_resolved' => $item->total_resolved,
                    'avg_time' => $hours > 0 ? "{$hours}h {$mins}m" : "{$mins}m"
                ];
            });

        return [
            'metrics' => [
                'newTickets' => $newTickets,
                'pendingTickets' => $pendingTickets,
                'highPendingTickets' => $highPendingTickets,
                'urgentPendingTickets' => $urgentPendingTickets,
                'inProgressTickets' => $inProgressTickets,
                'resolvedTickets' => $resolvedTickets,
                'resolutionRate' => $resolutionRate,
                'closedTickets' => $closedTickets,
                'closedRate' => $closedRate,
                'totalRequests' => $totalRequests,
            ],
            'chart' => [
                'labels' => $chartLabels,
                'created' => $createdData,
                'resolved' => $resolvedData,
            ],
            'requesterMetrics' => $requesterMetrics,
            'supportHeatmapData' => $supportHeatmapData,
            'requesterHeatmapData' => $requesterHeatmapData,
            'leaderboard' => $leaderboard,
            'topCategories' => $topCategories,
            'monitoring' => $monitoring,
            'topOffices' => $topOffices,
            'personnelPerformance' => $personnelPerformance,
        ];
    }
}
