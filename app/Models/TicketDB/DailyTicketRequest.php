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
        'status',
        'started_at',
        'resolved_at',
    ];

    /**
     * Cast custom timestamps to Carbon instances.
     */
    protected $casts = [
        'started_at'  => 'datetime',
        'resolved_at' => 'datetime',
    ];

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
