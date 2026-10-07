<?php

namespace App\Models\TicketDB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientSatisfactory extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $table = 'clientsatisfactory';

    protected $fillable = [
        'user_id',
        'cat_id',
        'subcat_id',
        'ticket_id',
        'rating',
        'feedback',
    ];

    public function ticket()
    {
        return $this->belongsTo(DailyTicketRequest::class, 'ticket_id');
    }
}
