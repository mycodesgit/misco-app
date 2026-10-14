<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\TicketDB\WifiVoucher;
use App\Models\EvalDB\QCEsemester;

class WifiVoucherController extends Controller
{
    public function index()
    {
        $years = QCEsemester::select('qceschlyear')->distinct()->orderBy('qceschlyear', 'DESC')->pluck('qceschlyear');
        $semesters = QCEsemester::select('qcesemester')->distinct()->orderBy('qcesemester')->pluck('qcesemester');

        return view('pages.manage.wifi', compact('years', 'semesters'));
    }

    public function show(Request $request)
    {
        $data = WifiVoucher::when($request->filled('schlyear'), function ($query) use ($request) {
                $query->where('schlyear', $request->schlyear);
            })
            ->when($request->filled('semester'), function ($query) use ($request) {
                $query->where('semester', $request->semester);
            })
            ->when($request->filled('status') && in_array($request->status, ['0', '1'], true), function ($query) use ($request) {
                $query->where('status', (int) $request->status);
            })
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Upload a CSV of voucher codes. Staff picks school year + semester first;
     * every imported row defaults to status 1 (Available).
     * Expected CSV: one code per row in the first column, optional header row.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'schlyear' => 'required|string|max:50',
            'semester' => 'required|string|max:50',
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $handle = fopen($path, 'r');
        if (!$handle) {
            return response()->json(['success' => false, 'message' => 'Could not read the uploaded file.'], 422);
        }

        $schlyear = trim($request->schlyear);
        $semester = trim($request->semester);
        $uploader = trim((Auth::user()->fname ?? '') . ' ' . (Auth::user()->lname ?? '')) ?: Auth::user()->email;

        $imported = 0;
        $skipped = 0;
        $seen = [];

        while (($row = fgetcsv($handle)) !== false) {
            $code = strtoupper(trim((string) ($row[0] ?? '')));

            // Skip blanks, header row, and in-file duplicates
            if ($code === '' || in_array(strtolower($code), ['voucher_code', 'voucher code', 'code'], true)) {
                $skipped++;
                continue;
            }
            if (isset($seen[$code])) {
                $skipped++;
                continue;
            }
            $seen[$code] = true;

            // Skip codes already in the database
            if (WifiVoucher::where('voucher_code', $code)->exists()) {
                $skipped++;
                continue;
            }

            WifiVoucher::create([
                'voucher_code' => $code,
                'schlyear'     => $schlyear,
                'semester'     => $semester,
                'status'       => 1,
                'uploaded_by'  => $uploader,
            ]);
            $imported++;
        }
        fclose($handle);

        return response()->json([
            'success' => true,
            'message' => "Upload complete: {$imported} voucher(s) added as Available, {$skipped} skipped.",
            'data'    => ['imported' => $imported, 'skipped' => $skipped],
        ]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id'     => 'required|exists:wifi_vouchers,id',
            'status' => 'required|in:0,1',
        ]);

        $voucher = WifiVoucher::findOrFail($request->id);
        $voucher->status = (int) $request->status;
        $voucher->save();

        $label = $voucher->status === 1 ? 'Available' : 'Used';

        return response()->json([
            'success' => true,
            'message' => "Voucher {$voucher->voucher_code} marked as {$label}.",
        ]);
    }

    public function delete($id)
    {
        $voucher = WifiVoucher::findOrFail($id);
        $voucher->delete();

        return response()->json([
            'success' => true,
            'message' => "Voucher {$voucher->voucher_code} deleted.",
        ]);
    }
}
