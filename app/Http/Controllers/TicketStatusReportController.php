<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PDF;

use App\Models\TicketDB\DailyTicketRequest;
use App\Models\TicketDB\Office;

class TicketStatusReportController extends Controller
{
    /**
     * Every status used by the DailyTicketRequest model.
     */
    public const STATUSES = ['Pending', 'In Progress', 'Resolved', 'Cancelled'];

    public function index()
    {
        $isAdmin = auth()->user()->role === 'Administrator';

        // Non-admins only ever see their own support office.
        $offices = $isAdmin
            ? Office::orderBy('office_abbr')->get(['id', 'office_abbr', 'office_name'])
            : Office::where('id', auth()->user()->office_id)->get(['id', 'office_abbr', 'office_name']);

        return view('pages.reports.ticketstatus', compact('offices', 'isAdmin'));
    }

    /**
     * Build the Ticket Status Report PDF. Called from the iframe via GET.
     */
    public function previewPdf(Request $request)
    {
        // The month select sends a zero-padded value ("03"), so use numeric
        // instead of integer for the month rule.
        $validator = Validator::make($request->all(), [
            'month'  => 'required|numeric|min:1|max:12',
            'year'   => 'required|integer|min:2000|max:2100',
            'off_id' => 'nullable|integer|exists:offices,id',
            'status' => 'nullable|in:' . implode(',', self::STATUSES),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $month = (int) $request->month;
        $year  = (int) $request->year;

        $isAdmin = auth()->user()->role === 'Administrator';
        $offId = $isAdmin
            ? ($request->filled('off_id') ? (int) $request->off_id : null)
            : auth()->user()->office_id;

        $status = $request->filled('status') ? $request->status : null;

        // Scope shared by the summary and the detail rows. The selected status
        // only narrows the detail table — the summary always shows every status.
        $base = DailyTicketRequest::query()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->when(!$isAdmin && !$offId, function ($query) {
                // A non-admin without an office must not fall back to "all
                // offices" — return an empty report instead.
                $query->whereRaw('1 = 0');
            })
            ->when($offId, function ($query) use ($offId) {
                $query->where('off_id', $offId);
            });

        $counts = (clone $base)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $summary = [];
        foreach (self::STATUSES as $ticketStatus) {
            $summary[$ticketStatus] = (int) ($counts[$ticketStatus] ?? 0);
        }
        $totalTickets = array_sum($summary);

        $tickets = (clone $base)
            ->with([
                'requester:id,fname,lname',
                'requesteroffice:id,office_abbr,office_name',
                'supportoffice:id,office_abbr,office_name',
                'category:id,ticketcatname',
                'subcategory:id,ticketsubcatname',
                'assignedStaff:id,fname,lname',
                'resolver:id,fname,lname',
            ])
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $officeLabel = $offId
            ? (Office::where('id', $offId)->value('office_abbr') ?? 'Office')
            : 'All Offices';

        $periodLabel = Carbon::createFromDate($year, $month, 1)->format('F Y');

        $pdf = Pdf::loadView('pages.reports.pdf.ticketstatus', [
            'tickets'      => $tickets,
            'summary'      => $summary,
            'totalTickets' => $totalTickets,
            'statuses'     => self::STATUSES,
            'statusFilter' => $status,
            'periodLabel'  => $periodLabel,
            'officeLabel'  => $officeLabel,
            'generatedAt'  => Carbon::now()->format('M d, Y h:i A'),
        ])->setPaper('Legal', 'landscape');

        $fileName = 'Ticket_Status_Report_' . $year . '_' . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->stream($fileName);
    }
}
