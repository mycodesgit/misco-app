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
use App\Models\TicketDB\Category;
use App\Models\TicketDB\Subcategory;
use App\Models\TicketDB\AuditTrailCategory;
use App\Models\TicketDB\AuditTrailCategorySub;

class CategorySubController extends Controller
{
    public function show()
    {
        $data = Subcategory::with(['user', 'category'])
            ->whereHas('category', function ($query) {
                $query->where('off_id', Auth::user()->office_id);
            })
            ->whereIn('status', [1, 2])
            ->get();

        return response()->json(['data' => $data]);
    }

    public function create(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'ticketsubcatname' => 'required',
            ]);

            $categoryID = $request->input('cat_id');

            // Subcategories can only be added under the office's own categories
            if (!$this->isOwnCategory($categoryID)) {
                return response()->json(['error' => true, 'message' => 'You can only add subcategories to your own office\'s categories.'], 403);
            }

            $subcategoryName = $request->input('ticketsubcatname');
            $existingSubcategory = Subcategory::where('ticketsubcatname', $subcategoryName)
                    ->where('cat_id', $categoryID)
                    ->whereIn('status', [1, 2])
                    ->first();

            if ($existingSubcategory) {
                return response()->json(['error' => true, 'message' => 'Subcategory already exists!'],  404);
            }

            try {
                $cat = Subcategory::create([
                    'user_id' => Auth::user()->id,
                    'cat_id' => $request->input('cat_id'),
                    'ticketsubcatname' => $request->input('ticketsubcatname'),
                ]);

                $userPayload = $cat->toArray();

                $this->logAudit($request, 'Add_Subcategory', $userPayload);

                return response()->json(['success' => true, 'message' => 'Subcategory stored successfully!'],  200);

            } catch (\Exception $e) {
                return response()->json(['error' => true, 'message' => 'Failed to store Subcategory!'],  404);
            }
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'ticketsubcatname' => 'required',
        ]);

        // Both the subcategory and the category it moves to must be the office's own
        $subcategory = $this->ownSubcategory($request->input('id'));
        if (!$subcategory || !$this->isOwnCategory($request->input('cat_id'))) {
            return response()->json(['error' => true, 'message' => 'You can only edit your own office\'s subcategories.'], 403);
        }

        try {
            $categoryID = $request->input('cat_id');
            $subcategoryName = $request->input('ticketsubcatname');
            $existingSubcategory = Subcategory::where('ticketsubcatname', $subcategoryName)
                    ->where('cat_id', $categoryID)
                    ->where('id', '!=', $request->input('id'))
                    ->first();

            if ($existingSubcategory) {
                return response()->json(['error' => true, 'message' => 'Subcategory already exists!'], 200);
            }

            $subcategory->update([
                'user_id' => Auth::user()->id,
                'cat_id' => $categoryID,
                'ticketsubcatname' => $subcategoryName,
                'status' => $request->input('status'),
            ]);

            $newData = $subcategory->fresh()->toArray();

            $auditPayload = [
                'after' => $newData,
            ];

            $this->logAudit($request, 'Edit_Subcategory', $auditPayload);

            return response()->json(['success' => true, 'message' => 'Updated Successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => 'Failed to update Subcategory!'], 404);
        }
    }

    public function delete(Request $request, $id)
    {
        // A desk may only delete its own office's subcategories
        $subcategory = $this->ownSubcategory($id);

        if (!$subcategory) {
            return response()->json(['success' => false, 'message' => 'Subcategory not found'], 404);
        }

        // Update status to 3 instead of removing from DB
        $subcategory->status = 3;
        $subcategory->save();

        // Get the updated model attributes for the audit log
        $newData = $subcategory->toArray();

        $auditPayload = [
            'after' => $newData,
        ];

        $this->logAudit($request, 'Delete_Subcategory', $auditPayload);

        return response()->json(['success' => true, 'message' => 'Deleted Successfully']);
    }

    /**
     * True when the category belongs to the logged-in user's office.
     */
    private function isOwnCategory($categoryId): bool
    {
        return Category::where('id', $categoryId)
            ->where('off_id', Auth::user()->office_id)
            ->exists();
    }

    /**
     * The subcategory with this id if its category belongs to the logged-in
     * user's office.
     */
    private function ownSubcategory($id): ?Subcategory
    {
        return Subcategory::where('id', $id)
            ->whereHas('category', function ($query) {
                $query->where('off_id', Auth::user()->office_id);
            })
            ->first();
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

        AuditTrailCategorySub::create([
            'user_id'    => auth()->id(),
            'email'   => auth()->user()->email ?? 'System',
            'action'     => $action,
            'activity'   => \App\Helpers\AuditActivity::describe($action, $payload),
            'actiondata' => json_encode($payload),
            'ip_address' => $request->ip(),
            'user_agent' => $browser . ' on ' . $platform,
            'login_at'   => now(),
        ]);
    }
}
