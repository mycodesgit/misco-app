<?php

namespace App\Models\TicketDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientFeedback extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $table = 'clientfeedback';

    protected $fillable = [
        'user_id',
        'cat_id',
        'subcat_id',
        'off_id',
        'resolved_by',
        'ticket_id',
        'rating',
        'feedback',
    ];

    public function ticket()
    {
        return $this->belongsTo(DailyTicketRequest::class, 'ticket_id');
    }

    /**
     * The IT staff member who resolved the ticket this feedback is for.
     */
    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function supportOffice()
    {
        return $this->belongsTo(Office::class, 'off_id');
    }
}
