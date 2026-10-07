<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\TicketDB\User;
use App\Models\TicketDB\AuditLog;
use App\Models\TicketDB\AuditTrailUser;
use App\Models\TicketDB\AuditTrailOffice;
use App\Models\TicketDB\AuditTrailCategory;
use App\Models\TicketDB\AuditTrailCategorySub;
use App\Models\TicketDB\AuditTrailDailyTask;
use App\Models\TicketDB\AuditTrailDailyTicketRequest;
use App\Models\TicketDB\AuditTrailClientFeedback;
use App\Models\TicketDB\AuditTrailUserRole;
use App\Models\TicketDB\AuditTrailUserAssignedTask;

class AuditTrailController extends Controller
{
    /**
     * Whitelist of searchable audit categories (key => model).
     * Keys are the only accepted values — never raw table names.
     */
    public const CATEGORIES = [
        'login'          => ['label' => 'Login / Logout',      'model' => AuditLog::class],
        'users'          => ['label' => 'Users',                'model' => AuditTrailUser::class],
        'roles'          => ['label' => 'User Roles',           'model' => AuditTrailUserRole::class],
        'assigned_tasks' => ['label' => 'Assigned Tasks',       'model' => AuditTrailUserAssignedTask::class],
        'offices'        => ['label' => 'Offices',              'model' => AuditTrailOffice::class],
        'categories'     => ['label' => 'Categories',           'model' => AuditTrailCategory::class],
        'subcategories'  => ['label' => 'Sub Categories',       'model' => AuditTrailCategorySub::class],
        'daily_tasks'    => ['label' => 'Daily Tasks',          'model' => AuditTrailDailyTask::class],
        'tickets'        => ['label' => 'Ticket Requests',      'model' => AuditTrailDailyTicketRequest::class],
        'feedback'       => ['label' => 'Client Feedback',      'model' => AuditTrailClientFeedback::class],
    ];

    public function index()
    {
        abort_unless(auth()->user()->role === 'Administrator', 403);

        $categories = collect(self::CATEGORIES)->mapWithKeys(fn ($c, $k) => [$k => $c['label']]);

        return view('pages.manage.audittrail', compact('categories'));
    }

    public function show(Request $request)
    {
        abort_unless(auth()->user()->role === 'Administrator', 403);

        // NOTE: month arrives zero-padded from the select ("03"); the
        // `integer` rule rejects leading-zero strings, so use `numeric`.
        $request->validate([
            'category' => 'required|string|in:' . implode(',', array_keys(self::CATEGORIES)),
            'month'    => 'required|numeric|min:1|max:12',
            'year'     => 'required|integer|min:2000|max:2100',
        ]);

        $model = self::CATEGORIES[$request->category]['model'];
        $month = (int) $request->month;

        $rows = $model::whereYear('created_at', $request->year)
            ->whereMonth('created_at', $month)
            ->orderBy('created_at', 'DESC')
            ->limit(1000)
            ->get();

        // Resolve actor names in bulk (audit models carry user_id + email only)
        $names = User::whereIn('id', $rows->pluck('user_id')->filter()->unique())
            ->get(['id', 'fname', 'lname'])
            ->mapWithKeys(fn ($u) => [$u->id => trim(($u->fname ?? '') . ' ' . ($u->lname ?? ''))]);

        $data = $rows->map(function ($row) use ($names) {
            $details = $row->actiondata ?? null;
            if (is_null($details) && isset($row->login_at)) {
                $details = 'Login: ' . $row->login_at . ($row->logout_at ? ' | Logout: ' . $row->logout_at : '');
            }

            // Pretty-print JSON payloads for the modal viewer
            $pretty = $details ?? '';
            if (is_string($details)) {
                $decoded = json_decode($details, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $pretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                }
            }

            return [
                'id'      => $row->id,
                'user'    => $names[$row->user_id] ?? 'System',
                'email'   => $row->email ?? '-',
                'action'  => $row->action ?? '-',
                'full'    => $pretty,
                'ip'      => $row->ip_address ?? '-',
                'agent'   => $row->user_agent ? Str::limit($row->user_agent, 60) : '-',
                'date'    => $row->created_at?->format('M d, Y h:i A'),
            ];
        });

        return response()->json(['data' => $data->values()]);
    }
}
