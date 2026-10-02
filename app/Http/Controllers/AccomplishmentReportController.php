<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

use Carbon\Carbon;
use PDF;

use Jenssegers\Agent\Agent;

use App\Models\TicketDB\User;
use App\Models\TicketDB\Category;
use App\Models\TicketDB\Subcategory;
use App\Models\TicketDB\DailyTask;
use App\Models\TicketDB\AuditTrailCategory;
use App\Models\TicketDB\AuditTrailCategorySub;
use App\Models\TicketDB\AuditTrailDailyTask;

class AccomplishmentReportController extends Controller
{
    public function index()
    {
        return view('pages.reports.accomplishment');
    }

    /**
     * Generate PDF for reports (handles both POST for form and GET for iframe src).
     * For iframe, use GET with query params.
     */
    public function previewPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // 1. Fetch completed tasks
        $tasks = DailyTask::with(['category', 'subcategory'])
            ->where('user_id', auth()->id())
            ->where('status', 'Completed')
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('completed_at', [
                    $startDate . ' 00:00:00',
                    $endDate . ' 23:59:59'
                ]);
            })
            ->get();

        // 2. Group by Category ID and Subcategory ID
        $groupedAccomplishments = $tasks->groupBy(function ($item) {
            return $item->cat_id . '-' . $item->subcat_id;
        });

        $pdf = Pdf::loadView('pages.reports.pdf.accom', [
            'groupedAccomplishments' => $groupedAccomplishments
        ]);

        return $pdf->stream('Accomplishment_Report.pdf');
    }
}
