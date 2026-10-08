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
            'month'   => 'required|numeric|min:1|max:12',
            'year'    => 'required|integer|min:2000|max:2100',
            'off_id'  => 'nullable|integer|exists:offices,id',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        $month = (int) $request->month;

        // Only Administrators may view all offices / pick any office or user.
        // Everyone else is locked to their own office (and its users).
        $isAdmin = auth()->user()->role === 'Administrator';
        $offId = $isAdmin
            ? ($request->filled('off_id') ? $request->off_id : null)
            : auth()->user()->office_id;
        $userId = $request->filled('user_id') ? (int) $request->user_id : null;
        if ($userId && !$isAdmin) {
            // Non-admins can only pick users from their own office.
            $belongsHere = \App\Models\TicketDB\User::where('id', $userId)
                ->where('office_id', auth()->user()->office_id)
                ->exists();
            if (!$belongsHere) {
                $userId = null;
            }
        }

        $base = ClientFeedback::whereYear('clientfeedback.created_at', $request->year)
            ->whereMonth('clientfeedback.created_at', $month)
            ->when($offId, function ($query) use ($offId) {
                $query->where('clientfeedback.off_id', $offId);
            })
            ->when($userId, function ($query) use ($userId) {
                // Match the user in EITHER role: the requester who gave the
                // feedback, or the staff member the feedback is intended for.
                $query->where(function ($q) use ($userId) {
                    $q->where('clientfeedback.user_id', $userId)
                      ->orWhere('clientfeedback.resolved_by', $userId)
                      ->orWhereHas('ticket', function ($t) use ($userId) {
                          $t->where('dailyticketrequest.resolved_by', $userId);
                      });
                });
            });

        // Total rating summary for the current selection (rated feedback only)
        $rated = (clone $base)->whereNotNull('clientfeedback.rating');
        $totalResponses = (clone $rated)->count();
        $averageRating = $totalResponses > 0
            ? round((clone $rated)->avg('clientfeedback.rating'), 2)
            : 0;
        $distribution = (clone $rated)
            ->selectRaw('clientfeedback.rating as rating, COUNT(*) as total')
            ->groupBy('clientfeedback.rating')
            ->pluck('total', 'rating')
            ->toArray();

        $scopeLabel = 'All Offices';
        if ($offId) {
            $scopeLabel = Office::where('id', $offId)->value('office_abbr') ?? 'Office';
        }
        if ($userId) {
            $picked = \App\Models\TicketDB\User::find($userId);
            if ($picked) {
                $scopeLabel .= ' — ' . trim(($picked->fname ?? '') . ' ' . ($picked->lname ?? ''));
            }
        }

        $data = (clone $base)->with(['ticket.requester', 'ticket.resolver', 'ticket.category', 'ticket.subcategory', 'supportOffice', 'resolver'])
            ->orderBy('clientfeedback.created_at', 'DESC')
            ->get()
            ->map(function ($item) {
                $requester = $item->ticket->requester ?? null;
                $resolver = $item->resolver ?? $item->ticket->resolver ?? null;
                return [
                    'id'           => $item->id,
                    'ticket_number'=> $item->ticket->ticket_number ?? 'N/A',
                    'requester'    => $requester
                        ? trim(($requester->fname ?? '') . ' ' . ($requester->lname ?? ''))
                        : 'Unknown',
                    'office'       => $item->supportOffice->office_abbr ?? ($item->ticket->supportoffice->office_abbr ?? '-'),
                    'resolved_by'  => $resolver
                        ? trim(($resolver->fname ?? '') . ' ' . ($resolver->lname ?? ''))
                        : '-',
                    'category'     => $item->ticket->category->ticketcatname ?? '-',
                    'subcategory'  => $item->ticket->subcategory->ticketsubcatname ?? '-',
                    'rating'       => $item->rating,
                    'feedback'     => $item->feedback,
                    'date'         => $item->created_at?->format('M d, Y h:i A'),
                ];
            });

        return response()->json([
            'data'    => $data,
            'summary' => [
                'scope'   => $scopeLabel,
                'total'   => $totalResponses,
                'average' => $averageRating,
                'dist'    => [
                    5 => $distribution[5] ?? 0,
                    4 => $distribution[4] ?? 0,
                    3 => $distribution[3] ?? 0,
                    2 => $distribution[2] ?? 0,
                    1 => $distribution[1] ?? 0,
                ],
            ],
        ]);
    }

    /**
     * Users of one office, for the "specific user" dropdown.
     * Non-admins are locked to their own office.
     */
    public function users(Request $request)
    {
        $request->validate([
            'off_id' => 'required|integer|exists:offices,id',
        ]);

        $offId = (int) $request->off_id;
        if (auth()->user()->role !== 'Administrator') {
            $offId = auth()->user()->office_id;
        }

        $users = \App\Models\TicketDB\User::where('office_id', $offId)
            ->orderBy('lname')
            ->orderBy('fname')
            ->get(['id', 'fname', 'lname']);

        return response()->json([
            'data' => $users->map(function ($u) {
                return [
                    'id'   => $u->id,
                    'name' => trim(($u->fname ?? '') . ' ' . ($u->lname ?? '')) ?: ('User #' . $u->id),
                ];
            }),
        ]);
    }
}
