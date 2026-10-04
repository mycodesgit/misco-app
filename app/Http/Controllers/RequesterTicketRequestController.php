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
use App\Models\TicketDB\DailyTicketRequest;
use App\Models\TicketDB\AuditTrailCategory;
use App\Models\TicketDB\AuditTrailCategorySub;
use App\Models\TicketDB\AuditTrailDailyTicketRequest;

class RequesterTicketRequestController extends Controller
{
    public function index()
    {
        $currentUserRole = Auth::user()->role;

        $urole = UserRole::where('status', 1)
            ->whereNotIn('rolename', ['Administrator', 'Requester'])
            ->when($currentUserRole == 'Administrator', function ($query) use ($currentUserRole) {
                $query->where('rolename', $currentUserRole);
            })
            ->get();
        
        $cat = Category::with('user')->where('status', 1)->get();

        return view('pages.request.requestertickets', compact('urole', 'cat'));
    }

    public function getCat($supportType)
    {
        $categories = Category::where('status', 1)
            ->whereJsonContains('cattype', $supportType)
            ->get([
                'id',
                'ticketcatname'
            ]);

        return response()->json($categories);
    }

    public function getSubcat($category)
    {
        $subcategories = Subcategory::where('status', 1)
            ->where('cat_id', $category)
            ->get([
                'id',
                'ticketsubcatname'
            ]);

        return response()->json($subcategories);
    }

    public function getassignedPersonnel($category)
    {
        $users = User::with('assignedTasks')
            ->where('ustatus', '!=', 3)
            ->whereHas('assignedTasks', function ($query) use ($category) {
                $query->whereJsonContains('taskassigned', (string) $category);
            })
            ->get([
                'id',
                'fname',
                'lname',
            ]);

        return response()->json($users);
    }

}
