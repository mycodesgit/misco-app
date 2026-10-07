<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

use App\Models\TicketDB\Project;
use App\Models\TicketDB\User;
use App\Models\TicketDB\Office;

class ProjectController extends Controller
{
    public function index()
    {
        $myOffice = Office::find(auth()->user()->office_id);
        $statuses = Project::STATUSES;

        return view('pages.project.index', compact('myOffice', 'statuses'));
    }

    /**
     * Users within a specific office only (for team member picker).
     */
    public function membersByOffice($officeId)
    {
        $users = User::where('office_id', $officeId)
            ->where('ustatus', '!=', 3)
            ->orderBy('lname')
            ->get(['id', 'fname', 'lname']);

        return response()->json($users);
    }

    public function show(Request $request)
    {
        // Office is always the logged-in user's office — no office picker
        $myOfficeId = auth()->user()->office_id;

        $projects = Project::with(['office:id,office_abbr', 'members:id,fname,lname'])
            ->where('office_id', $myOfficeId)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderBy('start_date')
            ->get()
            ->map(function ($p) {
                $remaining = $p->days_remaining;
                return [
                    'id'            => $p->id,
                    'name'          => $p->name,
                    'office_id'     => $p->office_id,
                    'office'        => $p->office->office_abbr ?? '-',
                    'members'       => $p->members->map(fn ($m) => [
                        'id'   => $m->id,
                        'name' => trim(($m->fname ?? '') . ' ' . ($m->lname ?? '')),
                    ])->values(),
                    'member_names'  => $p->members->map(fn ($m) => trim(($m->fname ?? '') . ' ' . ($m->lname ?? '')))->implode(', '),
                    'member_ids'    => $p->members->pluck('id')->values(),
                    'start_date'    => $p->start_date->format('Y-m-d'),
                    'start_label'   => $p->start_date->format('M d, Y'),
                    'end_date'      => $p->end_date->format('Y-m-d'),
                    'end_label'     => $p->end_date->format('M d, Y'),
                    'duration'      => $p->duration_days,
                    'remaining'     => $remaining,
                    'remaining_label' => $remaining < 0
                        ? abs($remaining) . ' day(s) overdue'
                        : ($remaining === 0 ? 'Due today' : $remaining . ' day(s) left'),
                    'progress'      => (int) $p->progress,
                    'remarks'       => $p->remarks,
                    'status'        => $p->status,
                ];
            });

        return response()->json(['data' => $projects->values()]);
    }

    public function store(Request $request)
    {
        // Office always comes from the logged-in user — no office picker
        $request->merge(['office_id' => auth()->user()->office_id]);

        $validator = Validator::make($request->all(), [
            'office_id'  => 'required|exists:offices,id',
            'name'       => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'progress'   => 'nullable|integer|min:0|max:100',
            'remarks'    => 'nullable|string|max:2000',
            'status'     => 'required|in:' . implode(',', Project::STATUSES),
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Team members must belong to the project's office only
        if ($request->filled('member_ids')) {
            $outsiders = User::whereIn('id', $request->member_ids)
                ->where('office_id', '!=', $request->office_id)->count();
            if ($outsiders > 0) {
                return response()->json(['success' => false, 'message' => 'Team members must belong to the selected office only.'], 422);
            }
        }

        $project = Project::create([
            'office_id'  => $request->office_id,
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'progress'   => $request->input('progress', 0),
            'remarks'    => $request->remarks,
            'status'     => $request->status,
            'created_by' => Auth::id(),
        ]);

        $project->members()->sync($request->input('member_ids', []));

        return response()->json(['success' => true, 'message' => 'Project created successfully!', 'data' => $project]);
    }

    public function update(Request $request)
    {
        // Office always comes from the logged-in user — no office picker
        $request->merge(['office_id' => auth()->user()->office_id]);

        $validator = Validator::make($request->all(), [
            'id'         => 'required|exists:projects,id',
            'office_id'  => 'required|exists:offices,id',
            'name'       => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'progress'   => 'nullable|integer|min:0|max:100',
            'remarks'    => 'nullable|string|max:2000',
            'status'     => 'required|in:' . implode(',', Project::STATUSES),
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        if ($request->filled('member_ids')) {
            $outsiders = User::whereIn('id', $request->member_ids)
                ->where('office_id', '!=', $request->office_id)->count();
            if ($outsiders > 0) {
                return response()->json(['success' => false, 'message' => 'Team members must belong to the selected office only.'], 422);
            }
        }

        $project = Project::findOrFail($request->id);
        abort_unless((int) $project->office_id === (int) auth()->user()->office_id, 403);
        $project->update([
            'office_id'  => $request->office_id,
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'progress'   => $request->input('progress', 0),
            'remarks'    => $request->remarks,
            'status'     => $request->status,
        ]);
        $project->members()->sync($request->input('member_ids', []));

        return response()->json(['success' => true, 'message' => 'Project updated successfully!']);
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        abort_unless((int) $project->office_id === (int) auth()->user()->office_id, 403);
        $project->delete();

        return response()->json(['success' => true, 'message' => 'Project deleted successfully!']);
    }
}
