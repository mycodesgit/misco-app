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
use App\Models\CoasDB\QueueCounter;
use App\Models\CoasDB\QueueCustomer;
use App\Models\CoasDB\QueueMode;
use App\Models\EvalDB\QCEfevalrate;
use App\Models\EvalDB\QCEsemester;
use App\Models\EvalDB\QCEsetting;

class MonitoringDashboardController extends Controller
{
    public function index()
    {
        return view('pages.monitor', array_merge($this->helpdeskData(), [
            'personnel' => $this->personnelData(),
            'queue'     => $this->queueData(),
            'eval'      => $this->evaluationData(),
        ]));
    }

    public function helpdesk()
    {
        return response()->json([
            'success' => true,
            'data'    => $this->helpdeskData(),
        ]);
    }

    public function personnel()
    {
        return response()->json([
            'success' => true,
            'data'    => $this->personnelData(),
        ]);
    }

    public function queue()
    {
        return response()->json([
            'success' => true,
            'data'    => $this->queueData(),
        ]);
    }

    public function evaluation()
    {
        return response()->json([
            'success' => true,
            'data'    => $this->evaluationData(),
        ]);
    }

    private function evaluationData(): array
    {
        try {
            $setting = QCEsetting::first();
            $status = $setting ? trim((string) $setting->statuseval) : 'Off';
            $status = strcasecmp($status, 'On') === 0 ? 'On' : 'Off';

            $sem = QCEsemester::where('qcesemstat', '2')->orderBy('id', 'DESC')->first()
                ?? QCEsemester::orderBy('id', 'DESC')->first();
            $ord = ['1' => '1st Sem', '2' => '2nd Sem', '3' => '3rd Sem'][$sem->qcesemester ?? ''] ?? (($sem->qcesemester ?? '') . ' Sem');
            $term = $sem ? ('A.Y. ' . $sem->qceschlyear . ' (' . $ord . ')') : 'N/A';

            $today = QCEfevalrate::whereDate('created_at', today())->count();

            $latest = QCEfevalrate::where('created_at', '>=', now()->subMinute())
                ->orderBy('id', 'DESC')
                ->limit(5)
                ->get(['id', 'prog', 'subjidrate', 'qcefacname', 'evaluatorname', 'question_rate'])
                ->map(function ($r) {
                    $avg = null;
                    if ($r->question_rate) {
                        $rates = json_decode((string) $r->question_rate, true);
                        if (is_array($rates) && count($rates)) {
                            $nums = array_filter(array_values($rates), 'is_numeric');
                            if (count($nums)) {
                                $avg = number_format(array_sum($nums) / count($nums), 1);
                            }
                        }
                    }
                    return [
                        'id'      => $r->id,
                        'title'   => trim((string) $r->qcefacname) !== '' ? (string) $r->qcefacname : '—',
                        'sub'     => trim(trim((string) ($r->prog ?? '')) . ' • ' . trim((string) ($r->evaluatorname ?? '')), ' •'),
                        'average' => $avg,
                    ];
                })
                ->values();

            return [
                'status'       => $status,
                'term'         => $term,
                'today'        => $today,
                'latest'       => $latest,
            ];
        } catch (\Exception $e) {
            return ['status' => 'Off', 'term' => 'N/A', 'today' => 0, 'latest' => []];
        }
    }

    private function queueData(): array
    {
        $mode = QueueMode::first();
        $isOn = $mode && strcasecmp(trim((string) $mode->statusqueue), 'On') === 0;

        $counters = QueueCounter::orderBy('windowname')
            ->get(['id', 'windowname', 'category', 'activeidnumber', 'counterstatus']);

        // Window number = the queued ticket assigned to it:
        // counters.activeidnumber matches customers.id, display customers.queue_number.
        $customersById = QueueCustomer::whereIn('id', $counters->pluck('activeidnumber')->filter())
            ->get(['id', 'queue_number', 'status'])
            ->keyBy('id');

        $counters = $counters->map(function ($c) use ($customersById) {
                $cust = $customersById->get($c->activeidnumber);
                $number = $cust ? (string) $cust->queue_number : '—';
                return [
                    'id'       => $c->id,
                    'window'   => 'Win ' . $c->windowname,
                    'category' => $c->category,
                    'number'   => $number,
                    'serving'  => $cust !== null,
                    'active'   => (string) $c->counterstatus === '1',
                ];
            })
            ->values();

        $main = $counters->firstWhere('serving', true) ?? $counters->first();

        $waiting = QueueCustomer::where('status', 'waiting')
            ->whereDate('created_at', today())
            ->count();

        return [
            'mode'     => $isOn ? 'On' : 'Off',
            'main'     => $main ? ['window' => $main['window'], 'number' => $main['number']] : null,
            'counters' => $counters,
            'waiting'  => $waiting,
        ];
    }

    private function personnelData(): array
    {
        $staff = User::with('office')
            ->whereIn('role', ['MIS Support', 'Administrator'])
            ->where('ustatus', '!=', 3)
            ->orderBy('fname')
            ->get(['id', 'fname', 'lname', 'office_id', 'role', 'station'])
            ->map(function ($u) {
                return [
                    'id'         => $u->id,
                    'name'       => trim(($u->fname ?? '') . ' ' . ($u->lname ?? '')),
                    'short_name' => trim(strtoupper(substr((string) $u->fname, 0, 1)) . '. ' . ($u->lname ?? '')),
                    'office'     => $u->office->office_abbr ?? 'N/A',
                    'station'    => $u->station,
                ];
            })
            ->values();

        return [
            'total'   => $staff->count(),
            'onduty'  => $staff->where('station', '!=', 'Leave')->count(),
            'office'  => $staff->where('station', 'Office')->count(),
            'field'   => $staff->where('station', 'Field')->count(),
            'leave'   => $staff->where('station', 'Leave')->count(),
            'staff'   => $staff,
        ];
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
