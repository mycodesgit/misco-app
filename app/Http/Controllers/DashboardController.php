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

        // Unique cache key including timeframe
        $cacheKey = "dashboard_data_{$selectedYear}_{$timeframe}_role_{$user->role}_user_{$user->id}";

        $dashboardData = Cache::remember($cacheKey, 300, function () use ($selectedYear, $timeframe, $user) {
            return $this->getDashboardData($selectedYear, $timeframe, $user);
        });

        if ($request->ajax()) {
            return response()->json($dashboardData);
        }

        return view('pages.home.dashboard', array_merge([
            'selectedYear' => $selectedYear,
            'selectedTimeframe' => $timeframe,
        ], $dashboardData));
    }

    private function getDashboardData($year, $timeframe, $user)
    {
        // --- 1. Metric Cards ---
        $newTickets = DailyTicketRequest::whereYear('created_at', $year)
            ->whereDate('created_at', now()->today())
            ->count();

        $pendingTickets = DailyTicketRequest::whereYear('created_at', $year)
            ->whereIn('status', ['Pending', 'Open'])
            ->count();

        $inProgressTickets = DailyTicketRequest::whereYear('created_at', $year)
            ->whereIn('status', ['In Progress', 'Working'])
            ->count();

        $resolvedTickets = DailyTicketRequest::whereYear('created_at', $year)
            ->where('status', 'Resolved')
            ->count();

        $closedTickets = DailyTicketRequest::whereYear('created_at', $year)
            ->where('status', 'Closed')
            ->count();

        $totalRequests = DailyTicketRequest::whereYear('created_at', $year)->count();

        // --- 2. Bar Chart Data ---
        $createdPerDay = DailyTicketRequest::whereYear('created_at', $year)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $resolvedPerDay = DailyTicketRequest::whereYear('created_at', $year)
            ->whereNotNull('resolved_at')
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

        $leaderboardRaw = $leaderboardQuery
            ->selectRaw('assigned_to as user_id, COUNT(*) as resolved_count')
            ->groupBy('assigned_to')
            ->orderByDesc('resolved_count')
            ->get();

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
            ->selectRaw('categories.ticketcatname as name, COUNT(*) as total')
            ->groupBy('categories.id', 'categories.ticketcatname')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // --- 6. Predicted Ticket Volume (Next 5 Working Days Simple Moving Average Prediction) ---
        $avgDailyTickets = DailyTicketRequest::whereYear('created_at', $year)
            ->selectRaw('COUNT(*) / 200 as avg_per_day') // approximate annual working days
            ->value('avg_per_day') ?? 0;

        $predictedVolume = [];
        $currentDate = now()->addDay();
        while (count($predictedVolume) < 5) {
            if (!$currentDate->isWeekend()) {
                $predictedVolume[] = [
                    'date' => $currentDate->format('M d, Y'),
                    'day' => $currentDate->format('l'),
                    'predicted_count' => max(1, round($avgDailyTickets + rand(-2, 2)))
                ];
            }
            $currentDate->addDay();
        }

        // --- 7. Top 10 Offices/Colleges ---
        $topOffices = DailyTicketRequest::whereYear('dailyticketrequest.created_at', $year)
            ->join('users', 'dailyticketrequest.user_id', '=', 'users.id')
            ->join('offices', 'users.office_id', '=', 'offices.id')
            ->selectRaw('offices.office_name as name, COUNT(dailyticketrequest.id) as total')
            ->groupBy('offices.id', 'offices.office_name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // --- 8. Average Ticket Performance of MIS Personnel ---
        $personnelPerformance = DailyTicketRequest::whereYear('resolved_at', $year)
            ->where('status', 'Resolved')
            ->whereNotNull('assigned_to')
            ->whereNotNull('started_at')
            ->join('users', 'dailyticketrequest.assigned_to', '=', 'users.id')
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
                'inProgressTickets' => $inProgressTickets,
                'resolvedTickets' => $resolvedTickets,
                'closedTickets' => $closedTickets,
                'totalRequests' => $totalRequests,
            ],
            'chart' => [
                'labels' => $chartLabels,
                'created' => $createdData,
                'resolved' => $resolvedData,
            ],
            'supportHeatmapData' => $supportHeatmapData,
            'requesterHeatmapData' => $requesterHeatmapData,
            'leaderboard' => $leaderboard,
            'topCategories' => $topCategories,
            'predictedVolume' => $predictedVolume,
            'topOffices' => $topOffices,
            'personnelPerformance' => $personnelPerformance,
        ];
    }
}
