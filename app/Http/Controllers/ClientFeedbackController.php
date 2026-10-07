<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\TicketDB\ClientFeedback;
use App\Models\TicketDB\Office;

class ClientFeedbackController extends Controller
{
    public function index()
    {
        $offices = Office::orderBy('office_abbr')->get(['id', 'office_abbr', 'office_name']);

        return view('pages.reports.clientfeedback', compact('offices'));
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

        $data = ClientFeedback::with(['ticket.requester', 'ticket.category', 'ticket.subcategory', 'supportOffice'])
            ->whereYear('clientfeedback.created_at', $request->year)
            ->whereMonth('clientfeedback.created_at', $month)
            ->when($request->filled('off_id'), function ($query) use ($request) {
                $query->where('clientfeedback.off_id', $request->off_id);
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
