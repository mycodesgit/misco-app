<?php

namespace App\Models\TicketDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectKanbanTask extends Model
{
    use HasFactory;

    protected $table = 'project_kanban_tasks';

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'assigned_to',
        'position',
        'created_by',
    ];

    public const STATUSES = ['todo', 'in_progress', 'on_hold', 'done'];

    public const STATUS_LABELS = [
        'todo'        => 'To Do',
        'in_progress' => 'In Progress',
        'on_hold'     => 'On Hold',
        'done'        => 'Done',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
