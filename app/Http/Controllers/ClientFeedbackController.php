<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\TicketDB\ClientFeedback;
use App\Models\TicketDB\Office;

class ClientFeedbackController extends Controller
{
    public function index()
    {
        $isAdmin = auth()->user()->role === 'Administrator';

        // Non-admins only ever see their own office's feedback
        $offices = $isAdmin
            ? Office::orderBy('office_abbr')->get(['id', 'office_abbr', 'office_name'])
            : Office::where('id', auth()->user()->office_id)->get(['id', 'office_abbr', 'office_name']);

        return view('pages.reports.clientfeedback', compact('offices', 'isAdmin'));
    }

    public function show(Request $request)
    {
        // NOTE: month arrives zero-padded from the select ("03"); the
        // `integer` rule rejects leading-zero strings, so use `numeric`.
        $request->validate([
            'month'  => 'required|numeric|min:1|max:12',
            'year'   => 'required|integer|min:2000|max:2100',
            'off_id' => 'nullable|integer|exists:offices,id',
        ]);

        $month = (int) $request->month;

        // Only Administrators may view all offices / pick any office.
        // Everyone else is locked to their own office's feedback.
        $offId = auth()->user()->role === 'Administrator'
            ? ($request->filled('off_id') ? $request->off_id : null)
            : auth()->user()->office_id;

        $data = ClientFeedback::with(['ticket.requester', 'ticket.category', 'ticket.subcategory', 'supportOffice'])
            ->whereYear('clientfeedback.created_at', $request->year)
            ->whereMonth('clientfeedback.created_at', $month)
            ->when($offId, function ($query) use ($offId) {
                $query->where('clientfeedback.off_id', $offId);
            })
            ->orderBy('clientfeedback.created_at', 'DESC')
            ->get()
            ->map(function ($item) {
                $requester = $item->ticket->requester ?? null;
                return [
                    'id'           => $item->id,
                    'ticket_number'=> $item->ticket->ticket_number ?? 'N/A',
                    'requester'    => $requester
                        ? trim(($requester->fname ?? '') . ' ' . ($requester->lname ?? ''))
                        : 'Unknown',
                    'office'       => $item->supportOffice->office_abbr ?? ($item->ticket->supportoffice->office_abbr ?? '-'),
                    'category'     => $item->ticket->category->ticketcatname ?? '-',
                    'subcategory'  => $item->ticket->subcategory->ticketsubcatname ?? '-',
                    'rating'       => $item->rating,
                    'feedback'     => $item->feedback,
                    'date'         => $item->created_at?->format('M d, Y h:i A'),
                ];
            });

        return response()->json(['data' => $data]);
    }
}
