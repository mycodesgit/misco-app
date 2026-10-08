<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Models\TicketDB\Project;
use App\Models\TicketDB\ProjectKanbanTask;

class ProjectKanbanController extends Controller
{
    /**
     * Same rule as projects: same office + member/creator/Administrator.
     */
    private function authorizeKanban(Project $project): void
    {
        abort_unless((int) $project->office_id === (int) auth()->user()->office_id, 403);

        $user = auth()->user();
        $isMember = $project->members()->where('users.id', $user->id)->exists();
        $isCreator = (int) $project->created_by === (int) $user->id;
        $isAdmin = $user->role === 'Administrator';

        abort_unless($isMember || $isCreator || $isAdmin, 403, 'Only team members can use this board.');
    }

    private function taskPayload(ProjectKanbanTask $t): array
    {
        $assignee = $t->assignee;
        return [
            'id'            => $t->id,
            'title'         => $t->title,
            'description'   => $t->description,
            'status'        => $t->status,
            'position'      => (int) $t->position,
            'assigned_to'   => $t->assigned_to,
            'assignee_name' => $assignee ? trim(($assignee->fname ?? '') . ' ' . ($assignee->lname ?? '')) : null,
            'created_by'    => $t->created_by,
            'updated_label' => $t->updated_at ? $t->updated_at->format('M d, Y h:i A') : null,
        ];
    }

    /**
     * Whole board for one project (stays for the entire project duration).
     */
    public function board($projectId)
    {
        $project = Project::with('members:id,fname,lname')->findOrFail($projectId);
        $this->authorizeKanban($project);

        $tasks = ProjectKanbanTask::with('assignee:id,fname,lname')
            ->where('project_id', $project->id)
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->map(fn ($t) => $this->taskPayload($t))
            ->values();

        return response()->json([
            'project' => [
                'id'      => $project->id,
                'name'    => $project->name,
                'members' => $project->members->map(fn ($m) => [
                    'id'   => $m->id,
                    'name' => trim(($m->fname ?? '') . ' ' . ($m->lname ?? '')),
                ])->values(),
            ],
            'tasks' => $tasks,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id'  => 'required|exists:projects,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'status'      => 'nullable|in:' . implode(',', ProjectKanbanTask::STATUSES),
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $project = Project::findOrFail($request->project_id);
        $this->authorizeKanban($project);

        // Assignee must be a team member — or yourself (board openers include
        // creators/admins who may log their own work without joining the team)
        if ($request->filled('assigned_to')) {
            $assigneeOk = $project->members()->where('users.id', $request->assigned_to)->exists()
                || (int) $request->assigned_to === (int) Auth::id();
            if (!$assigneeOk) {
                return response()->json(['success' => false, 'message' => 'Assignee must be a member of this project.'], 422);
            }
        }

        $status = $request->input('status', 'todo');
        $position = (int) (ProjectKanbanTask::where('project_id', $project->id)
            ->where('status', $status)->max('position') ?? -1) + 1;

        $task = ProjectKanbanTask::create([
            'project_id'  => $project->id,
            'title'       => $request->title,
            'description' => $request->description,
            'status'      => $status,
            'assigned_to' => $request->assigned_to,
            'position'    => $position,
            'created_by'  => Auth::id(),
        ]);
        $task->load('assignee:id,fname,lname');

        return response()->json(['success' => true, 'message' => 'Task added to board!', 'data' => $this->taskPayload($task)], 201);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'          => 'required|exists:project_kanban_tasks,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $task = ProjectKanbanTask::findOrFail($request->id);
        $this->authorizeKanban($task->project);
        abort_unless((int) $task->created_by === (int) auth()->id(), 403, 'Only the member who added this task can edit it.');

        // Assignee must stay inside the project team
        if ($request->filled('assigned_to')) {
            $isMember = $task->project->members()->where('users.id', $request->assigned_to)->exists();
            if (!$isMember) {
                return response()->json(['success' => false, 'message' => 'Assignee must be a member of this project.'], 422);
            }
        }

        $task->update([
            'title'       => $request->title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
        ]);
        $task->load('assignee:id,fname,lname');

        return response()->json(['success' => true, 'message' => 'Task updated!', 'data' => $this->taskPayload($task)]);
    }

    /**
     * Persist drag-and-drop: full column order per status.
     * { project_id, order: { todo: [ids], in_progress: [ids], on_hold: [ids], done: [ids] } }
     */
    public function reorder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'order'      => 'required|array',
            'order.*'    => 'array',
            'order.*.*'  => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $project = Project::findOrFail($request->project_id);
        $this->authorizeKanban($project);

        $validIds = ProjectKanbanTask::where('project_id', $project->id)->pluck('id')->all();
        // Members may only move their own cards — everyone else's stay untouched.
        $myIds = ProjectKanbanTask::where('project_id', $project->id)
            ->where('created_by', auth()->id())
            ->pluck('id')->map(fn ($id) => (int) $id)->all();

        DB::transaction(function () use ($request, $validIds, $myIds) {
            foreach (ProjectKanbanTask::STATUSES as $status) {
                $ids = $request->input("order.{$status}", null);
                if (!is_array($ids)) {
                    continue; // column untouched — leave as is
                }
                foreach (array_values($ids) as $position => $id) {
                    if (!in_array((int) $id, array_map('intval', $validIds), true)) {
                        continue;
                    }
                    if (!in_array((int) $id, $myIds, true)) {
                        continue; // not my card — leave status/position alone
                    }
                    ProjectKanbanTask::where('id', $id)->update([
                        'status'   => $status,
                        'position' => $position,
                    ]);
                }
            }
        });

        return response()->json(['success' => true, 'message' => 'Board updated!']);
    }

    public function destroy($id)
    {
        $task = ProjectKanbanTask::findOrFail($id);
        $this->authorizeKanban($task->project);
        abort_unless((int) $task->created_by === (int) auth()->id(), 403, 'Only the member who added this task can delete it.');
        $task->delete();

        return response()->json(['success' => true, 'message' => 'Task removed from board!']);
    }
}
