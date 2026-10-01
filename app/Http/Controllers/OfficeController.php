<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Carbon\Carbon;

use Jenssegers\Agent\Agent;

use App\Models\TicketDB\User;
use App\Models\TicketDB\Office;
use App\Models\TicketDB\AuditTrailOffice;

class OfficeController extends Controller
{
    public function index()
    {
        $offcs = Office::get();

        return view('pages.manage.office', compact('offcs'));
    }

    public function show()
    {
        $data = Office::orderBy('office_name', 'ASC')->get();

        return response()->json(['data' => $data]);
    }

    public function create(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'office_name' => 'required',
                'office_abbr' => 'required',
            ]);

            $officeName = $request->input('office_name');
            $existingOffice = Office::where('office_name', $officeName)->first();

            if ($existingOffice) {
                return response()->json(['error' => true, 'message' => 'Office already exists!'],  404);
            }

            try {
                $offcs = Office::create([
                    'user_id'     => Auth::user()->id,
                    'office_name' => $officeName,
                    'office_abbr' => $request->input('office_abbr'),
                    'remember_token' => Str::random(60),
                ]);

                $userPayload = $offcs->makeHidden(['remember_token'])->toArray();
                $this->logAudit($request, 'Add_Office', $userPayload);

                return response()->json(['success' => true, 'message' => 'Office stored successfully!'],  200);
            } catch (\Exception $e) {
                return response()->json(['error' => true, 'message' => 'Failed to add Office!'],  404);
            }
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'office_name' => 'required',
            'office_abbr' => 'required',
        ]);

        try {
            $officeName = $request->input('office_name');
            $existingOffice = Office::where('Office_name', $officeName)->where('id', '!=', $request->input('id'))->first();

            if ($existingOffice) {
                return response()->json(['error' => true, 'message' => 'Office already exists!'], 200);
            }

            $offcs = Office::findOrFail($request->input('id'));
            $offcs->update([
                'user_id'     => Auth::user()->id,
                'office_name' => $officeName,
                'office_abbr' => $request->input('office_abbr')
            ]);

            $newData = $offcs->fresh()->makeHidden(['remember_token'])->toArray();

            $auditPayload = [
                'after' => $newData,
            ];

            $this->logAudit($request, 'Edit_Office', $auditPayload);

            return response()->json(['success' => true, 'message' => 'Updated Successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => 'Failed to update Office!'], 404);
        }
    }

    /**
     * Helper function to centralize audit trail logging.
     */
    private function logAudit(Request $request, string $action, array $payload): void
    {
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        $browser  = $agent->browser();
        $platform = $agent->platform();

        AuditTrailOffice::create([
            'user_id'    => auth()->id(),
            'email'   => auth()->user()->email ?? 'System',
            'action'     => $action,
            'actiondata' => json_encode($payload),
            'ip_address' => $request->ip(),
            'user_agent' => $browser . ' on ' . $platform,
            'login_at'   => now(),
        ]);
    }
}
