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
use App\Models\TicketDB\UserRole;
use App\Models\TicketDB\Category;
use App\Models\TicketDB\Subcategory;
use App\Models\TicketDB\AuditTrailCategory;
use App\Models\TicketDB\AuditTrailCategorySub;

class CategoryController extends Controller
{
    public function index()
    {
        $currentUserRole = Auth::user()->role;

        $cat = Category::with('user')->where('status', 1)->get();

        $urole = UserRole::where('status', 1)
            ->where('rolename', '!=', 'Administrator')
            ->where(function ($query) use ($currentUserRole) {
                $query->where('rolename', '=', $currentUserRole)
                    ->orWhere('rolename', 'Requester');
            })
            ->get();

        return view('pages.manage.category', compact('cat', 'urole'));
    }

    public function show()
    {
        $data = Category::with('user')->where('off_id', Auth::user()->office_id)->whereIn('status', [1, 2])->get();

        return response()->json(['data' => $data]);
    }

    public function create(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'ticketcatname' => 'required',
            ]);

            $categoryName = $request->input('ticketcatname');
            $categoryType = $request->input('cattype');
            $existingCategory = Category::where('ticketcatname', $categoryName)
                    ->where('cattype', $categoryType)
                    ->whereIn('status', [1, 2])
                    ->first();

            if ($existingCategory) {
                return response()->json(['error' => true, 'message' => 'Category already exists!'],  404);
            }

            try {
                $cat = Category::create([
                    'user_id' => Auth::user()->id,
                    'off_id' => Auth::user()->office_id,
                    'ticketcatname' => $request->input('ticketcatname'),
                    'cattype' => $request->input('cattype'),
                ]);

                $userPayload = $cat->toArray();

                $this->logAudit($request, 'Add_Category', $userPayload);

                return response()->json(['success' => true, 'message' => 'Category stored successfully!', 'data' => $cat],  200);

            } catch (\Exception $e) {
                return response()->json(['error' => true, 'message' => 'Failed to store Category!'],  404);
            }
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'ticketcatname' => 'required',
        ]);

        try {
            $categoryName = $request->input('ticketcatname');
            $categoryType = $request->input('cattype');
            $existingCategory = Category::where('ticketcatname', $categoryName)
                    ->whereJsonContains('cattype', $categoryType)
                    ->where('id', '!=', $request->input('id'))
                    ->first();

            if ($existingCategory) {
                return response()->json(['error' => true, 'message' => 'Category already exists!'], 200);
            }

            $category = Category::findOrFail($request->input('id'));
            $category->update([
                'user_id' => Auth::user()->id,
                'ticketcatname' => $categoryName,
                'cattype' => $categoryType,
                'status' => $request->input('status'),
            ]);

            $newData = $category->fresh()->toArray();

            $auditPayload = [
                'after' => $newData,
            ];

            $this->logAudit($request, 'Edit_Category', $auditPayload);

            return response()->json(['success' => true, 'message' => 'Updated Successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => 'Failed to update Category!'], 404);
        }
    }

    public function delete(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        // Update status to 3 instead of removing from DB
        $category->status = 3;
        $category->save();

        // Get the updated model attributes for the audit log
        $newData = $category->toArray();

        $auditPayload = [
            'after' => $newData,
        ];

        $this->logAudit($request, 'Delete_Category', $auditPayload);

        return response()->json(['success' => true, 'message' => 'Deleted Successfully']);
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

        AuditTrailCategory::create([
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
