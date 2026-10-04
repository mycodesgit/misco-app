<?php

namespace App\Models\TicketDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketChat extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $table = 'ticketchats';

    protected $fillable = [
        'ticket_id',
        'sender_id',
        'message',
        'is_read',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function ticket()
    {
        return $this->belongsTo(DailyTicketRequest::class, 'ticket_id');
    }
}
