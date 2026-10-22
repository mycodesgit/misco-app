<?php

namespace App\Models\TicketDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyTicketRequest extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $table = 'dailyticketrequest';

    protected $fillable = [
        'user_id',
        'reqoff_id',
        'off_id',
        'cat_id',
        'subcat_id',
        'ticket_number',
        'issue_description',
        'priority',
        'contactno',
        'attachment',
        'remarks',
        'assigned_to',
        'resolved_by',
        'status',
        'started_at',
        'resolved_at',
    ];

    /**
     * Computed columns sent to the ticket tables.
     */
    protected $appends = [
        'resolved_by_name',
        'duration',
        'duration_minutes',
    ];

    /**
     * Cast custom timestamps to Carbon instances.
     */
    protected $casts = [
        'started_at'  => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // --- COMPUTED ATTRIBUTES ---

    /**
     * The full name of the staff member who marked this ticket as Resolved.
     */
    public function getResolvedByNameAttribute(): ?string
    {
        $resolver = $this->resolver;

        if (!$resolver) {
            return null;
        }

        $name = trim(($resolver->fname ?? '') . ' ' . ($resolver->lname ?? ''));

        return $name !== '' ? $name : null;
    }

    /**
     * How long the ticket took.
     *
     * Resolved tickets count from creation until they were resolved, closed
     * tickets until they were closed. Tickets that are still open show the
     * time elapsed so far.
     */
    public function getDurationAttribute(): ?string
    {
        $seconds = $this->durationSeconds();

        return $seconds === null ? null : self::formatDuration($seconds);
    }

    /**
     * The same duration in whole minutes — handy for sorting.
     */
    public function getDurationMinutesAttribute(): ?int
    {
        $seconds = $this->durationSeconds();

        return $seconds === null ? null : intdiv($seconds, 60);
    }

    /**
     * The duration in seconds, or null when the ticket has no creation date.
     */
    protected function durationSeconds(): ?int
    {
        $start = $this->created_at;

        if (!$start) {
            return null;
        }

        $status = strtolower((string) $this->status);

        if ($status === 'resolved') {
            // Finished — count until it was resolved
            $end = $this->resolved_at ?: $this->updated_at;
        } elseif ($status === 'cancelled') {
            // Closed without a resolution — the close date is the last update
            $end = $this->updated_at;
        } else {
            // Still open (Pending / In Progress) — count the time elapsed so
            // far. A reopened ticket also lands here: it is open again even
            // though it still carries the resolved_at of its first cycle.
            $end = now();
        }

        if (!$end) {
            return null;
        }

        return max(0, $end->getTimestamp() - $start->getTimestamp());
    }

    /**
     * Turn a number of seconds into a short human readable string.
     */
    public static function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . 's';
        }

        $minutes = intdiv($seconds, 60);

        if ($minutes < 60) {
            return $minutes . 'm';
        }

        $hours = intdiv($minutes, 60);

        if ($hours < 24) {
            return $hours . 'h ' . ($minutes % 60) . 'm';
        }

        $days = intdiv($hours, 24);

        return $days . 'd ' . ($hours % 24) . 'h';
    }

    // --- RELATIONSHIPS ---

    /**
     * The employee/client who created the ticket request.
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The office/department where the request originated.
     */
    public function requesteroffice()
    {
        return $this->belongsTo(Office::class, 'reqoff_id');
    }

    public function supportoffice()
    {
        return $this->belongsTo(Office::class, 'off_id');
    }

    /**
     * The IT staff member assigned to work on this ticket.
     */
    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * The IT staff member who marked this ticket Resolved.
     */
    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Ticket Category.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id');
    }

    /**
     * Ticket Subcategory.
     */
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class, 'subcat_id');
    }
}
