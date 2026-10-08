<?php

namespace App\Models\TicketDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';

    protected $fillable = [
        'office_id',
        'name',
        'start_date',
        'end_date',
        'progress',
        'remarks',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public const STATUSES = ['Planning', 'In Progress', 'On Hold', 'Completed', 'Cancelled'];

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_members', 'project_id', 'user_id')->withTimestamps();
    }

    public function kanbanTasks()
    {
        return $this->hasMany(ProjectKanbanTask::class, 'project_id')->orderBy('position');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getDurationDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function getDaysRemainingAttribute(): int
    {
        return now()->startOfDay()->diffInDays($this->end_date->copy()->startOfDay(), false);
    }
}
