<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\TicketDB\DailyTicketRequest;

/**
 * Ticket Archive — finished tickets (Resolved / Cancelled) from past years.
 * Tickets land here automatically when the year changes; see
 * DailyTicketRequest::scopeArchived(). Open to every role, each seeing only
 * the tickets they may view (DailyTicketRequest::scopeVisibleTo()).
 */
class TicketArchiveController extends Controller
{
    public function index()
    {
        // Past years that have archived tickets this user can see, newest first
        $years = DailyTicketRequest::archived()
            ->visibleTo(Auth::user())
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return view('pages.request.archive', compact('years'));
    }

    public function show(Request $request)
    {
        $year = (int) $request->query('year');

        $data = DailyTicketRequest::with([
                'requester.office',
                'category',
                'subcategory',
                'supportoffice:id,office_abbr',
                'resolver:id,fname,lname,ext',
            ])
            ->archived()
            ->visibleTo(Auth::user())
            ->when($year > 0, fn ($q) => $q->whereYear('created_at', $year))
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['data' => $data]);
    }
}
