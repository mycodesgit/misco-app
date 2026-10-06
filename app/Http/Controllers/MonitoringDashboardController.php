<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

class MonitoringDashboardController extends Controller
{
    public function index()
    {
        return view('pages.monitor', $this->helpdeskData());
    }

    public function helpdesk()
    {
        return response()->json([
            'success' => true,
            'data'    => $this->helpdeskData(),
        ]);
    }

    private function helpdeskData(): array
    {
        $counts = [
            'pending'     => DailyTicketRequest::where('status', 'Pending')->count(),
            'in_progress' => DailyTicketRequest::where('status', 'In Progress')->count(),
            'resolved'    => DailyTicketRequest::where('status', 'Resolved')->count(),
        ];

        $priority = DailyTicketRequest::whereIn('status', ['Pending', 'In Progress'])
            ->selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority')
            ->toArray();

        $latest = DailyTicketRequest::whereIn('status', ['Pending', 'In Progress'])
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->get(['id', 'ticket_number', 'issue_description', 'priority', 'status', 'created_at'])
            ->map(function ($t) {
                return [
                    'id'                => $t->id,
                    'ticket_number'     => $t->ticket_number,
                    'issue_description' => $t->issue_description,
                    'priority'          => $t->priority,
                    'status'            => $t->status,
                ];
            });

        return [
            'counts'   => $counts,
            'priority' => [
                'High'   => $priority['High'] ?? 0,
                'Medium' => $priority['Medium'] ?? 0,
                'Low'    => $priority['Low'] ?? 0,
                'Urgent' => $priority['Urgent'] ?? 0,
            ],
            'latest'   => $latest,
        ];
    }
}
